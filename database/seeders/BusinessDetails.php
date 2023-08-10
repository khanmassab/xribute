<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BusinessDetails extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('categories')->insert(
            array(
            ['name' => 'Hotel Rooms', 'type' => 1],
            ['name' => 'Residential Hotel', 'type' => 1],
            ['name' => 'Accommodation', 'type' => 1],
            ['name' => 'Parties', 'type' => 2],
            ['name' => 'Events', 'type' => 2],
            ['name' => 'Catering', 'type' => 2],
            ['name' => 'Vans', 'type' => 3],
            ['name' => 'BBQ', 'type' => 3],
            ['name' => 'Sweats', 'type' => 3],
            )
        );
    }
}
