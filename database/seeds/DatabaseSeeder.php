<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(TournamentTypeSeeder::class);
        $this->call(UsersSeeder::class);
        $this->call(CardCycleSeeder::class);
        $this->call(CardPackSeeder::class);
    }
}
