<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Answercollection;
use App\User;

class AnswercollectionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Model::unguard();

    	DB::table('answercollections')->delete();

    	$questions = array(

    		['question_id'=>1,'option_text' => 'Tajmahal','option_choice'=>'Agra','answer'=>'true'],
    		['question_id'=>1,'option_text' => 'Gatway of India','option_choice'=>'Mumbai','answer'=>'true'],
    		['question_id'=>1,'option_text' => 'India gate','option_choice'=>'Delhi','answer'=>'true'],
    		['question_id'=>1,'option_text' => 'kumbh','option_choice'=>'Allahabad','answer'=>'true'],

    		['question_id'=>2,'option_text' => 'Fermi','option_choice'=>'A','answer'=>'true'],
    		['question_id'=>2,'option_text' => 'Angstrog','option_choice'=>'B','answer'=>'false'],
    		['question_id'=>2,'option_text' => '10 power -15','option_choice'=>'C','answer'=>'true'],
    		['question_id'=>2,'option_text' => 'Newton','option_choice'=>'D','answer'=>'false'],

    		['question_id'=>3,'option_text' => 'Sushma Swaraj','option_choice'=>'A','answer'=>'false'],
    		['question_id'=>3,'option_text' => 'Arun Jetley','option_choice'=>'B','answer'=>'true'],
    		['question_id'=>3,'option_text' => 'V.K.Singh ','option_choice'=>'C','answer'=>'false'],
    		['question_id'=>3,'option_text' => 'Mayawati','option_choice'=>'D','answer'=>'false'],

    		['question_id'=>4,'option_text' => '','option_choice'=>'','answer'=>'true'],

    		['question_id'=>5,'option_text' => '','option_choice'=>'','answer'=>'Narendra Modi'],

    		['question_id'=>6,'option_text' => '','option_choice'=>'','answer'=>'Text for descriptive answer'],


    		);

        // Loop through each user above and create the record for them in the database

    	foreach ($questions as $question)
    	{
    		
    		$answer = new Answercollection();
    		$answer->question_id = $question['question_id'];
    		$answer->option_text = $question['option_text'];
    		$answer->option_choice = $question['option_choice'];
    		$answer->answer = $question['answer'];
    		$answer->save();

    	}

    	Model::reguard();
    }
}
