<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Subjectsubcategory;

class SubjectsubcategoryTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Model::unguard();

    	DB::table('subjectsubcategories')->delete();

    	$subcategories = array(

    		['subjectcategory_id'=>1,'name' => 'Engineering Mathematics','code' => 'MATH001'],
    		['subjectcategory_id'=>2,'name' => 'Operating System','code' => 'OS001'],
    		['subjectcategory_id'=>2,'name' => 'Digital Logic Design','code' => 'DLD001'],
    		);

        // Loop through each user above and create the record for them in the database


    	foreach ($subcategories as $subcategory)
    	{
    		Subjectsubcategory::create($subcategory);
    	}

    	Model::reguard();
    }
}
