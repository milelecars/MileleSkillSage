<?php

namespace App\Livewire;

use App\Models\Test;
use Livewire\Component;
use App\Mail\InvitationEmail;
use App\Models\Invitation;
use App\Models\Department;
use App\Http\Controllers\OAuthController;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Google\Client;
use Google\Service\Gmail;
use Maatwebsite\Excel\HeadingRowImport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Livewire\WithFileUploads;

class InviteCandidates extends Component
{
    use WithFileUploads;


    public string $newEmail = '';
    public string $firstName = '';
    public string $lastName = '';
    public string $role = '';
    public string $candidate = '';
    public array $emailList = [];
    public $testId;
    public $excelFile = null;
    public string $selectedDepartment = '';
    public $departments = [];
    public $newDepartmentName = '';


    
    protected $validationAttributes = [
        'newEmail' => 'email',
        'firstName' => 'first name',
        'lastName' => 'last name',
        'selectedDepartment' => 'required|string|max:255',
        'role' => 'role',
    ];

    // Define validation messages
    protected $messages = [
        'firstName.required' => 'First name is required',
        'lastName.required' => 'Last name is required',
        'role.required' => 'Role is required',
        'selectedDepartment.required' => 'Department is required',
        'selectedDepartment.max' => 'Department cannot exceed 255 characters',
        'newEmail.required' => 'Email is required',
        'newEmail.email' => 'Please enter a valid email',
        'firstName.max' => 'First name cannot exceed 255 characters',
        'lastName.max' => 'Last name cannot exceed 255 characters',
        'role.max' => 'Role cannot exceed 255 characters',
        'newEmail.max' => 'Email cannot exceed 255 characters',
    ];

    public function mount($testId)
    {
        $this->testId = $testId;
        // Retrieve saved data from session
        $this->emailList = session("test_{$testId}_emails", []);

        $this->departments = Department::orderBy('name')->get();
        
        // Ensure OAuth token is available
        $this->checkAccessToken();
    }

    private function checkAccessToken()
    {
        try {
            $oAuthController = new OAuthController();
            $client = $oAuthController->getClient();
        } catch (\Exception $e) {
            Log::info('Access token check failed. Redirecting to Google login.');
            session()->flash('info', 'Please authenticate with Google to send emails.');
            return redirect()->route('google.login', ['testId' => $this->testId]);
        }
    }
   
    public function addNewDepartment()
    {
        $this->validate([
            'newDepartmentName' => 'required|string|max:255|unique:departments,name',
        ]);

        Department::create(['name' => $this->newDepartmentName]);

        $this->departments = Department::orderBy('name')->get(); // refresh list
        $this->selectedDepartment = $this->newDepartmentName;
        $this->newDepartmentName = '';
    }

    public function setDepartment($department)
    {
        $this->selectedDepartment = $department;
    }


    public function addEmail()
    {
        $this->resetValidation();
        
        $validatedData = $this->validate([
            'firstName' => 'required|string|max:255',
            'lastName' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'selectedDepartment' => 'required|string|max:255',
            'newEmail' => 'required|email|max:255',
        ]);

        $existingInvitation = Invitation::where('test_id', $this->testId)
        ->whereJsonContains('invited_emails->invites', ['email' => $this->newEmail])
        ->exists();

        if ($existingInvitation) {
            $this->addError('newEmail', 'This email has already been invited.');
            return;
        }

        $emailExistsInList = collect($this->emailList)->contains('email', $this->newEmail);

        if ($emailExistsInList) {
            $this->addError('newEmail', 'This email has already been added to the current list.');
            return;
        }
        
        $this->emailList[] = [
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'role' => $this->role,
            'department' => $this->selectedDepartment,
            'email' => $this->newEmail,
        ];
    
        session(["test_{$this->testId}_emails" => $this->emailList]);
        
        $this->reset(['firstName', 'lastName', 'role', 'selectedDepartment', 'newEmail']);
    }

    public function removeEmail($index)
    {
        unset($this->emailList[$index]);
        $this->emailList = array_values($this->emailList); 
        
        session(["test_{$this->testId}_emails" => $this->emailList]);
    }

