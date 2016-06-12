r<?php

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
        //$this->call(CardIdentitySeeder::class); TODO, cardpack, cycles as well
    }
}
