<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use DB;
use App\library\Functions;

class AnalyticController extends Controller {

    public function quizTopRanks(Request $request) {
        $response = [ 'status' => false];
        $qid = $request->get('qid');

        $response['data'] = DB::select("SELECT user_id,first_name as name,AVG(marks) as avg_marks,count(*) as cnt 
                                        FROM user_quiz as uq 
                                        JOIN users as u ON uq.user_id = u.id 
                                        WHERE quiz_id=" . $qid . ". AND uq.status = 'completed' 
                                        GROUP BY uq.user_id ORDER BY `avg_marks` DESC LIMIT 10");

        if (count($response['data']) > 0) {
            $response['status'] = true;
        } else {
            unset($response['data']);
        }
        return response($response)->header('Content-Type', 'application/json');
    }

    public function userQuizAttemps(Request $request) {
        $response = [ 'status' => false];
        $qid = $request->get('qid');
        $uid = $request->get('uid');
        $response['data'] = DB::select('SELECT marks,updated FROM user_quiz as uq JOIN users as u ON uq.user_id = u.id WHERE quiz_id=' . $qid . ' AND uq.user_id=' . $uid . ' AND uq.status="completed" LIMIT 10');
        if (count($response['data']) > 0) {
            $response['status'] = true;
        } else {
            unset($response['data']);
        }
        return response($response)->header('Content-Type', 'application/json');
    }

    public function gradeWiseUserCount(Request $request) {
        $response = [ 'status' => false];
        $aid = $request->get('asset_id');
        $at = $request->get('asset_type');
        $cid = $request->get('cid');
        $sql = "SELECT user_id FROM course_enrolments WHERE course_id=$cid AND role_id=5";
        $users = $quizResults = DB::select($sql);
        if ($at == "quiz") {
            $sql = "SELECT MAX(marks) AS marks, uq.user_id,uq.total_marks, q.course_id FROM user_quiz uq JOIN quizzes q ON uq.quiz_id=q.id WHERE q.course_id='$cid' AND uq.quiz_id='$aid' AND uq.status='completed' GROUP BY uq.user_id";
            $quizResults = $quizResults = DB::select($sql);
        } else if ($at == "assignment") {
            $sql = "SELECT assm.user_id,credits_scored as marks,credits as total_marks FROM `assignement_submissions` assm JOIN assignements as ass ON ass.id=assm.assign_id WHERE assign_id='$aid'";
            $quizResults = $quizResults = DB::select($sql);
        }

        $array = $this->getgradeCountCal($quizResults, $users, $cid);

        if (count($array) > 0) {
            $response['status'] = true;
            $response['data'] = $array;
        }
        return response($response)->header('Content-Type', 'application/json');
    }

    private function getgradeCountCal($quizResults, $users, $cid) {
        try {
            $quizResultsArray = [];
                foreach ($quizResults as $quizResult) {
                    $quizResultsArray[] = $quizResult->user_id;
                }

                $results = [];
                
                foreach ($users as $user) {
                    foreach ($quizResults as $result) {
                        if ($user->user_id == $result->user_id) {
                            $result->percentage = new \stdClass();
                            $result->percentage = $this->calculatePer($result->total_marks, $result->marks);
                            $result->grade = new \stdClass();
                            $result->grade = Functions::gradeCalculation($result->percentage);
                            $results[] = $result;
                        }
                    }

                    if (!in_array($user->user_id, $quizResultsArray)) {
                        $res = [];
                        $res['user_id'] = $user->user_id;
                        $res['marks'] = 0;
                        $res['total_marks'] = $quizResults[0]->total_marks;
                        $res['course_id'] = $cid;
                        $res['percentage'] = $this->calculatePer($res['total_marks'], $res['marks']);
                        $res['grade'] = Functions::gradeCalculation($res['percentage']);
                        $results[] = (object)$res;
                    }
            }
        } catch (\Exception $e) {
            foreach ($users as $user) {
                $res = [];
                $res['user_id'] = $user->user_id;
                $res['marks'] = 0;
                $res['total_marks'] = 30;
                $res['course_id'] = $cid;
                $res['percentage'] = 0;
                $res['grade'] = 'E';
                $results[] = (object)$res;
            }
        }

        $array = ['A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0];

        foreach ($results as $result) {
            $array[$result->grade] = $array[$result->grade] + 1;
        }

        $array_values = array_values($array);
        $sum_values = array_sum($array_values);
        $array_per = [];
        foreach ($array_values as $value){
            $array_per[] = (int)round($this->calculatePer($sum_values,$value));
        }
        return $array_per;
    }

    private function calculatePer($total, $obt) {
        
        $percentage = ($obt * 100) / $total;
        //echo $percentage.'</br>'; 
        return $percentage;
    }

//    private function calculateAssmentPer($total,$obt){
//        
//        $percentage = ($obt*10)/$total;
//        return $percentage;
//        
//    }
}
