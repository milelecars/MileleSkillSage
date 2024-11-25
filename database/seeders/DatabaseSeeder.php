<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
   public function run(): void
   {
       $flagTypes = [
           [
               'name' => 'More than One Person',
               'description' => 'Multiple faces detected in camera view during test session',
               'threshold' => 2, 
               'created_at' => now(),
               'updated_at' => now(),
           ],
           [
               'name' => 'Book',
               'description' => 'Physical book or reading material detected in view',
               'threshold' => 2, 
               'created_at' => now(),
               'updated_at' => now(),
           ],
           [
               'name' => 'Cellphone',
               'description' => 'Mobile device detected in camera view during test',
               'threshold' => 2, 
               'created_at' => now(),
               'updated_at' => now(),
           ],
           [
               'name' => 'Tab Switches',
               'description' => 'Candidate switched to different browser tab during test',
               'threshold' => 3, 
               'created_at' => now(),
               'updated_at' => now(),
           ],
           [
               'name' => 'Window Blurs', 
               'description' => 'Browser window lost focus during test session time',
               'threshold' => 3, 
               'created_at' => now(),
               'updated_at' => now(),
           ],
           [
               'name' => 'Mouse Exits',
               'description' => 'Mouse cursor left the browser window area',
               'threshold' => 2, 
               'created_at' => now(),
               'updated_at' => now(),
           ],
           [
               'name' => 'Copy/Cut Attempts',
               'description' => 'Candidate tried to copy or cut test content',
               'threshold' => 2, 
               'created_at' => now(),
               'updated_at' => now(),
           ],
           [
               'name' => 'Right Clicks',
               'description' => 'Right click button pressed during test',
               'threshold' => 2, 
               'created_at' => now(),
               'updated_at' => now(),
           ],
           [
               'name' => 'Keyboard Shortcuts',
               'description' => 'Restricted keyboard combinations were attempted during test',
               'threshold' => 2, 
               'created_at' => now(),
               'updated_at' => now(),
           ],
       ];

       DB::table('flag_types')->insert($flagTypes);
   }
}