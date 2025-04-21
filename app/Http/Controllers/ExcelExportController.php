<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\DB;
use App\Models\Invitation;
use App\Models\Candidate;
use App\Models\Test;
use Carbon\Carbon;

class ExcelExportController extends Controller
{
    public function exportCandidates(Request $request)
    {
        // Get filters from request
        $search = $request->input('search');
        $testFilter = $request->input('test_filter');
        
        // Log the count of regular candidate test records
        $candidateTestCount = DB::table('candidate_test')
            ->join('candidates', 'candidate_test.candidate_id', '=', 'candidates.id')
            ->join('tests', 'candidate_test.test_id', '=', 'tests.id')
            ->when($search, function($query) use ($search) {
                return $query->where(function($q) use ($search) {
                    $q->where('candidates.name', 'like', "%{$search}%")
                    ->orWhere('candidates.email', 'like', "%{$search}%");
                });
            })
            ->when($testFilter, function($query) use ($testFilter) {
                return $query->where('candidate_test.test_id', $testFilter);
            })
            ->count();
        
        \Log::info("Regular candidate_test records: {$candidateTestCount}");
        
        // Step 1: Get all candidate test data
        $candidateTestQuery = DB::table('candidate_test as ct')
            ->join('candidates as c', 'ct.candidate_id', '=', 'c.id')
            ->join('tests as t', 'ct.test_id', '=', 't.id')
            ->select(
                'c.name as candidate_name',
                'c.email',
                't.id as test_id',
                't.title as test_title',
                'ct.status',
                'ct.started_at',
                'ct.completed_at',
                'ct.score'
            );
        
        if ($search) {
            $candidateTestQuery->where(function($q) use ($search) {
                $q->where('c.name', 'like', "%{$search}%")
                ->orWhere('c.email', 'like', "%{$search}%");
            });
        }
        
        if ($testFilter) {
            $candidateTestQuery->where('ct.test_id', $testFilter);
        }
        
        $candidateTests = $candidateTestQuery->get()->map(function($item) {
            return (array)$item;
        })->toArray();
        
        // Step 2: Get all invitations
        // FIX: Use Invitation model instead of InvitationController
        $invitationsQuery = Invitation::query()
            ->when($testFilter, function($query) use ($testFilter) {
                return $query->where('test_id', $testFilter);
            })
            ->whereJsonLength('invited_emails->invites', '>', 0)
            ->with('test:id,title');
        
        $invitations = $invitationsQuery->get();
        
        \Log::info("Number of invitations found: " . count($invitations));
        
        $invitedEmails = [];
        
        // Process each invitation
        foreach ($invitations as $invitation) {
            $invites = is_string($invitation->invited_emails) 
                ? json_decode($invitation->invited_emails, true)['invites'] ?? []
                : ($invitation->invited_emails['invites'] ?? []);
            
            foreach ($invites as $invite) {
                $email = $invite['email'];
                
                // Skip if search filter doesn't match
                if ($search && !str_contains(strtolower($email), strtolower($search))) {
                    continue;
                }
                
                $deadline = Carbon::parse($invite['deadline']);
                $isExpired = now()->greaterThan($deadline);
                
                // Check if this email + test combination already exists in candidate_test
                $existingRecord = false;
                foreach ($candidateTests as $ct) {
                    if ($ct['email'] === $email && $ct['test_id'] === $invitation->test_id) {
                        $existingRecord = true;
                        break;
                    }
                }
                
                // Only include if not already in candidate_test
                if (!$existingRecord) {
                    // Try to find candidate name if available
                    $candidate = Candidate::where('email', $email)->first();
                    
                    $invitedEmails[] = [
                        'candidate_name' => $candidate ? $candidate->name : '', // Leave empty if no name
                        'email' => $email,
                        'test_id' => $invitation->test_id,
                        'test_title' => $invitation->test->title,
                        'status' => $isExpired ? 'expired' : 'invited',
                        'started_at' => null,
                        'completed_at' => null,
                        'score' => null
                    ];
                }
            }
        }
        
        \Log::info("Additional invitation records to add: " . count($invitedEmails));
        
        // Combine both sets of records
        $allRecords = array_merge($candidateTests, $invitedEmails);
        
        \Log::info("Total combined records: " . count($allRecords));
        
        // Create CSV content with headers
        $headers = [
            'Candidate Name', 'Email', 'Test', 'Status', 'Started At', 'Completed At', 
            'Score', 'Percentile', 'Report'
        ];
        
        $csv = implode(',', $headers) . "\n";
        
        // Get all scores for percentile calculation by test
        $scoresByTest = [];
        foreach ($allRecords as $row) {
            if (!empty($row['score']) && isset($row['test_id'])) {
                if (!isset($scoresByTest[$row['test_id']])) {
                    $scoresByTest[$row['test_id']] = [];
                }
                $scoresByTest[$row['test_id']][] = $row['score'];
            }
        }
        
        // Get test question types
        $testQuestionTypes = [];
        $testIds = array_unique(array_column($allRecords, 'test_id'));
        foreach ($testIds as $testId) {
            $test = Test::find($testId);
            if ($test) {
                if ($test->title == "General Mental Ability (GMA)") {
                    $questions = $test->questions()
                        ->skip(8)
                        ->take(PHP_INT_MAX)
                        ->get();
                } else {
                    $questions = $test->questions;
                }
                
                $testQuestionTypes[$testId] = [
                    'hasMCQ' => $questions->contains('question_type', 'MCQ'),
                    'hasLSQ' => $questions->contains('question_type', 'LSQ')
                ];
            }
        }
        
        foreach ($allRecords as $row) {
            // Format dates
            $startedAt = !empty($row['started_at']) ? date('M d, Y H:i', strtotime($row['started_at'])) : '-';
            $completedAt = !empty($row['completed_at']) ? date('M d, Y H:i', strtotime($row['completed_at'])) : '-';
            
            // Format status - capitalize first letter and handle special cases
            $status = ucfirst($row['status'] ?? 'Invited');
            if (($row['status'] ?? '') === 'in_progress') {
                $status = 'In Progress';
            }
            
            // Format score with % for MCQ tests
            $score = '-';
            if (!empty($row['score'])) {
                $score = $row['score'];
                // Check if test has MCQ questions
                if (isset($testQuestionTypes[$row['test_id']]['hasMCQ']) && $testQuestionTypes[$row['test_id']]['hasMCQ']) {
                    $score .= '%';
                }
            }
            
            // Calculate percentile
            $percentile = '-';
            if (!empty($row['score']) && isset($row['test_id']) && isset($scoresByTest[$row['test_id']])) {
                $percentileValue = $this->calculatePercentile((int)$row['score'], $scoresByTest[$row['test_id']]);
                
                // Format similar to the view
                if ($percentileValue >= 99) {
                    $percentile = 'Top 1%';
                } elseif ($percentileValue > 0) {
                    $percentile = 'Top ' . (100 - floor($percentileValue)) . '%';
                } else {
                    $percentile = 'Bottom Performer';
                }
            }
            
            $csvRow = [
                '"' . str_replace('"', '""', $row['candidate_name'] ?? '') . '"',
                '"' . str_replace('"', '""', $row['email'] ?? '') . '"',
                '"' . str_replace('"', '""', $row['test_title'] ?? '') . '"',
                '"' . str_replace('"', '""', $status) . '"',
                '"' . str_replace('"', '""', $startedAt) . '"',
                '"' . str_replace('"', '""', $completedAt) . '"',
                '"' . str_replace('"', '""', $score) . '"',
                '"' . str_replace('"', '""', $percentile) . '"',
                '"-"' // Report column is always empty in export
            ];
            
            $csv .= implode(',', $csvRow) . "\n";
        }
        
        $filename = 'candidates_export_' . date('Y-m-d_H-i-s') . '.csv';
        
        // Add logging for total rows in CSV
        $rowCount = substr_count($csv, "\n") - 1; // Subtract 1 for header row
        \Log::info("Total rows in CSV export: {$rowCount}");
        
        return Response::make($csv, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function calculatePercentile(int $score, array $allScores): float {
        $count = count($allScores);
        if ($count === 0) return 0;
        
        $belowOrEqual = count(array_filter($allScores, fn($s) => $s <= $score));
        return round(($belowOrEqual / $count) * 100, 2);
    }
}