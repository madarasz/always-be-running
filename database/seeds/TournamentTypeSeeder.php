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
                    'order' => 1,
                    'id' => 1
                ),
                array(
                    'type_name' => 'store championship',
                    'order' => 2,
                    'id' => 2
                ),
                array(
                    'type_name' => 'regional championship',
                    'order' => 3,
                    'id' => 3
                ),
                array(
                    'type_name' => 'national championship',
                    'order' => 4,
                    'id' => 4
                ),
                array(
                    'type_name' => 'worlds championship',
                    'order' => 6,
                    'id' => 5
                ),
                array(
                    'type_name' => 'non-FFG tournament',
                    'order' => 7,
                    'id' => 6
                ),
                array(
                    'type_name' => 'online event',
                    'order' => 8,
                    'id' => 7
                ),
                array(
                    'type_name' => 'non-tournament event',
                    'order' => 9,
                    'id' => 8
                ),
                array(
                    'type_name' => 'continental championship',
                    'order' => 5,
                    'id' => 9
                ),
            )
        );
    }
}
