<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;


class DatabaseSeeder extends Seeder
{
   public function run(): void
   {
        $flagTypes = [
            [
                'name' => 'More than One Person',
                'description' => 'Multiple faces detected in camera view during test session',
                'threshold' => 2, 
                 
            ],
            [
                'name' => 'Book',
                'description' => 'Physical book or reading material detected in view',
                'threshold' => 2, 
                 
            ],
            [
                'name' => 'Cellphone',
                'description' => 'Mobile device detected in camera view during test',
                'threshold' => 2, 
                 
            ],
            [
                'name' => 'Tab Switches',
                'description' => 'Candidate switched to different browser tab during test',
                'threshold' => 3, 
                 
            ],
            [
                'name' => 'Window Blurs', 
                'description' => 'Browser window lost focus during test session time',
                'threshold' => 3, 
                 
            ],
            [
                'name' => 'Mouse Exits',
                'description' => 'Mouse cursor left the browser window area',
                'threshold' => 2, 
                 
            ],
            [
                'name' => 'Copy/Cut Attempts',
                'description' => 'Candidate tried to copy or cut test content',
                'threshold' => 2, 
                 
            ],
            [
                'name' => 'Right Clicks',
                'description' => 'Right click button pressed during test',
                'threshold' => 2, 
                 
            ],
            [
                'name' => 'Keyboard Shortcuts',
                'description' => 'Restricted keyboard combinations were attempted during test',
                'threshold' => 2, 
                 
            ],
        ];

        DB::table('flag_types')->insert($flagTypes);
        
        $admins = [
            [
                'name' => 'Helia Admin',
                'email' => 'helia.haghighi@milele.com',
                'password' => Hash::make('password12'),
                 

            ],
        ];

        DB::table('admins')->insert($admins);
        $adminId = DB::table('admins')->where('email', 'helia.haghighi@milele.com')->first()->id;

        $tests = [
            [
                'title' => 'AGCT Test',
                'description' => 'AGCT (Army General Classification Test) is a standardized assessment designed to evaluate cognitive abilities and problem-solving skills. The test consists of 150 multiple-choice questions that measure various aspects of intelligence, including verbal comprehension, arithmetic reasoning, pattern recognition, and spatial visualization.',
                'duration' =>  2,
                'admin_id' => $adminId,
                 
            ],
        ];

        DB::table('tests')->insert($tests);
        $testId = DB::table('tests')->latest('id')->first()->id;

        $invitations = [
            [
                'test_id' => $testId,
                'invited_emails' => ['helia.haghighi@milele.com'],
                'expiration_date' => now()->addYear(),
                'invitation_token' => 'DpYZDbp5uuenaNxPKuhTrWgwLLGiowkz',
                'invitation_link' => 'http://127.0.0.1:8000/invitation/DpYZDbp5uuenaNxPKuhTrWgwLLGiowkz'

            ],
        ];

        DB::table('invitations')->insert($invitations);

   }
}