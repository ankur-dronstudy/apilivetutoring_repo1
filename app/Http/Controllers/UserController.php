<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\models\User;
use DB;
use App\Classes\SSAuth;
use App\models\Examinfo;
use App\models\Subject;
use App\models\UserSubject;
use App\models\UserOnboardDetail;
use App\library\Functions;
use App\models\Referral;
use App\Classes\SSMail;
use App\models\EmailTemplate;
use App\models\CourseAttendanceDate;

class UserController extends Controller {

    public function index() {
        return response([], 200, ['Content-Type' => 'application/json']);
    }

    public function isOnboarded(Request $request) {
        $response = [ 'status' => false];

        $uid = SSAuth::user()->id;

        if (isset($uid) && ( $uid !== '' )) {
            if (User::isOnboarded($uid) == 1) {
                $response['status'] = true;
            }
        }
        return response($response, 200, ['Content-Type' => 'application/json']);
    }

    public function updateOnboard(Request $request) {
        $response = [ 'status' => false];
        $data = [];
        $data['exam_id'] = $request->get('eid');
        $data['subject_id'] = $request->get('sid');
        $data['program'] = $request->get('program');

        $user_id = SSAuth::user()->id;

        $data['exam'] = Examinfo::where('id', $data['exam_id'])->value('title');

        $details = UserOnboardDetail::updateOnboard($user_id, $data);

        UserSubject::clearUserSubjects($user_id);
        $usersubject = true;
        if (!is_null($data['subject_id']) && ( intval($data['subject_id']) > 0 )) {
            $usersubject = UserSubject::updateSubject($user_id, $data['subject_id']);
        }

        if ($details && $usersubject) {
            User::updateOnboard($user_id, 1);
            $response['status'] = true;
        }
        return response($response, 200, ['Content-Type' => 'application/json']);
    }

    public function getOnboardDetail(Request $request) {
        $response = [ 'status' => false];
        $user = SSAuth::user();
        $user_id = $user->id;
        $details = UserOnboardDetail::where('user_onboard_details.user_id', $user_id)
        ->first(['exam_id', 'exam_name', 'program']);

        if (!is_null($details)) {
            $onboardDetails['exam_id'] = $details->exam_id;
            $onboardDetails['exam_name'] = $details->exam_name;
            $onboardDetails['program'] = $details->program;
            $onboardDetails['subjects'] = [];
            $onboardDetails['user'] = [
                'name' => $user->first_name . ' ' . $user->last_name,
                'img' => Functions::user_pic($user_id),
                'email' => $user->email,
                'mobile' => $user->mobile
            ];
            $subjects = UserSubject::getUserSubjects($user_id);
            foreach ($subjects as $subject) {
                $onboardDetails['subjects'][] = $subject;
            }
            $response['status'] = true;
            $response['data'] = $onboardDetails;
        } else {
            unset($details);
        }
        return response($response, 200, ['Content-Type' => 'application/json']);
    }

    public function referUser(Request $request) {
        $response = [ 'status' => false];
        $user = SSAuth::user();
        $name = $user->first_name . ' ' . $user->last_name;
        $data = [];
        $data['refer_eid'] = $request->get('rid');
        $data['exam_id'] = $request->get('eid');
        $data['url'] = $request->get('url');
        $data['user_id'] = SSAuth::user()->id;
        $data['discount'] = 10;
        $code = Functions::generating_random_string(6, 'App\models\Referral', 'code');
        $referr = Referral::create($data);
        $baselink = \Config::get('scholar.link.ru');
        $postpara = 'e=' . $data['refer_eid'] . '&t=' . $code;
        $jdata = 'rcode=' . base64_encode($postpara);
        $link = $baselink . $jdata;

        $to = [];
        $mail = new SSMail();
        $type = 'refer_link';
        $to = [$data['refer_eid']];
        $d = [];
        $d['link'] = $link;
        $d['name'] = $name;
        $data = EmailTemplate::get_template($d, $type);
        $cc = [];
        $bcc = [];
        $status = $mail->send($to, $cc, $bcc, $data['subject'], $data['html']);
        if ($status == true) {
            $response['status'] = true;
        }
        return response($response, 200, ['Content-Type' => 'application/json']);
    }

