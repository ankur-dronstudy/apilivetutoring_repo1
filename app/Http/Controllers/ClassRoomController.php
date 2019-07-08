<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\models\ClassRoom;
use App\library\Functions;
use App\Classes\SSAuth;
use DB;

class ClassRoomController extends Controller {

    public function classRoomAccess(Request $request) {
		$result['status'] = array('statusCode' => 0, 'message' => 'You are not Authorize to Login');
		$class_id = $request->get('class_id');
		$user_id = SSAuth::user()->id;
		
			$cId = $class_id;
			$userJoinedStatus = Functions::userJoinedStatus($user_id, $cId);
			
			if ($userJoinedStatus) {
				$result['status'] = array('statusCode' => 1, 'message' => 'Success');
                                $result['classRoomDetail'] = array('classroomId' => $cId);
				//$result['status'] = array('statusCode' => 0, 'message' => 'Already joined classroom from some other machine');
			} else {
				$joinliveClassroom = Functions::joinliveClassroom($user_id, $cId);
				$result['status'] = array('statusCode' => 1, 'message' => 'Success');	
				$result['classRoomDetail'] = array('classroomId' => $cId);	
			}

	

		return response()->json($result)->header('Content-Type', 'application/json');
	}

	public function studentclassRoomAccess(Request $request) {
		$result['status'] = array('statusCode' => 0, 'message' => 'You are not Authorize to access classroom');
		$cr_id = $request->get('cr_id');
		$user_id = SSAuth::user()->id;
		
		$query = "SELECT cr.id as classroomId, cr.name as classroom_name FROM classrooms cr JOIN class_enrolments ce ON ce.class_id = cr.id WHERE cr.access_id = '$cr_id' AND ce.user_id = '$user_id'";
		
		$classroom_details = DB::select($query);
		$row_count = count($classroom_details);
		
		if ($row_count > 0) {
			$cId = $classroom_details[0]->classroomId;
			$userJoinedStatus = Functions::userJoinedStatus($user_id, $cId);
			
			if ($userJoinedStatus) {
				$result['status'] = array('statusCode' => 0, 'message' => 'Already joined classroom from some other machine');
			} else {
				$joinliveClassroom = Functions::joinliveClassroom($user_id, $cId);
				$result['status'] = array('statusCode' => 1, 'message' => 'Success');	
			}
		}

		return response()->json($result)->header('Content-Type', 'application/json');
	}

	public function leaveClassRoom(Request $request) {
		$result['status'] = array('statusCode' => 0, 'message' => 'Already out of the classroom');
		$cId = $request->get('cId');
		$user_id = SSAuth::user()->id;
		$leaveClassroom = Functions::leaveClassroom($user_id, $cId);
		$leaveClassroom = Functions::deleteClassroomContent($cId);
		$allstudentLeave = Functions::allstudentLeave($cId);
		$result['status'] = array('statusCode' => 1, 'message' => 'Left Classroom Successfully');
		

		return response()->json($result)->header('Content-Type', 'application/json');
	}

	public function classRoomMember(Request $request) {
        $result['status'] = array('statusCode' => 0, 'message' => 'Not Authorized to Access');
		$classroom_id = $request->get('cId');
        $role_id = $request->get('rId');
        $current_time = time();
        
        $member = DB::select("SELECT ce.user_id, u.name, lc.blocked FROM class_enrolments ce LEFT JOIN users u ON u.id = ce.user_id LEFT JOIN live_classrooms lc on lc.user_id = ce.user_id WHERE ce.class_id = '$classroom_id' AND ce.role_id = '$role_id' ORDER BY u.id ASC");



        //block = 0
        //unblock = 1
        // null = 3

        $connected_user = Functions::connectionStatus($classroom_id);
        foreach ($member as $key1 => $value1) {
        	$member[$key1]->status =  "offline";
        	
        	if ($member[$key1]->blocked === null) {
        		$member[$key1]->blocked = 3;	
        	}
        	
        	foreach ($connected_user as $key2 => $value2) {
        		$current_time = time();
        		$current_time = $current_time - 10;

        		$user_id2 = $value2->user_id;
        		$user_id1 = $value1->user_id;
        		$joinedAt = $value2->joinedAt;
				
				if ($user_id1 == $user_id2 && $current_time < $joinedAt) {
        			$member[$key1]->status =  "online";	
        		}
        	}
        }
        
        if (count($member) > 0) {
            $result['status'] = array('statusCode' => 1, 'message' => 'Success');
            $result['userList'] = $member;
        }

        return response()->json($result)->header('Content-Type', 'application/json');
    }

