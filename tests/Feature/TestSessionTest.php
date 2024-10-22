<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Test;
use App\Models\Candidate;
use App\Models\TestInvitation;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Str;

class TestSessionTest extends TestCase
{
    use WithFaker;

    protected $existingTest;
    protected $testInvitation;
    protected $invitedEmails;
    protected $candidates = [];

    protected function setUp(): void
    {
        parent::setUp();
        
        // Use existing test from database
        $this->existingTest = Test::first();
        
        if (!$this->existingTest) {
            $this->fail('No test exists in the database. Please ensure you have at least one test record.');
        }

        // Create test emails for invitation
        $this->invitedEmails = [
            'testcandidate1@example.com',
            'testcandidate2@example.com'
        ];

        // Create or update test invitation
        $this->testInvitation = TestInvitation::updateOrCreate(
            ['test_id' => $this->existingTest->id],
            [
                'invitation_link' => route('invitation.show', Str::random(32)),
                'email_list' => $this->invitedEmails,
                'expires_at' => now()->addDays(7),
                'created_by' => 1 // Assuming admin user ID 1
            ]
        );
    }

    protected function tearDown(): void
    {
        // Clean up test candidates
        Candidate::whereIn('email', $this->invitedEmails)->delete();
        parent::tearDown();
    }

    /** @test */
    public function only_invited_candidates_can_access_test()
    {
        // Try with invited email
        $response = $this->post(route('invitation.authenticate'), [
            'name' => 'Test Candidate 1',
            'email' => $this->invitedEmails[0],
            'invitation_token' => basename($this->testInvitation->invitation_link)
        ]);

        $response->assertRedirect(route('tests.show', $this->existingTest->id));

        // Try with non-invited email
        $response = $this->post(route('invitation.authenticate'), [
            'name' => 'Unauthorized Candidate',
            'email' => 'unauthorized@example.com',
            'invitation_token' => basename($this->testInvitation->invitation_link)
        ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function multiple_invited_candidates_can_take_test_simultaneously()
    {
        // Create sessions for both invited candidates
        $candidates = [];
        foreach ($this->invitedEmails as $index => $email) {
            // Authenticate candidate
            $response = $this->post(route('invitation.authenticate'), [
                'name' => "Test Candidate " . ($index + 1),
                'email' => $email,
                'invitation_token' => basename($this->testInvitation->invitation_link)
            ]);

            $candidate = Candidate::where('email', $email)->first();
            $this->assertNotNull($candidate);
            $candidates[] = $candidate;

            // Start test
            $this->actingAs($candidate, 'candidate')
                ->post(route('tests.start', $this->existingTest->id));

            // Submit different answers
            $response = $this->actingAs($candidate, 'candidate')
                ->post(route('tests.next-question', $this->existingTest->id), [
                    'current_index' => 0,
                    'answer' => $index === 0 ? 'a' : 'b'
                ]);

            // Verify test started in database
            $this->assertDatabaseHas('test_candidate', [
                'test_id' => $this->existingTest->id,
                'candidate_id' => $candidate->id,
            ]);

            // Verify session data
            $testSession = session('test_session');
            $this->assertEquals($this->existingTest->id, $testSession['test_id']);
            $this->assertEquals($index === 0 ? 'a' : 'b', $testSession['answers'][0]);
        }
    }

    /** @test */
    public function invitation_expires_after_specified_time()
    {
        // Set invitation to expire
        $this->testInvitation->update([
            'expires_at' => now()->subDay()
        ]);

        // Try to authenticate with expired invitation
        $response = $this->post(route('invitation.authenticate'), [
            'name' => 'Test Candidate',
            'email' => $this->invitedEmails[0],
            'invitation_token' => basename($this->testInvitation->invitation_link)
        ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function candidates_can_resume_test_after_authentication()
    {
        // First authenticate and start test
        $this->post(route('invitation.authenticate'), [
            'name' => 'Test Candidate',
            'email' => $this->invitedEmails[0],
            'invitation_token' => basename($this->testInvitation->invitation_link)
        ]);

        $candidate = Candidate::where('email', $this->invitedEmails[0])->first();
        
        // Start test and submit an answer
        $this->actingAs($candidate, 'candidate')
            ->post(route('tests.start', $this->existingTest->id));

        $this->actingAs($candidate, 'candidate')
            ->post(route('tests.next-question', $this->existingTest->id), [
                'current_index' => 0,
                'answer' => 'a'
            ]);

        // Clear session
        Session::flush();

        // Re-authenticate
        $this->post(route('invitation.authenticate'), [
            'name' => 'Test Candidate',
            'email' => $this->invitedEmails[0],
            'invitation_token' => basename($this->testInvitation->invitation_link)
        ]);

        // Try to resume test
        $response = $this->actingAs($candidate, 'candidate')
            ->get(route('tests.start', $this->existingTest->id));

        $testSession = session('test_session');
        $this->assertEquals('a', $testSession['answers'][0]);
    }

    /** @test */
    public function each_invited_candidate_has_independent_session()
    {
        $candidates = [];
        $answers = ['a', 'b'];

        // Authenticate and start test for both candidates
        foreach ($this->invitedEmails as $index => $email) {
            $this->post(route('invitation.authenticate'), [
                'name' => "Test Candidate " . ($index + 1),
                'email' => $email,
                'invitation_token' => basename($this->testInvitation->invitation_link)
            ]);

            $candidate = Candidate::where('email', $email)->first();
            $candidates[] = $candidate;

            $this->actingAs($candidate, 'candidate')
                ->post(route('tests.start', $this->existingTest->id));

            $this->actingAs($candidate, 'candidate')
                ->post(route('tests.next-question', $this->existingTest->id), [
                    'current_index' => 0,
                    'answer' => $answers[$index]
                ]);
        }

        // Verify each candidate has their own session and answers
        foreach ($candidates as $index => $candidate) {
            $this->actingAs($candidate, 'candidate');
            $testSession = session('test_session');
            $this->assertEquals($answers[$index], $testSession['answers'][0]);
        }
    }

    /** @test */
    public function invalid_invitation_token_prevents_access()
    {
        $response = $this->post(route('invitation.authenticate'), [
            'name' => 'Test Candidate',
            'email' => $this->invitedEmails[0],
            'invitation_token' => 'invalid-token'
        ]);

        $response->assertSessionHasErrors();
    }

    /** @test */
    public function candidate_cannot_start_test_without_authentication()
    {
        $response = $this->post(route('tests.start', $this->existingTest->id));
        $response->assertRedirect(route('invitation.candidate-auth'));
    }
}