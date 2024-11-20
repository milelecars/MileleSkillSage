<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Collection;
use App\Models\Test;
use Illuminate\Support\Facades\Log;

class QuestionsImport implements ToCollection, WithHeadingRow
{
    protected $test;

    public function __construct(Test $test)
    {
        $this->test = $test;
    }

    public function collection(Collection $rows)
    {
        // Log the raw data
        Log::info('Raw Excel data', [
            'test_id' => $this->test->id,
            'rows_count' => $rows->count(),
            'first_row' => $rows->first()?->toArray()
        ]);

        return $rows;
    }

    public function headingRow(): int
    {
        return 1; // Excel headers are in row 1
    }
}