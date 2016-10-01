<?php

use Illuminate\Database\Seeder;

class CardPackSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        DB::table('card_packs')->insert(
            array(
                array(
                    'id' => 'unknown',
                    'cycle_code' => 'unknown',
                    'cycle_position' => 9999,
                    'name' => '--- not yet known ---',
                    'position' => '9999',
                    'date_release' => null,
                    'usable' => 1
                )
            )
        );
    }
}
