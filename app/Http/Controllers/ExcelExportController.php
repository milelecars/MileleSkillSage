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
        $name = $request->input('name');
        $email = $request->input('email');
        $role = $request->input('role');
        $department = $request->input('department');
        $testFilter = $request->input('test_filter');

        // Map all department IDs to names
        $departmentsMap = \App\Models\Department::pluck('name', 'id')->map(fn($name) => $name);

        // Find department ID from name (if provided)
        $departmentId = null;
        if ($department) {
            $departmentId = $departmentsMap->search($department);
        }

        $candidates = Candidate::with(['tests' => function ($query) use ($testFilter) {
            $query->select('tests.id', 'title')
                ->when($testFilter, fn($q) => $q->where('tests.id', $testFilter))
                ->withPivot([
                    'role', 'department_id', 'started_at', 'completed_at', 'score',
                    'status', 'created_at'
                ]);
        }])
        ->when($testFilter, fn($q) => $q->whereHas('tests', fn($q) => $q->where('tests.id', $testFilter)))
        ->when($role, fn($q) => $q->whereHas('tests', fn($q) => $q->whereRaw('LOWER(candidate_test.role) = ?', [strtolower($role)])))
        ->when($departmentId, fn($q) => $q->whereHas('tests', fn($q) => $q->where('candidate_test.department_id', $departmentId)))
        ->when($name, fn($q) => $q->where('name', 'like', "%$name%"))
        ->when($email, fn($q) => $q->where('email', 'like', "%$email%"))
        ->get();

        $data = [];

        foreach ($candidates as $candidate) {
            foreach ($candidate->tests as $test) {
                $row = [
                    'candidate_name' => $candidate->name,
                    'email' => $candidate->email,
                    'role' => $test->pivot->role ?? '-',
                    'department' => $departmentsMap[$test->pivot->department_id] ?? '-',
                    'test_title' => $test->title,
                    'test_id' => $test->id,
                    'status' => $test->pivot->status,
                    'started_at' => $test->pivot->started_at,
                    'completed_at' => $test->pivot->completed_at,
                    'score' => $test->pivot->score
                ];
                $data[] = $row;
            }
        }

        // Now apply same logic for invitations
        $taken = DB::table('candidate_test')
            ->join('candidates', 'candidate_test.candidate_id', '=', 'candidates.id')
            ->select('candidates.email', 'candidate_test.test_id')
            ->get()
            ->map(fn($row) => strtolower($row->email) . '_' . $row->test_id)
            ->toArray();

        $invitations = Invitation::when($testFilter, fn($q) => $q->where('test_id', $testFilter))
            ->whereJsonLength('invited_emails->invites', '>', 0)
            ->with('test:id,title')
            ->get();

        foreach ($invitations as $invitation) {
            $invites = is_string($invitation->invited_emails)
                ? json_decode($invitation->invited_emails, true)['invites'] ?? []
                : ($invitation->invited_emails['invites'] ?? []);

            foreach ($invites as $invite) {
                $email = strtolower($invite['email']);
                $checkKey = $email . '_' . $invitation->test_id;

                if (in_array($checkKey, $taken)) continue;

                // Apply invitation-level filters
                if (
                    ($name && !str_contains($email, strtolower($name))) ||
                    ($email && !str_contains($email, strtolower($email))) ||
                    ($role && strtolower($invite['role'] ?? '-') !== strtolower($role)) ||
                    ($department && strtolower($invite['department'] ?? '-') !== strtolower($department))
                ) continue;

                $deadline = Carbon::parse($invite['deadline']);
                $isExpired = now()->greaterThan($deadline);

                $data[] = [
                    'candidate_name' => '',
                    'email' => $invite['email'],
                    'role' => $invite['role'] ?? '-',
                    'department' => $invite['department'] ?? '-',
                    'test_title' => $invitation->test->title ?? '-',
                    'test_id' => $invitation->test_id,
                    'status' => $isExpired ? 'expired' : 'invited',
                    'started_at' => null,
                    'completed_at' => null,
                    'score' => null
                ];
            }
        }

        // Scores by test for percentile
        $scoresByTest = collect($data)
            ->filter(fn($row) => isset($row['score']) && isset($row['test_id']))
            ->groupBy('test_id')
            ->map(fn($group) => $group->pluck('score')->toArray());

        $headers = [
            'Candidate Name', 'Email', 'Role', 'Department', 'Test',
            'Status', 'Started At', 'Completed At', 'Score', 'Percentile'
        ];

        $csv = implode(',', $headers) . "\n";

        foreach ($data as $row) {
            $startedAt = $row['started_at'] ? date('M d, Y H:i', strtotime($row['started_at'])) : '-';
            $completedAt = $row['completed_at'] ? date('M d, Y H:i', strtotime($row['completed_at'])) : '-';
            $status = ucfirst($row['status']);
            $score = $row['score'] !== null ? $row['score'] . '%' : '-';

            $percentile = '-';
            if ($row['score'] && isset($scoresByTest[$row['test_id']])) {
                $percentileValue = $this->calculatePercentile((int)$row['score'], $scoresByTest[$row['test_id']]);
                $percentile = $percentileValue >= 99
                    ? 'Top 1%'
                    : ($percentileValue > 0 ? 'Top ' . (100 - floor($percentileValue)) . '%' : 'Bottom Performer');
            }

            $csvRow = [
                '"' . str_replace('"', '""', $row['candidate_name'] ?? '') . '"',
                '"' . str_replace('"', '""', $row['email'] ?? '') . '"',
                '"' . str_replace('"', '""', $row['role'] ?? '-') . '"',
                '"' . str_replace('"', '""', $row['department'] ?? '-') . '"',
                '"' . str_replace('"', '""', $row['test_title'] ?? '-') . '"',
                '"' . $status . '"',
                '"' . $startedAt . '"',
                '"' . $completedAt . '"',
                '"' . $score . '"',
                '"' . $percentile . '"',
            ];

            $csv .= implode(',', $csvRow) . "\n";
        }

        $filename = 'candidates_export_' . now()->format('Y-m-d_H-i-s') . '.csv';

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
