<?php

use Illuminate\Database\Seeder;

class UsStateSeeder extends Seeder
{
    public function run()
    {
        DB::table('us_states')->insert(
            array(
                array('name' => 'Alabama', 'code' => 'AL'),
                array('name' => 'Alaska', 'code' => 'AK'),
                array('name' => 'Arizona', 'code' => 'AZ'),
                array('name' => 'Arkansas', 'code' => 'AR'),
                array('name' => 'California', 'code' => 'CA'),
                array('name' => 'Colorado', 'code' => 'CO'),
                array('name' => 'Connecticut', 'code' => 'CT'),
                array('name' => 'Delaware', 'code' => 'DE'),
                array('name' => 'District of Columbia', 'code' => 'DC'),
                array('name' => 'Florida', 'code' => 'FL'),
                array('name' => 'Georgia', 'code' => 'GA'),
                array('name' => 'Hawaii', 'code' => 'HI'),
                array('name' => 'Idaho', 'code' => 'ID'),
                array('name' => 'Illinois', 'code' => 'IL'),
                array('name' => 'Indiana', 'code' => 'IN'),
                array('name' => 'Iowa', 'code' => 'IA'),
                array('name' => 'Kansas', 'code' => 'KS'),
                array('name' => 'Kentucky', 'code' => 'KY'),
                array('name' => 'Louisiana', 'code' => 'LA'),
                array('name' => 'Maine', 'code' => 'ME'),
                array('name' => 'Maryland', 'code' => 'MD'),
                array('name' => 'Massachusetts', 'code' => 'MA'),
                array('name' => 'Michigan', 'code' => 'MI'),
                array('name' => 'Minnesota', 'code' => 'MN'),
                array('name' => 'Mississippi', 'code' => 'MS'),
                array('name' => 'Missouri', 'code' => 'MO'),
                array('name' => 'Montana', 'code' => 'MT'),
                array('name' => 'Nebraska', 'code' => 'NE'),
                array('name' => 'Nevada', 'code' => 'NV'),
                array('name' => 'New Hampshire', 'code' => 'NH'),
                array('name' => 'New Jersey', 'code' => 'NJ'),
                array('name' => 'New Mexico', 'code' => 'NM'),
                array('name' => 'New York', 'code' => 'NY'),
                array('name' => 'North Carolina', 'code' => 'NC'),
                array('name' => 'North Dakota', 'code' => 'ND'),
                array('name' => 'Ohio', 'code' => 'OH'),
                array('name' => 'Oklahoma', 'code' => 'OK'),
                array('name' => 'Oregon', 'code' => 'OR'),
                array('name' => 'Pennsylvania', 'code' => 'PA'),
                array('name' => 'Rhode Island', 'code' => 'RI'),
                array('name' => 'South Carolina', 'code' => 'SC'),
                array('name' => 'South Dakota', 'code' => 'SD'),
                array('name' => 'Tennessee', 'code' => 'TN'),
                array('name' => 'Texas', 'code' => 'TX'),
                array('name' => 'Utah', 'code' => 'UT'),
                array('name' => 'Vermont', 'code' => 'VT'),
                array('name' => 'Virginia', 'code' => 'VA'),
                array('name' => 'Washington', 'code' => 'WA'),
                array('name' => 'West Virginia', 'code' => 'WV'),
                array('name' => 'Wisconsin', 'code' => 'WI'),
                array('name' => 'Wyoming', 'code' => 'WY')
            ));
    }
}
