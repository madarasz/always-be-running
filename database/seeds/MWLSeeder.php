<?php

use Illuminate\Database\Seeder;

class MWLSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('mwls')->insert(
            array(
                array(
                    'id' => 0,
                    'name' => '---unknown---',
                    'date' => '1984.01.01'
                )
            )
        );
    }
}
