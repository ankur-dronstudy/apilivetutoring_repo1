<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Usertype;

class UsertypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Model::unguard();

    	DB::table('usertypes')->delete();

    	$usertypes = array(

    		['type' => 'student'],
    		['type' => 'alumni'],
    		
    		);

        // Loop through each user above and create the record for them in the database


    	foreach ($usertypes as $type)
    	{
    		Usertype::create($type);
    	}

    	Model::reguard();
    }
}
