<?php

use Illuminate\Database\Seeder;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // adding admins
        DB::table('users')->insert([
            'id' => 1276,
            'name' => 'Necro',
            'admin' => 1
        ]);
    }
}