    public function browseCourseList(Request $request) {
        $response = ['status' => false];
        $cid1 = $request->get('cid1'); // Main Course
        $cid2 = $request->get('cid2'); // Browsing course
        $cid1_members = $this->courseMember($cid1);
        $cid2_members = $this->courseMember($cid2);

        if (count($cid1_members) > count($cid2_members)) {
            $large_array = $cid1_members;
            $small_array = $cid2_members;
        } else {
            $large_array = $cid2_members;
            $small_array = $cid1_members;
        }

        $userList = array_values(array_udiff($large_array, $small_array, array($this, 'udiffCompare')));

        if ($userList) {
            $response['status'] = true;
            $response['data'] = $userList;
        }

        return response($response, 200, ['Content-Type' => 'application/json']);
    }

    public function userList(Request $request) {
        $response = ['status' => false];
        $user_id = SSAuth::user()->id;
        

        $college_id = SSAuth::user()->college_id;
        $cid = $request->get('cid');
        $rid = $request->get('rid');
        $gid = $request->get('group_id');        

        if (isset($cid) && isset($rid)) {
            
            $shift_id = Functions::getShiftId($cid);

            $courseMember = DB::select("SELECT ce.user_id, us.name FROM course_enrolments ce JOIN users us on us.id = ce.user_id WHERE ce.course_id = '$cid' AND ce.role_id = '$rid' AND us.shift = '$shift_id' ");

            $userList = DB::select("SELECT u.id as user_id, u.name FROM users u JOIN role_user ru ON ru.user_id = u.id WHERE ru.role_id='$rid' AND u.college_id = '$college_id' AND u.shift = '$shift_id' ");

            $userList = array_values(array_udiff($userList, $courseMember, array($this, 'udiffCompare')));
        } else {

            $userList = DB::select("SELECT id as user_id, name, pic as img, user_details FROM users u WHERE u.id!='$user_id' AND onboarded=1 AND u.college_id = '$college_id'");

            $userinterest = DB::select("SELECT si.name AS i_name, us.user_id, i.id AS i_id FROM user_subinterests us LEFT JOIN sub_interests si ON si.id = us.subinterest_id LEFT JOIN interests i ON i.id = si.interest_id");

            foreach ($userList as $user) {
                $user->Areas_of_Interest = [];
                $user->Exam_of_Interest = [];
                $user->img = Functions::user_pic($user->user_id);
                $role = User::roles($user->user_id);
                $user->role_id = (int) $role['role_id'];
                $user->user_details = json_decode($user->user_details);
                if ($user->user_details === NULL) {
                    $user->user_details = false;
                }

                $userEnrolmentCount = Functions::userEnrolmentCount($user->user_id);
                $user->course_count = $userEnrolmentCount['course_count'];
                $user->group_count = $userEnrolmentCount['group_count'];

                foreach ($userinterest as $ui) {
                    if ($user->user_id == $ui->user_id) {
                        if ($ui->i_id == 1) {
                            $user->Areas_of_Interest[] = $ui->i_name;
                        } elseif ($ui->i_id == 2) {
                            $user->Exam_of_Interest[] = $ui->i_name;
                        }
                    }
                }
            }

            $groupUser = DB::select("SELECT user_id from group_enrollments WHERE group_id='$gid' ");

            $userList = array_values(array_udiff($userList, $groupUser, array($this, 'udiffCompare')));
        }

        if ($userList) {
            $response['status'] = true;
            $response['data'] = $userList;
        }
        return response($response, 200, ['Content-Type' => 'application/json']);
    }

    // public function userList(Request $request){
    //     $response = ['status' => false];
    //     $cid = $request->get('cid');
    //     $courseMember = DB::select("SELECT ce.user_id, us.name FROM course_enrolments ce JOIN users us on us.id = ce.user_id WHERE ce.course_id = '$cid'");
    //     $userList = DB::select("SELECT id as user_id, name FROM users");
    //     $userList = array_values(array_udiff($userList, $courseMember, array($this, 'udiffCompare')));
    //     if ($userList) {
    //         $response['status'] = true;
    //         $response['data'] = $userList;
    //     }
    //     return response($response, 200, ['Content-Type' => 'application/json']);
    // }



    public function udiffCompare($a, $b) {
        return $a->user_id - $b->user_id;
    }

    private function courseMember($cid) {
        $courseMember = DB::select("SELECT ce.user_id, us.name, ce.role_id FROM course_enrolments ce JOIN users us on us.id = ce.user_id WHERE ce.course_id = '$cid'");
        return $courseMember;
    }

