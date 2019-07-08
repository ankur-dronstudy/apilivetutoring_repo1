<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\models\ClassRoom;
use App\library\Functions;
use App\Classes\SSAuth;
use DB;

class ContentController extends Controller {

    public function contentInstruction(Request $request) {
		$result['status'] = array('statusCode' => 0, 'message' => 'You are not Authorize to take instruction', 'timeStamp'=>'0000-00-00 00:00:00:000000');
		$studentId = $request->get('studentId');
		$query = "SELECT ur.id FROM users ur WHERE ur.license_id = '$studentId'";
		$student_details = DB::select($query);

		if (count($student_details)) {
			$user_id = $student_details[0]->id;
			$classEnrollStatus = Functions::classEnrollCheck($user_id, 5, null);
			if ($classEnrollStatus) {
				$class_id = $classEnrollStatus['class_id'];
				// $teacher_id = Functions::userClassEnrolled($class_id,4);
				// $teacher_id = $teacher_id[0]->id;
				// if (count($teacher_id)) {
				//print_r($user_id);
				$updateConnectionStatus = Functions::updateConnectionStatus($user_id, $class_id, 1);
				
				// } else {
				// 	$result['status'] = array('statusCode' => 5, 'message' => 'Teacher has left the class');
				// 	return response()->json($result)->header('Content-Type', 'application/json');
				// }

				// $teacherLiveStatus = Functions::userPresenceCheck($teacher_id, $class_id);
				// if ($teacherLiveStatus) {
					$userPresenceCheck = Functions::userPresenceCheck($user_id, $class_id);
					//if ($userPresenceCheck) {
						$playlistInstruction = Functions::getInstruction($class_id);
						$status = $playlistInstruction['status'];
						if ($status) {
							$inst_time = $playlistInstruction['inst_time'];
							$inst_time = gmdate('Y-m-d h:i:s:u', $inst_time);
							$data['status'] = array('statusCode' => 1, 'message' => 'Success', 'timeStamp'=>$inst_time);
							$data['command'] = json_decode($playlistInstruction['instruction']);
							return $data;
							
						} else {
							$result['status'] = array('statusCode' => 4, 'message' => 'No instruction to play', 'timeStamp'=>'0000-00-00 00:00:00:000000');
						}
					// } else {
					// 	$result['status'] = array('statusCode' => 3, 'message' => 'User is blocked in the live class', 'timeStamp'=>'0000-00-00 00:00:00:000000');
					// }	

				// } else {
					
				// 	$result['status'] = array('statusCode' => 2, 'message' => 'Teacher has dismissed the class', 'timeStamp'=>'0000-00-00 00:00:00:000000');
				// }
			} 
		}

		return response()->json($result)->header('Content-Type', 'application/json');
	}

	public function classRoomAccessId(Request $request) {
    	$result['status'] = array('statusCode' => 0, 'message' => 'Can not access any class');
        $user_id = SSAuth::user()->id;
		$userEnrolledClass = Functions::userEnrolledClass($user_id, 4);
		
		if (count($userEnrolledClass)) {
			$result['status'] = array('statusCode' => 1, 'message' => 'Success');
			$result['classAccessDetails'] = $userEnrolledClass;
		}
		
		return response()->json($result)->header('Content-Type', 'application/json');
    }

    public function subTopicDetails(Request $request) {
    	$result['status'] = array('statusCode' => 0, 'message' => 'Can not access any class');
    	$stId = $request->get('stId');
	$query = "SELECT cs.id, cs.start_time as startTime, cs.end_time as stopTime, name, file_name as videoName, video_path as videoPath, player_type as playerName, player_path as playerPath, video_length as videoLength FROM classroom_subtopics cs WHERE cs.id = '$stId'";
	$subTopicDetails = DB::select($query);
		
		if (count($subTopicDetails)) {
			$result['status'] = array('statusCode' => 1, 'message' => 'Success');
			$result['subTopicDetails'] = $subTopicDetails[0];
		}
		
		return response()->json($result)->header('Content-Type', 'application/json');
    }


}

