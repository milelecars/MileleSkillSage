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
    protected $cleanedData = [];

    public function __construct(Test $test)
    {
        $this->test = $test;
    }

    public function collection(Collection $rows)
    {
        $this->cleanedData = $rows->map(function ($row) {
            // Create a copy of the row to modify
            $cleanRow = $row->toArray();
            
            // Sanitize all text fields that might need cleaning
            foreach ($cleanRow as $key => $value) {
                if (is_string($value)) {
                    $cleanRow[$key] = $this->sanitizeValue($value);
                }
            }
            
            return $cleanRow;
        })->toArray();
    
        Log::info('Sanitized Excel data', [
            'test_id' => $this->test->id,
            'rows_count' => count($this->cleanedData),
            'first_row' => !empty($this->cleanedData) ? $this->cleanedData[0] : null
        ]);
    }

    public function getCleanedData()
    {
        return $this->cleanedData;
    }

    protected function sanitizeValue($value)
    {
        if (!is_string($value)) return $value;

        // Remove non-breaking spaces and other HTML entities
        $value = html_entity_decode($value);             // Converts &nbsp; → real space
        $value = str_replace("\xC2\xA0", ' ', $value);    // UTF-8 non-breaking space
        $value = preg_replace('/\s+/', ' ', $value);      // Normalize multiple spaces
        $value = trim($value);                            // Trim leading/trailing whitespace

        return $value;
    }

    public function headingRow(): int
    {
        return 1; 
    }
}