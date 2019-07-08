<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Subjectcategory;

class SubjectcategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Model::unguard();

    	DB::table('subjectcategories')->delete();

    	$categories = array(

    		['name' => 'Logical','code' => 'LOGIC'],
    		['name' => 'Computer Science','code' => 'CS'],
    		);

        // Loop through each user above and create the record for them in the database


    	foreach ($categories as $category)
    	{
    		Subjectcategory::create($category);
    	}

    	Model::reguard();
    }
}
