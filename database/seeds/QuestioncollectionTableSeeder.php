<?php

use Illuminate\Database\Seeder;
use Illuminate\Database\Eloquent\Model;
use App\Questiontype;
use App\Questioncollection;
use App\User;

class QuestioncollectionTableSeeder extends Seeder
{
    /** 
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	Model::unguard();

    	DB::table('questioncollections')->delete();

        $user = User::first();

        $questions = array(

            ['questiontype_id'=>1,'question_text' => 'Match the following','marks'=>4,'time'=>120,'difficulty'=>1,'random_id'=>mt_rand(),'create_userid'=> $user['id'],'update_userid'=>$user['id']
            ],

            ['questiontype_id'=>2,'question_text' => 'Which of these are related to nuclear size','marks'=>4,'time'=>120,'difficulty'=>1,'random_id'=>mt_rand(),'create_userid'=> $user['id'],'update_userid'=>$user['id']
            ],

            ['questiontype_id'=>3,'question_text' => 'Who is the finance minister of India','marks'=>4,'time'=>120,'difficulty'=>2,'random_id'=>mt_rand(),'create_userid'=> $user['id'],'update_userid'=>$user['id']
            ],

            ['questiontype_id'=>4,'question_text' => 'Ishlamabad is the capital of pakistan','marks'=>4,'time'=>120,'difficulty'=>3,'random_id'=>mt_rand(),'create_userid'=> $user['id'],'update_userid'=>$user['id']
            ],

            ['questiontype_id'=>5,'question_text' => '__________ is the prime minister of India','marks'=>4,'time'=>120,'difficulty'=>1,'random_id'=>mt_rand(),'create_userid'=> $user['id'],'update_userid'=>$user['id']
            ],

            ['questiontype_id'=>6,'question_text' => 'This is text for descriptive question','marks'=>4,'time'=>120,'difficulty'=>4,'random_id'=>mt_rand(),'create_userid'=> $user['id'],'update_userid'=>$user['id']
            ],

            );

        // Loop through each user above and create the record for them in the database

       foreach ($questions as $questiondata)
        {

         $question = new Questioncollection();
         $question->question_text = $questiondata['question_text'];
         $question->marks = $questiondata['marks'];
         $question->time = $questiondata['time'];
         $question->difficulty = $questiondata['difficulty'];
         $question->random_id = $questiondata['random_id'].'_'.mt_rand();
         $question->create_userid = $questiondata['create_userid'];
         $question->update_userid = $questiondata['update_userid'];
         $question->questiontype_id = $questiondata['questiontype_id'];
         $question->save();


     }

     Model::reguard();
 }
} 
