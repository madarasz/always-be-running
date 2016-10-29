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
                    'type_name' => 'GNK / seasonal tournament',
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
                    'type_name' => 'national championship',
                    'order' => 4
                ),
                array(
                    'type_name' => 'worlds championship',
                    'order' => 5
                ),
                array(
                    'type_name' => 'non-FFG tournament',
                    'order' => 6
                ),
                array(
                    'type_name' => 'online event',
                    'order' => 7
                ),
                array(
                    'type_name' => 'non-tournament event',
                    'order' => 8
                ),
            )
        );
    }
}
