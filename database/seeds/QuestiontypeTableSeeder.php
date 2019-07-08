<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Questiontype;

class QuestiontypeTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Model::unguard();

    	DB::table('questiontypes')->delete();

    	$questiontypes = array(

    		['type' => 'Matching Question'],
    		['type' => 'Multiple type question'],
    		['type' => 'True/False'],
    		['type' => 'Match'],
    		['type' => 'Fill in the blanks'],
    		['type' => 'Descriptive'],
    		
    		);

        // Loop through each user above and create the record for them in the database


    	foreach ($questiontypes as $type)
    	{
    		Questiontype::create($type);
    	}

    	Model::reguard();
    }
}
