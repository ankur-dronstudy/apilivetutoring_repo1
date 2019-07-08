<?php

namespace App\Classes;

use App\library\Functions;
use DB;
//use App\models\News;
use App\models\Quiz;

//use App\models\ForumPost;
//use App\models\ForumThread;
//use App\models\Books;

class QuizLibrary {

    public $quiz;

    public function __construct($quiz) {
        $this->quiz = $quiz;
    }

    public function __destruct() {
        
    }

    public function userQuizResult($uqid) {
        $data = [];

        $result = DB::select("SELECT user_quiz_questions.*,user_quiz.total_marks as total_marks,UpdatedAt-(CreatedAt+pause_time) as time_taken,quizzes.totaltime as totaltime  FROM user_quiz_questions "
                        . "JOIN user_quiz ON user_quiz.id=user_quiz_questions.user_quiz_id JOIN quizzes ON quizzes.id=user_quiz.quiz_id WHERE user_quiz_id=$uqid");

        $data['time_taken'] = $result[0]->time_taken;
        $data['totaltime'] = $result[0]->totaltime;
        $data['totalQuestion'] = 0;
        $data['positiveMarks'] = 0;
        $data['negativeMarks'] = 0;
        $data['positiveCount'] = 0;
        $data['negativeCount'] = 0;
        $data['totalAttempted'] = 0;
        $data['totalNotAttempted'] = 0;
        $data['totalMarks'] = 0;
        for ($i = 0; $i < count($result); $i++) {
            $gotMarks = $result[$i]->total;
            $data['totalMarks'] = $data['totalMarks'] + $result[$i]->marks;
            $data['totalQuestion'] = $data['totalQuestion'] + 1;
            if ($gotMarks > 0) {
                $data['positiveCount'] = $data['positiveCount'] + 1;
                $data['positiveMarks'] = $data['positiveMarks'] + $gotMarks;
            } else if ($gotMarks < 0) {
                $data['negativeCount'] = $data['negativeCount'] + 1;
                $data['negativeMarks'] = $data['negativeMarks'] + $gotMarks;
            }
            $getStatus = $result[$i]->status;
            if ($getStatus == 'attempted') {
                $data['totalAttempted'] = $data['totalAttempted'] + 1;
            } else if ($getStatus == 'notattempt') {
                $data['totalNotAttempted'] = $data['totalNotAttempted'] + 1;
            }
        }
        $data['marksObtained'] = $data['positiveMarks'] + $data['negativeMarks'];
        $data['marksPercent'] = $data['marksObtained'] * 100 / $data['totalMarks'];

        return $data;
    }

    public function quizStatus() {
        $status = 0;
        $currentTime = time();
        $activeOn = $this->quiz->active_on;
        $expireOn = $this->quiz->expire_on;
        if ($currentTime < $activeOn) {
            $status = 2;
        } elseif (($currentTime < $expireOn) && ($currentTime > $activeOn)) {
            $status = 1;
        } elseif ($currentTime > $expireOn) {
            $status = 0;
        }
        return $status;
    }

}