    // public function userList(Request $request){
    //     $cid = $request->get('cid');
    //     $response = ['status' => false];
    //     //$userList = DB::select("SELECT us.id as user_id, name, ce.course_id FROM users us LEFT JOIN course_enrolments ce on ce.user_id = us.id GROUP BY us.id");
    //     $u_id=DB::select("SELECT user_id from courses where id='$cid'");
    //     $uid=$u_id[0]->user_id;
    //     $userList = DB::select("SELECT us.id as user_id, name, ce.course_id FROM users us LEFT JOIN course_enrolments ce on ce.user_id = us.id");
    //     foreach ($userList as $key => $value) {
    //         $course_id = $value->course_id;
    //         $user_id = $value->user_id;
    //         if ($course_id == $cid || $user_id == $uid) {
    //             unset($userList[$key]);
    //         }
    //         unset($value->course_id);
    //     }
    //     $userList = array_values(array_unique($userList,SORT_REGULAR));
    //     if ($userList) {
    //         $response['status'] = true;
    //         $response['data'] = $userList;
    //     }
    //     return response($response, 200, ['Content-Type' => 'application/json']);
    // }

    /* Ankur */
    // Teacher Courses
    public function userCourse(Request $request) {
        $response = ['status' => false];
        $user_id = SSAuth::user()->id;
        $course_id = $request->get('cid');
        $courseList = DB::select("SELECT id as course_id, name FROM courses WHERE user_id = '$user_id' AND type=1");

        foreach ($courseList as $key => $value) {
            $cid = $value->course_id;
            if ($cid == $course_id) {
                unset($courseList[$key]);
            }
        }

        if (count($courseList > 0)) {
            $response['status'] = true;
            $response['data'] = $courseList;
        }

        return response($response);
    }

    public function studentName(Request $request) {
        $response = ['status' => false];
        $uid = $request->get('student_id');
        $data = DB::select("SELECT id,name from users WHERE id='$uid'");

        if ($data) {
            $response['status'] = true;
            $response['data'] = $data[0];
        }
        return response($response)->header('Content-Type', 'application/json');
    }

