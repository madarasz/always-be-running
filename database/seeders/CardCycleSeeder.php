<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CardCycleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('card_cycles')->insert(
            array(
                array(
                    'id' => 'unknown',
                    'name' => 'UNKNOWN',
                    'position' => 9999
                )
            )
        );
    }
}
