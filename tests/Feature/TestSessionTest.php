<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Test;
use App\Models\Admin;
use App\Models\Candidate;
use App\Models\TestInvitation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Session;

class TestSessionTest extends TestCase
{
    use WithFaker, RefreshDatabase;

    protected $existingTest;
    protected $testInvitation;
    protected $invitedEmails;
    protected $admin;
    protected $invitationToken = 'TFT8mnftxu1xv5nFkhfHqiCQw78Fv0B';

    protected function setUp(): void
    {
        parent::setUp();
        
        // Create admin user
        $this->admin = Admin::create([
            'email' => 'heliaa.haghighi@gmail.com',
            'name' => 'Admin User',
            'password' => bcrypt('password12')
        ]);

        // Create a test with all required fields
        $this->existingTest = Test::create([
            'name' => 'Sample Test',
            'description' => 'Test Description',
            'duration' => 60,
            'questions_file_path' => 'questions/sample.xlsx'
        ]);

        $this->invitedEmails = [
            'heliahaghighi16@gmail.com',
            'testcandidate2@example.com'
        ];

        // Create invitation
        $this->testInvitation = TestInvitation::create([
            'test_id' => $this->existingTest->id,
            'invitation_token' => $this->invitationToken,
            'invitation_link' => "http://127.0.0.1:8000/invitation/{$this->invitationToken}",
            'invited_emails' => $this->invitedEmails,
            'expiration_date' => now()->addDays(7),
            'created_by' => $this->admin->id
        ]);
    }

    /** @test */
    public function candidate_can_only_take_test_once()
    {
        $email = $this->invitedEmails[0];
        
        // First visit invitation page to get CSRF token
        $response = $this->withoutExceptionHandling()
            ->get("/invitation/{$this->invitationToken}");
        $response->assertSuccessful();

        // Validate the invitation
        $response = $this->withoutExceptionHandling()
            ->withSession(['_token' => csrf_token()])
            ->post("/invitation/{$this->invitationToken}/validate", [
                '_token' => csrf_token(),
                'name' => 'Test Candidate',
                'email' => $email,
                'invitation_token' => $this->invitationToken
            ]);

        // Verify candidate was created
        $candidate = Candidate::where('email', $email)->first();
        $this->assertNotNull($candidate, 'Candidate should be created');

        // Complete the test
        $candidate->tests()->attach($this->existingTest->id, [
            'started_at' => now()->subMinutes(30),
            'completed_at' => now(),
            'score' => 5
        ]);

        // Try to access test again
        $response = $this->actingAs($candidate, 'candidate')
            ->withSession(['_token' => csrf_token()])
            ->get(route('tests.show', $this->existingTest->id));

        // Should see completed status
        $response->assertSuccessful();

        // Verify database state
        $testStatus = $candidate->tests()
            ->where('test_id', $this->existingTest->id)
            ->first();
        $this->assertNotNull($testStatus->pivot->completed_at);
    }

    /** @test */
    public function completed_test_prevents_new_submissions()
    {
        // Create and authenticate candidate
        $candidate = Candidate::create([
            'email' => $this->invitedEmails[0],
            'name' => 'Test Candidate'
        ]);

        // Complete the test
        $candidate->tests()->attach($this->existingTest->id, [
            'started_at' => now()->subHour(),
            'completed_at' => now()->subMinutes(30),
            'score' => 5
        ]);

        // Try to submit new answer
        $response = $this->actingAs($candidate, 'candidate')
            ->withSession(['_token' => csrf_token()])
            ->from(route('tests.show', $this->existingTest->id))
            ->post(route('tests.next', $this->existingTest->id), [
                '_token' => csrf_token(),
                'current_index' => 0,
                'answer' => 'a'
            ]);

        // Should be redirected to results
        $response->assertRedirect(route('tests.result', $this->existingTest->id));
    }

    /** @test */
    public function incomplete_test_can_be_resumed()
    {
        // Create candidate with incomplete test
        $candidate = Candidate::create([
            'email' => $this->invitedEmails[0],
            'name' => 'Test Candidate'
        ]);

        // Start but don't complete test
        $candidate->tests()->attach($this->existingTest->id, [
            'started_at' => now()->subMinutes(5),
            'completed_at' => null
        ]);

        // Set up session for the test
        Session::put([
            'current_test_id' => $this->existingTest->id,
            'test_session' => [
                'test_id' => $this->existingTest->id,
                'start_time' => now()->subMinutes(5)->toDateTimeString(),
                'current_question' => 0,
                'answers' => []
            ]
        ]);

        // Try to resume test
        $response = $this->actingAs($candidate, 'candidate')
            ->withSession(['_token' => csrf_token()])
            ->get(route('tests.start', $this->existingTest->id));

        $response->assertSuccessful();

        // Verify test is still in progress
        $testStatus = $candidate->tests()
            ->where('test_id', $this->existingTest->id)
            ->first();
        $this->assertNull($testStatus->pivot->completed_at);
    }

    /** @test */
    public function expired_invitation_prevents_access()
    {
        // Set invitation as expired
        $this->testInvitation->update([
            'expiration_date' => now()->subDay()
        ]);

        // Try to access expired invitation
        $response = $this->withoutExceptionHandling()
            ->get("/invitation/{$this->invitationToken}");

        $response->assertRedirect(route('invitation.expired'));
        $this->assertTrue($this->testInvitation->fresh()->isExpired());
    }
}