    public function userEnrolledCourse(Request $request) {
        $response = ['status' => false];
        $uid = $request->get('uid');
        $user_id = SSAuth::user()->id;

        $data = DB::select("SELECT u.id as id, u.name, u.pic as img, u.email, u.mobile, u.gender, u.house, u.shift, u.religion, u.session, u.registration_id, u.unique_id, u.guardian_details, ce.course_id, ce.start_date, ce.end_date, ce.status as ce_status, c.name as course_name, c.image, c.status as c_status, c.type, u.user_details FROM users u LEFT JOIN course_enrolments ce ON ce.user_id = u.id LEFT JOIN courses c ON c.id = ce.course_id WHERE u.id = '$uid' AND c.type = 1 ORDER BY c.sorting_order ASC");

        // print_r($data);
        // die();

        // $data = DB::select("SELECT u.id as id, u.name, u.pic as img, u.email, u.mobile, u.gender, u.house, u.shift, u.religion, u.session, ce.course_id, ce.start_date, ce.end_date, ce.status as ce_status, c.name as course_name, c.image, c.status as c_status, c.type, u.user_details, g.id as group_id, g.name as group_name, g.status as g_status ,g.image as group_image, g.type as g_type, ge.status as ge_status, ge.CreatedAt, ge.UpdatedAt FROM users u LEFT JOIN course_enrolments ce ON ce.user_id = u.id LEFT JOIN courses c ON c.id = ce.course_id LEFT JOIN group_enrollments ge ON ge.user_id = u.id LEFT JOIN groups g ON g.id=ge.group_id WHERE u.id = '$uid' AND c.type = 1 ORDER BY c.sorting_order ASC");

        if ($data) {
            $arr = [];
            $course = [];
            $group = [];

            foreach ($data as $key => $value) {
                $array = [];
                $array1 = [];
                $arr['id'] = $value->id;
                $arr['name'] = $value->name;
                $arr['gender'] = 'Male';

                if ($value->shift == 1) {
                    $arr['shift'] = "Morning Shift";
                } else if ($value->shift == 2) {
                    $arr['shift'] = "Evening Shift";
                } else {
                    $arr['shift'] = "No Shift";
                }

                if ($value->religion == 1) {
                    $arr['religion'] = "Hindu";
                } else if ($value->religion == 2) {
                    $arr['religion'] = "Muslim";
                } else if ($value->religion == 3) {
                    $arr['religion'] = "Sikh";
                } else if ($value->religion == 4) {
                    $arr['religion'] = "Jain";
                } else if ($value->religion == 5) {
                    $arr['religion'] = "Christian";
                } else if ($value->religion == 6) {
                    $arr['religion'] = "Buddhist";
                } else if ($value->religion == 7) {
                    $arr['religion'] = "Others";
                }

                $arr['session'] = $value->session; 
                $arr['mobile'] = $value->mobile;
                $arr['house'] = $value->house;
                $arr['registration_id'] = $value->registration_id;
                $arr['unique_id'] = $value->unique_id;

                $data1 = Functions::role($value->id);

                $arr['role'] = $data1['role_id'];
                $arr['role_name'] = $data1['role_name'];

                $arr['user_details'] = json_decode($value->user_details);
                $arr['guardian_details'] = json_decode($value->guardian_details);

                if ($arr['user_details'] === NULL) {
                    $arr['user_details'] = [];
                }
                if ($arr['guardian_details'] === NULL) {
                    $arr['guardian_details'] = [];
                }
                if ($value->gender == 'F') {
                    $arr['gender'] = 'Female';
                }

                $arr['email'] = $value->email;
                $course_id = $value->course_id;

                if ($course_id !== NULL) {
                    $array['c'] = $value->c_status;
                    $array['ce'] = Functions::enrollCheck($uid, $course_id);
                    //$array['ce'] = $value->ce_status;
                    $array['c_type'] = $value->type;
                    $array['course_id'] = $value->course_id;
                    $array['course_name'] = $value->course_name;
                    $array['course_image'] = \Config::get('scholar.coursecdn') . "/" . $value->image;
                    $array['start_date'] = $value->start_date;
                    $array['end_date'] = $value->end_date;
                    $course[] = $array;
                }

                //$group_id = $value->group_id;

                // if ($group_id !== NULL) {
                //     $array1['g'] = $value->g_status;
                //     $array1['ge'] = Functions::enrollGroupCheck($uid, $group_id);
                //     //$array1['ge'] =  $value->ge_status;
                //     $array1['g_type'] = $value->g_type;
                //     $array1['group_id'] = $value->group_id;
                //     $array1['group_name'] = $value->group_name;
                //     $array1['group_image'] = \Config::get('scholar.groupcdn') . "/" . $value->group_image;
                //     $array1['start_date'] = $value->CreatedAt;
                //     $array1['end_date'] = $value->UpdatedAt;
                //     $group[] = $array1;
                // }
            }

            $arr['img'] = Functions::user_pic($uid);
            $arr['course_enrolled'] = array_unique($course, SORT_REGULAR);
            //$arr['group_enrolled'] = array_unique($group, SORT_REGULAR);
            $arr['thread_id'] = Functions::getThread($uid);

            $response['status'] = true;
            $response['data'] = $arr;
        }

        return response($response)->header('Content-Type', 'application/json');
    }

    public function userInterestDetail(Request $request) {
        $response = ['status' => false];
        $uid = $request->get('uid');
        $interest = DB::select("SELECT i.id, i.name, i.color FROM interests i WHERE i.status = 1");

        $userinterest = DB::select("SELECT si.name, si.color, si.bgcolor, i.id FROM user_subinterests us LEFT JOIN sub_interests si ON si.id = us.subinterest_id LEFT JOIN interests i ON i.id = si.interest_id WHERE us.user_id='$uid'");

        $branch = Functions::branchExists($uid);

        if ($branch) {
            $userprogram = DB::select("SELECT ud.program_id, p.name AS program_name, ud.branch_id, b.name AS branch_name, ud.department_id FROM user_details ud JOIN programs p ON ud.program_id=p.id LEFT JOIN branches b ON ud.branch_id=b.id WHERE ud.user_id='$uid'");
        } else {
            $userprogram = DB::select("SELECT ud.program_id, p.name AS program_name, ud.department_id FROM user_details ud LEFT JOIN programs p ON ud.program_id=p.id WHERE ud.user_id='$uid'");
        }


        foreach ($interest as $key => $value) {
            $value->sub_interest = [];
            foreach ($userinterest as $key1 => $value1) {
                if ($value->id === $value1->id) {
                    $value->sub_interest[] = $value1;
                }
            }
        }

        if ($interest) {
            $response['status'] = true;
            $response['data'] = ['program' => $userprogram, 'interests' => $interest];
        }

        return response($response)->header('Content-Type', 'application/json');
    }

    // 0 Submited Not review
    // 1 submited and reviewed
    // 2 not submited

