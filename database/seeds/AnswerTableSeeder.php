<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Answer;
use App\User;

class AnswerTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Model::unguard();

    	DB::table('answers')->delete();

    	$questions = array(

    		['questionnaire_id'=>1,'option_text' => 'Tajmahal','option_choice'=>'Agra','answer'=>'true'],
    		['questionnaire_id'=>1,'option_text' => 'Gatway of India','option_choice'=>'Mumbai','answer'=>'true'],
    		['questionnaire_id'=>1,'option_text' => 'India gate','option_choice'=>'Delhi','answer'=>'true'],
    		['questionnaire_id'=>1,'option_text' => 'kumbh','option_choice'=>'Allahabad','answer'=>'true'],

    		['questionnaire_id'=>2,'option_text' => 'Fermi','option_choice'=>'A','answer'=>'true'],
    		['questionnaire_id'=>2,'option_text' => 'Angstrog','option_choice'=>'B','answer'=>'false'],
    		['questionnaire_id'=>2,'option_text' => '10 power -15','option_choice'=>'C','answer'=>'true'],
    		['questionnaire_id'=>2,'option_text' => 'Newton','option_choice'=>'D','answer'=>'false'],

    		['questionnaire_id'=>3,'option_text' => 'Sushma Swaraj','option_choice'=>'A','answer'=>'false'],
    		['questionnaire_id'=>3,'option_text' => 'Arun Jetley','option_choice'=>'B','answer'=>'true'],
    		['questionnaire_id'=>3,'option_text' => 'V.K.Singh ','option_choice'=>'C','answer'=>'false'],
    		['questionnaire_id'=>3,'option_text' => 'Mayawati','option_choice'=>'D','answer'=>'false'],

    		['questionnaire_id'=>4,'option_text' => '','option_choice'=>'','answer'=>'true'],

    		['questionnaire_id'=>5,'option_text' => '','option_choice'=>'','answer'=>'Narendra Modi'],

    		['questionnaire_id'=>6,'option_text' => '','option_choice'=>'','answer'=>'Text for descriptive answer'],


    		);

        // Loop through each user above and create the record for them in the database

    	foreach ($questions as $question)
    	{
    		
    		$answer = new Answer();
    		$answer->questionnaire_id = $question['questionnaire_id'];
    		$answer->option_text = $question['option_text'];
    		$answer->option_choice = $question['option_choice'];
    		$answer->answer = $question['answer'];
    		$answer->save();

    	}

    	Model::reguard();
    }
}
