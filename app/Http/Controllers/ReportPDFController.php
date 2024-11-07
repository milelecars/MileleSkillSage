<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;

class ReportPDFController extends Controller
{

    public function generateSimplePDF()
    {
        $data = [
            'title' => 'Simple PDF Report',
            'date' => date('Y-m-d'),
            'content' => 'This is some sample content.'
        ];

        $pdf = Pdf::loadView('reports.v1', $data);


        return $pdf->stream();
    }

}