    public function assignmentDashboard(Request $request) {
        $response = ['status' => false];
        $uid = $request->get('uid');
        $course = DB::select("SELECT c.id AS course_id, c.name AS course_name, c.start_date, c.end_date, c.status as c_status, ce.status as ce_status, ce.role_id, ce.user_id FROM course_enrolments ce LEFT JOIN courses c ON c.id = ce.course_id WHERE ce.user_id = '$uid' AND c.type = 1 GROUP BY ce.course_id");

        foreach ($course as $key => $val) {
            if ($val->role_id == 4) {
                $query = DB::select("SELECT ass.id AS assign_id, count(ass_sub.assign_id) as total_submissions, ass.name AS assign_name, ass.credits AS total_credits, ass.start_date, ass.end_date, ass.status, ass.user_id FROM assignements ass JOIN assignement_submissions ass_sub ON ass_sub.assign_id = ass.id WHERE ass.course_id = '$val->course_id' GROUP BY ass_sub.assign_id");
            } else if ($val->role_id == 5) {
                $query = DB::select("SELECT ass.id AS assign_id, ass.name AS assign_name, credits AS total_credits, start_date, end_date, ass_sub.status as astatus, ass_sub.credits_scored AS credit, ass_sub.user_id, ass_sub.text as remark, ass_sub.CreatedAt as submition_date FROM assignements ass LEFT JOIN assignement_submissions ass_sub ON ass_sub.assign_id = ass.id WHERE ass.course_id = '$val->course_id'");
            }

            $class_count = DB::select("SELECT count(*) as count FROM course_enrolments ce WHERE ce.course_id = '$val->course_id' AND role_id = '5'");
            $count = 0;

            if ($class_count) {
                $count = $class_count[0]->count;
            }

            $course_array[] = ['course_id' => $val->course_id, 'course_name' => $val->course_name, 'start_date' => $val->start_date, 'end_date' => $val->end_date, 'c_status' => $val->c_status, 'ce_status' => $val->ce_status, 'class_count' => $count, 'assignments' => array_values($query)];

            foreach ($query as $key1 => $value1) {
                if ($value1->user_id == $uid || $value1->user_id == NULL) {

                } else {
                    unset($query[$key1]);
                }
            }
        }

        if ($course) {
            $response['status'] = true;
            $response['data'] = $course_array;
        }

        return response($response)->header('Content-Type', 'application/json');
    }

    public function attendanceDashboard(Request $request) {
        $response = ['status' => false];
        $uid = $request->get('uid');

        $course = DB::select("SELECT c.id AS course_id, c.name AS course_name, c.start_date, c.end_date, c.status as c_status FROM course_enrolments ce  JOIN courses c ON c.id = ce.course_id WHERE ce.user_id = '$uid' AND c.type = 1");

        foreach ($course as $key => $val) {
            $attendance = CourseAttendanceDate::getAttendance($val->course_id, $uid);
            $val->attendance = $attendance;
            $val->ce_status = Functions::enrollCheck($uid, $val->course_id);
            if ($val->start_date > time()) {
                $val->course_status = "Not Started";
            } else if ($val->start_date < time() && time() < $val->end_date) {
                $val->course_status = "On Going";
            } else if ($val->end_date < time()) {
                $val->course_status = "Completed";
            }
        }

        if ($course) {
            $response['status'] = true;
            $response['data'] = $course;
        }

        return response($response)->header('Content-Type', 'application/json');
    }

    public function countryList(Request $request) {
        $response = ['status' => false];

        $query = DB::select("SELECT * FROM countries");

        if ($query) {
            $response['status'] = true;
            $response['data'] = $query;
        }
        return response($response)->header('Content-Type', 'application/json');
    }

    public function stateList(Request $request) {
        $response = ['status' => false];
        $country_id = $request->get('cid');

        $query = DB::select("SELECT * FROM states WHERE country_id='$country_id' ");

        if ($query) {
            $response['status'] = true;
            $response['data'] = $query;
        }
        return response($response)->header('Content-Type', 'application/json');
    }

    public function cityList(Request $request) {
        $response = ['status' => false];
        $state_id = $request->get('sid');

        $query = DB::select("SELECT * FROM cities WHERE state_id='$state_id' ");

        if ($query) {
            $response['status'] = true;
            $response['data'] = $query;
        }
        return response($response)->header('Content-Type', 'application/json');
    }

