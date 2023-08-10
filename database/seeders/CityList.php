<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CityList extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('city_lists')->insert(
            array(
            ['name' => 'California'],
            ['name' => 'Canada'],
            ['name' => 'Colombia'],
            ['name' => 'France'],
            ['name' => 'Ireland'],
            ['name' => 'Russia'],
            )
        );
    }
}