    public function classRoomPlaylist(Request $request) {
    	$result['status'] = array('statusCode' => 0, 'message' => 'Not Authorized to Access');
        $classroom_id = $request->get('cId');
       	$user_id = SSAuth::user()->id;

		$classEnrollStatus = Functions::classEnrollCheck($user_id, 4, $classroom_id);

       	if (count($classEnrollStatus)) {
       		$classPackageId = Functions::classPackageId($classroom_id);
			if (count($classPackageId)) {
				$classPackageId = $classPackageId[0]->class_package_id;
			} else {
				return response()->json($result)->header('Content-Type', 'application/json');
			}

			$chapterList = DB::select("SELECT cc.* FROM classroom_chapters cc WHERE cc.package_id = '$classPackageId'");
			
			$topicList = DB::select("SELECT ct.* FROM classroom_topics ct WHERE ct.package_id = '$classPackageId'");

			$subtopicList = DB::select("SELECT cs.* FROM classroom_subtopics cs WHERE cs.package_id = '$classPackageId'");



			foreach ($chapterList as $key => $value) {
				$chapter_array[] = ['packageId' => $chapterList[$key]->id,'videoName' => $chapterList[$key]->name, 'Topics'=>[]];
				$chapter_id = $chapterList[$key]->id;
				
				foreach ($topicList as $k1 => $v1) {
					$chapter_id1 = $topicList[$k1]->chapter_id;
					$topic_id = $topicList[$k1]->id;

					if ($chapter_id === $chapter_id1) {

						$subtopicList = DB::select("SELECT cs.id, cs.name FROM classroom_subtopics cs WHERE cs.topic_id = '$topic_id'");
						$subtopicList = json_decode(json_encode($subtopicList), True);

						$topic_array = ['packageId' => $topicList[$k1]->id, 'videoName'=>$topicList[$k1]->name, 'subTopics' => $subtopicList];
						$chapter_array[$key]['Topics'][] = $topic_array;
					}
				}
			}

			if (count($chapter_array)) {

				$result['status'] = array('statusCode' => 1, 'message' => 'Success');
				$result['chapters'] = $chapter_array;
				return response()->json($result)->header('Content-Type', 'application/json');
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

  /*  public function submitLiveInstruction(Request $request) {
    	$result['status'] = array('statusCode' => 0, 'message' => 'Can not submit');
        $user_id = SSAuth::user()->id;
	$userEnrolledClass = Functions::userEnrolledClass($user_id, 4);
	$user_id = $userEnrolledClass[0]->id;
		$inst = $request->all();
		$inst = json_encode($inst);
		$updateInstruction = Functions::updateInstruction($user_id, $inst);

		if (count($userEnrolledClass)) {
			$result['status'] = array('statusCode' => 1, 'message' => 'Success');
		}

		return response()->json($result)->header('Content-Type', 'application/json');
     }*/

    public function submitLiveInstruction(Request $request) {
        $result['status'] = array('statusCode' => 0, 'message' => 'Can not submit');
        $user_id = SSAuth::user()->id;
        // $userEnrolledClass = Functions::userEnrolledClass($user_id, 4);
        // $class_id = $userEnrolledClass[0]->class_id;
        $inst1 = $request->all();

        $class_id = $inst1['class_id'];
        $inst['commandType'] = $inst1['commandType'];
        $inst['startTime'] = (int)$inst1['startTime'];
        $inst['stopTime'] = (int)$inst1['stopTime'];
        $inst['videoName'] = $inst1['videoName'];
        $inst['videoLength'] = (int)$inst1['videoLength'];
        $inst['videoPath'] = $inst1['videoPath'];
        $inst['playerName'] = $inst1['playerName'];
        $inst['playerPath'] = $inst1['playerPath'];

		if ($inst1['commandType'] == 'RESUME' && $inst1['startTime'] > 5) {
			$inst['startTime'] = $inst1['startTime'] - 5;
        }

        $inst = json_encode($inst);
        $updateInstruction = Functions::updateInstruction($class_id, $inst);

        if ($updateInstruction) {
                $result['status'] = array('statusCode' => 1, 'message' => 'Success');
        }

        return response()->json($result)->header('Content-Type', 'application/json');
    }


    public function blockMember(Request $request) {
    	$result['status'] = array('statusCode' => 0, 'message' => 'Can not Update');
	$status = $request->get('status');
	$user_id = $request->get('user_id');
	$class_id = $request->get('class_id');
	$updateConnectionStatus = Functions::updateBlock($user_id, $class_id, $status);
	if($updateConnectionStatus){
		$result['status'] = array('statusCode' => 1, 'message' => 'Update');
	}
	return response()->json($result)->header('Content-Type', 'application/json');
    }

}