    public function submitInvitations()
    {
        try {
            DB::beginTransaction();
    
            $oAuthController = new OAuthController();
            $client = $oAuthController->getClient();
            $service = new Gmail($client);
    
            $invitation = Invitation::where('test_id', $this->testId)->firstOrFail();
            $test = Test::findOrFail($this->testId);
    
            $existingInvites = $invitation->invited_emails['invites'] ?? [];
            $existingEmailMap = array_column($existingInvites, null, 'email');
    
            $newInvites = [];
            foreach ($this->emailList as $invite) {
                if (!isset($existingEmailMap[$invite['email']])) {
                    $newInvites[] = [
                        'email' => $invite['email'],
                        'firstName' => $invite['firstName'],
                        'lastName' => $invite['lastName'],
                        'role' => $invite['role'],
                        'department' => $invite['department'],
                        'invited_at' => now()->toISOString(),
                        'deadline' => now()->addDays(2)->toISOString(),
                    ];
                }
            }
    
            // Combine existing and new invites
            $allInvites = [
                'invites' => array_values(array_merge($existingInvites, $newInvites)),
            ];
    
            $invitation->update(['invited_emails' => $allInvites]);
    
            $failedEmails = [];
            $successfulEmails = [];
    
            foreach ($this->emailList as $invite) {
                try {
                    $message = new \Google\Service\Gmail\Message();
                    $template = view('emails.invitation-email-template', [
                        'testName' => $test->title,
                        'invitationLink' => $invitation->invitation_link,
                        'role' => $invite['role'],
                        'department' => $invite['department'],
                        'firstName' => $invite['firstName'],
                        'lastName' => $invite['lastName'],
                    ])->render();
                                      
    
                    $rawMessage = "From: Milele SkillSage <mileleskillsage@gmail.com>\r\n";
                    $rawMessage .= "To: <{$invite['email']}>\r\n";
                    $rawMessage .= 'Subject: =?utf-8?B?' . base64_encode("Invitation to Take a Test for Milele Motors") . "?=\r\n";
                    $rawMessage .= "MIME-Version: 1.0\r\n";
                    $rawMessage .= "Content-Type: text/html; charset=utf-8\r\n";
                    $rawMessage .= "Content-Transfer-Encoding: base64\r\n\r\n";
                    $rawMessage .= chunk_split(base64_encode($template));
    
                    $message->setRaw(base64_encode($rawMessage));
                    $service->users_messages->send('me', $message);
                    Log::info("Email sent successfully to {$invite['email']}");
                    $successfulEmails[] = $invite['email'];
                } catch (\Exception $e) {
                    Log::error("Failed to send to {$invite['email']}: " . $e->getMessage());
                    $failedEmails[] = $invite['email'];
                }
            }
    
            DB::commit();
    
            // Clear the session and reset if no failures
            if (empty($failedEmails)) {
                $this->reset(['newEmail', 'firstName', 'lastName', 'role', 'selectedDepartment']);
                $this->emailList = [];
                session()->forget("test_{$this->testId}_emails");
                session()->flash('success', 'Invitations sent successfully!');
            } else {
                // Partial success
                if (!empty($failedEmails)) {
                    $failList = collect($failedEmails)->map(fn($email) => "<li class='text-red-600'>• $email</li>")->implode('');
                    $failMessage = "<strong>Some emails failed to send:</strong><ul class='mt-1 list-disc ml-6'>$failList</ul>";
                
                    session()->flash('warning_html', $failMessage);
                }                
            }
    
            $this->dispatch('refresh');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to process invitations: " . $e->getMessage());
            $this->addError('submission', "Failed to process invitations");
        }
    }

    public function importExcel()
    {
        $this->validate([
            'excelFile' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        try {
            $import = new TempImport();
            Excel::import($import, $this->excelFile);

            $rows = $import->rows;

            foreach ($rows as $row) {
                if (
                    isset($row['firstname'], $row['lastname'], $row['role'], $row['department'], $row['email']) &&
                    filter_var($row['email'], FILTER_VALIDATE_EMAIL)
                ) {
                    $emailExists = collect($this->emailList)->contains('email', strtolower($row['email']));
                    if (!$emailExists) {
                        $this->emailList[] = [
                            'firstName' => $row['firstname'],
                            'lastName' => $row['lastname'],
                            'role' => $row['role'],
                            'department' => $row['department'],
                            'email' => strtolower($row['email']),
                        ];
                    }
                }
            }

            session(["test_{$this->testId}_emails" => $this->emailList]);
            $this->reset('excelFile');
            session()->flash('success', 'Excel imported successfully.');
        } catch (\Exception $e) {
            \Log::error('Import error: ' . $e->getMessage());
            session()->flash('warning', 'Failed to import Excel file.');
        }

        session(["test_{$this->testId}_emails" => $this->emailList]);

        $this->emailList = [...$this->emailList]; // force reactivity (optional safety)
        $this->reset('excelFile');
        $this->dispatch('$refresh'); // trigger UI refresh
        session()->flash('success', 'Excel imported successfully.');

    }

    

    public function render()
    {
        return view('livewire.invite-candidates', ['departments' => $this->departments]);

    }
}


class TempImport implements ToCollection, WithHeadingRow
{
    public $rows;

    public function collection(Collection $collection)
    {
        $this->rows = $collection;
    }
}
