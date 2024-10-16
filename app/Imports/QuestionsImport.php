<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class QuestionsImport implements ToArray, WithHeadingRow
{
    protected $test;

    public function __construct($test)
    {
        $this->test = $test;
    }

    public function array(array $rows)
    {
        return $rows;
    }
}