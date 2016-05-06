<?php

use Illuminate\Database\Seeder;

class TournamentTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tournament_types')->insert(
            array(
                array(
                    'type_name' => 'seasonal tournament',
                    'order' => 1
                ),
                array(
                    'type_name' => 'store championship',
                    'order' => 2
                ),
                array(
                    'type_name' => 'regional championship',
                    'order' => 3
                ),
                array(
                    'type_name' => 'worlds championship',
                    'order' => 4
                ),
                array(
                    'type_name' => 'casual',
                    'order' => 5
                ),
                array(
                    'type_name' => 'online event',
                    'order' => 6
                )
            )
        );
    }
}