    public function allUserList(Request $request) {
        $response = ['status' => false];
        //$user_id = SSAuth::user()->id;
        //$college_id = SSAuth::user()->college_id;
        $cid = $request->get('cid');
        $sid = $request->get('sid');
        $rid = $request->get('rid');

        $userList = DB::select("SELECT u.id , u.name, u.email, u.dob, u.health_details,u.user_details, u.category,u.first_name,u.last_name,u.academic_year, u.CreatedAt as sd, u.is_active as publish, u.gender, u.shift, u.mobile ,u.pic as img, c.name as category_name, ce.course_id as class_id ,courses.name as class_name FROM users u JOIN role_user ru ON ru.user_id = u.id JOIN categories as c on u.category = c.id JOIN course_enrolments as ce on u.id = ce.user_id
          JOIN courses  ON ce.course_id = courses.id  WHERE ru.role_id='$rid' AND (u.college_id = '$cid' AND u.shift = '$sid') GROUP BY ce.user_id");

        foreach ($userList as $user) {
            //$user->img = Functions::user_pic($user->id);
            if (isset($user->health_details)) {
                $user->health_details = json_decode($user->health_details);
            }
            if ($user->publish == 1) {
                $user->publish = true;
            } else {
                $user->publish = false;
            }
            
        }


        if ($userList) {
            $response['status'] = true;
            $response['data'] = $userList;
        }
        return response($response, 200, ['Content-Type' => 'application/json']);
    }

    public function roleUserList(Request $request) {
        $response = ['status' => false];
        //$user_id = SSAuth::user()->id;
        //$college_id = SSAuth::user()->college_id;
        $cid = $request->get('cid');
        $rid = $request->get('rid');
        
        $userList = DB::select("SELECT u.id , u.name, u.email, ru.role_id FROM users u JOIN role_user ru ON ru.user_id = u.id WHERE ru.role_id='$rid' AND u.college_id = '$cid'");

        if ($userList) {
            $response['status'] = true;
            $response['data'] = $userList;
        }
        return response($response, 200, ['Content-Type' => 'application/json']);
    }

    public function userActivityDetail(Request $request) {
        $response = ['status' => false];
        $user_id = $request->get('uid');
        $user_details = json_encode($request->get('user_details'));
        $userDetail = DB::update("UPDATE users SET user_details ='$user_details' WHERE id='$user_id'");

        if ($userDetail) {
            $response['status'] = true;
        }

        return response($response, 200, ['Content-Type' => 'application/json']);
    }

    //parent category

    /*id: 1,
    name: 'Children of Transferable & Non-Transfrable Cent.Government.Emp/Defence Personal'

    id: 2,
    name: 'Children of Cent.Government.Undertaking/Autono.bodies/Institute of higher learning'

    id: 3,
    name: 'Children of Transferable & Non-Transfrable State Govt Employees'

    id: 4,
    name: 'Transferable & Non-Transfrable State Govt. Emp, P.S.U/Autono.bodies/inst. of higher learning under State Govt.'

    id: 5,
    name: 'Others'

    id: 6,
    name: 'Special Despensation'

    //Admission type 
    id: 1,
    name: 'MP quota / HRM'
    
    id: 2,
    name: 'KV TC Admission'
    
    id: 3,
    name: 'Fresh Admission'
    
    id: 4,
    name: 'Special Dispensation Admission'
    
    id: 5,
    name: 'Others'*/


    public function getUserEnrollmentReport(Request $request) {
        $response = ['status' => false];
        $cid = $request->get('cid'); // Course ID
        $sid = $request->get('sid');
        $college_id = $request->get('college_id');

        $user_type = DB::select("SELECT COUNT(*) as count, admission_type, gender, u.category, u.parent_category, u.religion FROM course_enrolments ce JOIN users u on u.id = ce.user_id WHERE ce.role_id = 5 AND ce.course_id = '$cid' GROUP BY u.admission_type, u.gender, u.category, u.parent_category, u.religion");

        // Admission varaible

        $m_total = 0;
        $m_mpQuota_count = 0;
        $m_tc_count = 0;
        $m_freshAdmission_count = 0;
        $m_specialDispensation_count = 0;
        $m_others = 0;

        // Categories Varaible

        $m_general = 0;
        $m_sc = 0;
        $m_st = 0;
        $m_ews = 0;
        $m_obc = 0;
        $m_bpl = 0;
        $m_ph = 0;
        $m_singleChild = 0;

        // Religion Varaible

        $m_hindu = 0;
        $m_muslim = 0;
        $m_sikh = 0;
        $m_jain = 0;
        $m_christian = 0;
        $m_buddhist = 0;
        $m_religion_other = 0;

        // Parent Category Varaible

        $m_1 = 0;
        $m_2 = 0;
        $m_3 = 0;
        $m_4 = 0;
        $m_5 = 0;
        
        // Admission varaible
        
        $f_total = 0;
        $f_mpQuota_count = 0;
        $f_tc_count = 0;
        $f_freshAdmission_count = 0;
        $f_specialDispensation_count = 0;
        $f_others = 0;


        // Categories Varaible

        $f_general = 0;
        $f_sc = 0;
        $f_st = 0;
        $f_ews = 0;
        $f_obc = 0;
        $f_bpl = 0;
        $f_ph = 0;
        $f_singleChild = 0;

        // Religion Varaible

        $f_hindu = 0;
        $f_muslim = 0;
        $f_sikh = 0;
        $f_jain = 0;
        $f_christian = 0;
        $f_buddhist = 0;
        $f_religion_other = 0;

        // Parent Category Varaible

        $f_1 = 0;
        $f_2 = 0;
        $f_3 = 0;
        $f_4 = 0;
        $f_5 = 0;
        
        foreach ($user_type as $key => $value) {
            $admission_type = $value->admission_type;
            $count = $value->count;
            $gender = $value->gender;
            $category = $value->category;
            $religion = $value->religion;
            $parent_category = $value->parent_category;
            
            if ($gender == 'Male') {

                $m_total = $m_total + $count;

                if ($admission_type == 1) {
                    $m_mpQuota_count = $m_mpQuota_count + $count;

                } else if ($admission_type == 2) {
                    $m_tc_count = $m_tc_count + $count;
                    
                } else if ($admission_type == 3) {
                    $m_freshAdmission_count = $m_freshAdmission_count + $count;
                    
                } else if ($admission_type == 4) {
                    $m_specialDispensation_count = $m_specialDispensation_count + $count;
                    
                } else if ($admission_type == 5) {
                    $m_others = $m_others + $count;
                }

                if ($category == 1) {
                    $m_general = $m_general + $count;

                } else if ($category == 2) {
                    $m_sc = $m_sc + $count;
                    
                } else if ($category == 3) {
                    $m_st = $m_st + $count;
                    
                } else if ($category == 4) {
                    $m_ews = $m_ews + $count;
                    
                } else if ($category == 5) {
                    $m_obc = $m_obc + $count;
                    
                } else if ($category == 6) {
                    $m_bpl = $m_bpl + $count;
                    
                } else if ($category == 7) {
                    $m_ph = $m_ph + $count;
                    
                } else if ($category == 8) {
                    $m_singleChild = $m_singleChild + $count;
                    
                }

                if ($religion == 1) {
                    $m_hindu = $m_hindu + $count;
                    
                } else if ($religion == 2) {
                    $m_muslim = $m_muslim + $count;
                    
                } else if ($religion == 3) {
                    $m_sikh = $m_sikh + $count;
                    
                } else if ($religion == 4) {
                    $m_jain = $m_jain + $count;

                } else if ($religion == 5) {
                    $m_christian = $m_christian + $count;
                    
                } else if ($religion == 6) {
                    $m_buddhist = $m_buddhist + $count;
                    
                } else if ($religion == 7) {
                    $m_religion_other = $m_religion_other + $count;
                    
                }

                if ($parent_category == 1) {
                    $m_1 = $m_1 + $count;

                } else if ($parent_category == 2) {
                    $m_2 = $m_2 + $count;
                    
                } else if ($parent_category == 3) {
                    $m_3 = $m_3 + $count;
                    
                } else if ($parent_category == 4) {
                    $m_4 = $m_4 + $count;
                    
                } else if ($parent_category == 5) {
                    $m_5 = $m_5 + $count;
                    
                }

            } else if ($gender == 'Female') {
                $f_total = $f_total + $count;

                if ($admission_type == 1) {
                    $f_mpQuota_count = $f_mpQuota_count + $count;

                } else if ($admission_type == 2) {
                    $f_tc_count = $f_tc_count + $count;
                    
                } else if ($admission_type == 3) {
                    $f_freshAdmission_count = $f_freshAdmission_count + $count;
                    
                } else if ($admission_type == 4) {
                    $f_specialDispensation_count = $f_specialDispensation_count + $count;
                    
                } else if ($admission_type == 5) {
                    $others = $others + $count;
                }

                if ($category == 1) {
                    $f_general = $f_general + $count;

                } else if ($category == 2) {
                    $f_sc = $f_sc + $count;
                    
                } else if ($category == 3) {
                    $f_st = $f_st + $count;
                    
                } else if ($category == 4) {
                    $f_ews = $f_ews + $count;
                    
                } else if ($category == 5) {
                    $f_obc = $f_obc + $count;
                    
                } else if ($category == 6) {
                    $f_bpl = $f_bpl + $count;
                    
                } else if ($category == 7) {
                    $f_ph = $f_ph + $count;
                    
                } else if ($category == 8) {
                    $f_singleChild = $f_singleChild + $count;
                    
                }

                if ($religion == 1) {
                    $f_hindu = $f_hindu + $count;
                    
                } else if ($religion == 2) {
                    $f_muslim = $f_muslim + $count;
                    
                } else if ($religion == 3) {
                    $f_sikh = $f_sikh + $count;
                    
                } else if ($religion == 4) {
                    $f_jain = $f_jain + $count;

                } else if ($religion == 5) {
                    $f_christian = $f_christian + $count;
                    
                } else if ($religion == 6) {
                    $f_buddhist = $f_buddhist + $count;
                    
                } else if ($religion == 7) {
                    $f_religion_other = $f_religion_other + $count;
                }

                if ($parent_category == 1) {
                    $f_1 = $f_1 + $count;

                } else if ($parent_category == 2) {
                    $f_2 = $f_2 + $count;
                    
                } else if ($parent_category == 3) {
                    $f_3 = $f_3 + $count;
                    
                } else if ($parent_category == 4) {
                    $f_4 = $f_4 + $count;
                    
                } else if ($parent_category == 5) {
                    $f_5 = $f_5 + $count;
                    
                }

            }
        }

        
        $data['admission_type'] = ['kv_admission_count'=>['male'=>$m_tc_count, 'female'=>                         $f_tc_count],
        'MP_quota_count'=>['male'=>$m_mpQuota_count, 'female'=>
        $f_mpQuota_count],
        'm_freshAdmission_count'=>['male'=>$m_freshAdmission_count, 'female'=>$f_freshAdmission_count],
        'specialDispensation_count'=>['male'=>$m_specialDispensation_count, 'female'=>$f_specialDispensation_count],
        'm_others_count'=>['male'=>$m_others, 'female'=>
        $m_others]
    ];

    $data['categories'] = ['general'=>['male'=>$m_general, 'female'=>                         $f_general],
    'sc'=>['male'=>$m_sc, 'female'=>
    $f_sc],
    'st'=>['male'=>$m_st, 'female'=>$f_st],
    'ews'=>['male'=>$m_ews, 'female'=>$f_ews],
    'obc'=>['male'=>$m_obc, 'female'=>$f_obc],
    'bpl'=>['male'=>$m_bpl, 'female'=>$f_bpl],
    'ph'=>['male'=>$m_ph, 'female'=>$f_ph],
    'Single_Child'=>['male'=>$m_singleChild, 'female'=>$f_singleChild]
]; 

$data['religion'] = ['hindu'=>['male'=>$m_hindu, 'female'=>                         $f_hindu],
'muslim'=>['male'=>$m_muslim, 'female'=>
$f_muslim],
'sikh'=>['male'=>$m_sikh, 'female'=>$f_sikh],
'jain'=>['male'=>$m_jain, 'female'=>$f_jain],
'christian'=>['male'=>$m_christian, 'female'=>$f_christian],
'buddhist'=>['male'=>$m_buddhist, 'female'=>$f_buddhist],
'other'=>['male'=>$m_religion_other, 'female'=>$f_religion_other]
];

$data['parent_category'] = ['category_1'=>['male'=>$m_1, 'female'=>                         $f_1],
'category_2'=>['male'=>$m_2, 'female'=>
$f_2],
'category_3'=>['male'=>$m_3, 'female'=>$f_3],
'category_4'=>['male'=>$m_4, 'female'=>$f_4],
'category_5'=>['male'=>$m_5, 'female'=>$f_5]
]; 
$data['total_male'] = $m_total;
$data['total_female'] = $f_total;   
$data['grand_total'] = $m_total + $f_total;                         

$data1 = json_encode($data);
$UpdatedAt = time();

$query = "UPDATE `courses` SET `course_monthly_enrolment`= '$data1',`UpdatedAt`= '$UpdatedAt' WHERE id = '$cid'";

$updateCollegeData = DB::update($query);

if ($data1) {
    $response['status'] = true;
    $response['data'] = $data1;
}
return response($response, 200, ['Content-Type' => 'application/json']);
}

}
