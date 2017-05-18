<?php

use Illuminate\Database\Seeder;

class TournamentFormatsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('tournament_formats')->insert(
            array(
                array(
                    'id' => 1,
                    'format_name' => 'standard',
                    'order' => 1
                ),
                array(
                    'id' => 2,
                    'format_name' => 'cache refresh',
                    'order' => 2
                ),
                array(
                    'id' => 3,
                    'format_name' => '1.1.1.1',
                    'order' => 3
                ),
                array(
                    'id' => 4,
                    'format_name' => 'draft',
                    'order' => 4
                ),
                array(
                    'id' => 5,
                    'format_name' => 'cube draft',
                    'order' => 5
                ),
            )
        );
    }
}
