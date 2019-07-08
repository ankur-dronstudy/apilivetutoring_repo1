<?php

namespace App\library;

use Illuminate\Support\Str;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Hmac\Sha256;
use GuzzleHttp\Client;
use Illuminate\Contracts\Filesystem\Filesystem;
use Storage;
use App\models\User;
use App\models\Referral;
use App\models\Otprequest;
use DB;
use App\Classes\SSAuth;
use App\models\CourseEnrolment;
use App\Classes\SSPushNotification;

class Functions {

    private static $activeChannels = [LOG_LOCAL0, LOG_LOCAL1];
    private static $syslogPrefix = [LOG_LOCAL0 => 'route',
    LOG_LOCAL1 => 'quiz'
];

public static function okk() {
    return 'everything is okk';
}

public static function generating_random_string($str_length, $model, $column) {

    $randomize = rand(1, 9) . strtolower(Str::random($length = $str_length - 1));
    $random_exist = $model::where($column, $randomize)->count();
    if ($random_exist > 0)
        $randomize = rand(1, 9) . Str::random($length = $str_length - 1);
    return $randomize;
}

public static function generating_random_number($length, $model, $column) {

    $randomize = mt_rand(100000, 999999);
    $random_exist = $model::where($column, $randomize)->count();
    if ($random_exist > 0)
        $randomize = mt_rand(100000, 999999);
    return $randomize;
}

public static function randWithout($from, $to, array $exceptions) {

    sort($exceptions);
    $number = mt_rand($from, $to - count($exceptions));
    foreach ($exceptions as $exception) {
        if ($number >= $exception) {
            $number++;
        } else {
            break;
        }
    }
    return $number;
}

public static function picupload($data, $user, $dir) {
        //echo 'shailesh';
    list($type, $data) = explode(';', $data);
        // dd($data);
    list(, $data) = explode(',', $data);
    $data = base64_decode($data);
    $unique = $user->id . '_' . time() . '.png';
    $s3 = \Storage::disk('s3');
    $path = $dir . $unique;
    $s3->put($path, $data, 'public');
    return $unique;
}

public static function coursepicupload($cid, $img, $user, $dir) {
    list($type, $img) = explode(';', $img);
    list(, $img) = explode(',', $img);
    $img = base64_decode($img);
    $unique = $user . '_' . $cid . '_' . time() . '.png';
    $s3 = \Storage::disk('s3');
    $path = $dir . $unique;
    $s3->put($path, $img, 'public');
    return $unique;
}

public static function grouppicupload($gid, $img, $uid, $dir) {
    list($type, $img) = explode(';', $img);
    list(, $img) = explode(',', $img);
    $img = base64_decode($img);
    $unique = $uid . '_' . $gid . '_' . time() . '.png';
    $s3 = \Storage::disk('s3');
    $path = $dir . $unique;
    $s3->put($path, $img, 'public');
    return $unique;
}

public static function eventpicupload($img, $asset_type, $asset_id, $dir) {
        // dd($img);
    list($type, $img) = explode(';', $img);
    list(, $img) = explode(',', $img);
    $img = base64_decode($img);
    $unique = $asset_type . '_' . $asset_id . '_' . time() . '.png';
        // dd($unique);
    $s3 = \Storage::disk('s3');
    $path = $dir . $unique;
    $s3->put($path, $img, 'public');
    return $unique;
}

public static function postpicupload($img, $asset_type, $asset_id, $dir) {
        // dd($img);
    list($type, $img) = explode(';', $img);
    list(, $img) = explode(',', $img);
    $img = base64_decode($img);
    $unique = $asset_type . '_' . $asset_id . '_' . time() . '.png';
        // dd($unique);
    $s3 = \Storage::disk('s3');
    $path = $dir . $unique;
    $s3->put($path, $img, 'public');
    return $unique;
}

public static function idcardpicupload($img, $dir, $id, $user_role) {
        // dd($img);
    list($type, $img) = explode(';', $img);
    list(, $img) = explode(',', $img);
    $img = base64_decode($img);
    $unique = $id . '_' . $user_role . '_' . time() . '.png';
        // dd($unique);
    $s3 = \Storage::disk('s3');
    $path = $dir . $unique;
    $s3->put($path, $img, 'public');
    return $unique;
}

public static function assignementupload($data, $user, $dir) {

    $ftype = explode(".", $_FILES['assign_file']['name']);
    $type = $ftype[1];
    $unique = $user->id . '_' . time() . '.' . $type;
    $s3 = \Storage::disk('s3');
        $file = file_get_contents($data['assign_file']);     //->__toString();
        $status = $s3->put($dir . $unique, $file, 'public');
        return $unique;
    }

    public static function pdfupload($data, $dir) {
        $unique = $data['user_id'] . '_' . time();
        $s3 = \Storage::disk('s3');
        $file = file_get_contents($data['topic_file']);
        $status = $s3->put($dir . $unique, $file, 'public');
        return $unique;
    }

    public static function pdfdelete($dir, $urls) {
        $s3 = \Storage::disk('s3');
        $file = $dir . $urls;
        $exists = $s3->has($file);
        if ($exists) {
            $status = $s3->delete($file);
            return $status;
        } else {
            return true;
        }
    }

    public static function tax() {
        return 14;
    }

    public static function logMessage($type, $channel, $message) {

        if (!in_array($channel, self::$activeChannels)) {
            return false;
        }
        var_dump($channel);
        $prefix = self::$syslogPrefix[$channel];
        if (openlog($prefix, LOG_NDELAY, $channel)) {
            syslog($type, $message);
            closelog();
        }
    }

    public static function verifyToken($tokenStr, &$user) {
        $res = false;
        if (isset($tokenStr) && ( $tokenStr !== '' )) {
            $token = (new Parser())->parse((string) $tokenStr); // Parses from a string
            $signer = new Sha256();
            $user = $token->getClaim('user');
            $salt = \Config::get('scholar.jwt.salt');
            $res = $token->verify($signer, $salt);
            unset($token);
            unset($signer);
        }
        return $res;
    }

    public static function curlPostJson($url, $json) {
        $ch = curl_init();
        $params = json_encode($json);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'Content-Length: ' . strlen($params))
    );
        $server_output = curl_exec($ch);
        dd($server_output);
        curl_close($ch);
        return json_decode($server_output);
    }

    public static function curlGet($url, $paramsArr) {
        $ch = curl_init();
        $params = http_build_query($paramsArr);
        curl_setopt($ch, CURLOPT_URL, $url . '?' . $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = curl_exec($ch);
        curl_close($ch);
        return json_decode($server_output);
    }

    public static function curlDelete($url) {

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
        curl_exec($ch);
        curl_close($ch);
    }

    public static function likeCheck($user_id, $pid) {
        $query = DB::select("SELECT * FROM post_likes pl WHERE post_id = $pid AND user_id = $user_id");
        if (count($query)) {
            return true;
        } else {
            return false;
        }
    }

    public static function followCheck($user_id, $pid) {
        $query = DB::select("SELECT * FROM post_follow pf WHERE post_id = $pid AND user_id = $user_id");
        if (count($query)) {
            return true;
        } else {
            return false;
        }
    }

    
    public static function classCheck($user_id) {
        $query = DB::select("SELECT ce.course_id, c.name FROM course_enrolments ce JOIN courses c on c.id = ce.course_id WHERE ce.user_id = '$user_id'");

        if (count($query)) {
            $result['class_id'] = $query[0]->course_id;
            $result['class_name'] = $query[0]->name;
            return $result;
        }
    }

    public static function substituteTeacher($uid, $cid) {
        $substitute_teachers = DB::select("SELECT ce.user_id, ce.special_role,ce.role_id, us.name FROM course_enrolments ce JOIN users us on us.id = ce.user_id WHERE ce.course_id = '$cid' AND ce.role_id = 4 AND ce.user_id != $uid");

        if (count($substitute_teachers)) {
            return $substitute_teachers;
        } else {
            return [];
        }
    }

    public static function substituteTeacherAcknowledgement($uid, $cid, $user_id) {
        $substitute_teachers_ack = DB::select("SELECT * FROM course_enrolments ce WHERE ce.user_id = '$uid' AND ce.course_id = '$cid' AND ce.role_id = 4 AND ce.user_id != $user_id");

        if (count($substitute_teachers_ack)) {
            return 1;
        } else {
            return 0;
        }
    }

    public static function userEnrolmentCount($user_id) {
        $course_query = DB::select("SELECT count(*) as course_count FROM course_enrolments ce WHERE  user_id = $user_id ");
        $course_count = $course_query[0]->course_count;

        $group_query = DB::select("SELECT count(*) as group_count FROM group_enrollments WHERE user_id = $user_id ");
        $group_count = $group_query[0]->group_count;

        $count = ['course_count' => $course_count, 'group_count' => $group_count];

        return $count;
    }

    //0- member doesnot exist
    //1- member exists
    //2- member is blocked

    public static function enrollGroupCheck($user_id, $gid) {
        $query = DB::select("SELECT status FROM group_enrollments WHERE group_id= $gid AND user_id= $user_id");


        if ($query) {
            if ($query[0]->status == 1) {
                return 1;
            } elseif ($query[0]->status == 3) {
                return 3;
            } elseif ($query[0]->status == 2) {
                return 2;
            } elseif ($query[0]->status == 0) {
                return 0;
            }
        } else {
            return 0;
        }
    }

    // public static function joinGroupCheck($user_id, $gid){
    //     $joined=DB::select("SELECT * FROM group_enrollments WHERE group_id='$gid' AND user_id='$user_id'");
    //     if($joined){
    //         return 1;
    //     }else{
    //         return 0;
    //     } 
    //}

    /**
     * 
     * @param type $mobile (919811298958,919811298958,...)
     * @param type $message (this+is+message)
     * @return type
     */
    public static function transactionalSms($mobileArr, $message) {
        $tgDetails = \Config::get('scholar.sms.txtguru');
        $url = $tgDetails['url'];
        $params = [];
        $params['username'] = $tgDetails['username'];
        $params['password'] = $tgDetails['password'];
        $params['source'] = $tgDetails['source'];

        $p = [];
        foreach ($mobileArr as $mobile) {
            $p[] = '91' . $mobile;
        }

        $params['dmobile'] = implode(',', $p);
        //$params['dmobile'] = $mobileArr;
        //var_dump($params);exit;
        unset($p);

        $params['message'] = $message;
        $qp = http_build_query($params);

        $url .= '?' . $qp;


        $client = new Client();
        $response = $client->request('GET', $url);

        unset($client);
        unset($params);

        return $response;
    }

    /**
     * 
     * @param type $mobile (919811298958,919811298958,...)
     * @param type $message (this+is+message)
     * @return type
     */
    public static function transactionalSms1($mobile, $message) {
        $tgDetails = \Config::get('scholar.sms.txtguru');
        $url = $tgDetails['url'];
        $params = [];
        $params['username'] = $tgDetails['username'];
        $params['password'] = $tgDetails['password'];
        $params['source'] = $tgDetails['source'];
        $params['dmobile'] = '91' . $mobile;
        $params['message'] = $message;
        $qp = http_build_query($params);
        $url .= '?' . $qp;
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        if($result == true){
           return true;
       }
       else{
          return false;  
      }

        // $client = new Client();

        // $response = $client->request('GET', $url);

        // unset($client);
        // unset($params);

        // return true;
  }

  public static function user_pic($id) {
    $user_data = User::where('id', $id)->first(array("pic", "gender"));
    $pic = $user_data->pic;
    $gender = $user_data->gender;
    $cdnPath = \Config::get('scholar.imgcdn');

    if ($pic == NUll) {
        if (($gender == 'O') OR ( $gender == NULL)) {
            $pic = $cdnPath . 'default/neutral.png';
        } else if ($gender == 'M') {
            $pic = $cdnPath . 'default/boy.svg';
        } else if ($gender == 'F') {
            $pic = $cdnPath . 'default/girl.svg';
        }
    } else {
        if (!preg_match('/(http|https):\/\//', $pic)) {
            $pic = $cdnPath . 'profile/' . $pic;
        }
    }

    return $pic;
}

public static function user_name($id) {
    $user_data = User::where('id', $id)->first(array("name"));
    $name = $user_data->name;
    return $name;
}

public static function slugCreate($string) {
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
    return $slug;
}

/* Add question to elastic and return id */

public static function curlPostJsonId($url, $json) {
    $ch = curl_init();
    $params = json_encode($json);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Content-Length: ' . strlen($params))
);
    $server_output = curl_exec($ch);
    curl_close($ch);
    return json_decode($server_output);
}

public static function percCal($mark, $tmark) {
    $perc = ($mark / $tmark) * 100;
    return $perc;
}

public static function gradeCalculation($mark) {
    if ($mark >= 90 && $mark <= 100)
        return "A";
    elseif ($mark >= 70 && $mark <= 90)
        return "B";
    elseif ($mark >= 50 && $mark <= 70)
        return "C";
    elseif ($mark >= 30 && $mark <= 50)
        return "D";
    elseif ($mark <= 30)
        return "E";
}

public static function userPermission($detail) {
        //dd($detail);
    $user_id = SSAuth::user()->id;
    $status = false;
    $enrollment = Functions::checkEnrollments($detail->course_id, $user_id);
    if ($enrollment) {
        $status = true;
    }
    return $status;
}

public static function checkEnrollments($course_id, $user_id) {
    $enrolment = CourseEnrolment::where('course_id', $course_id)->where('user_id', $user_id)->first();
    if ($enrolment) {
        return true;
    }
    return false;
}





public static function eventGoingCount($eid) {
    $count = DB::select("SELECT count(user_id) as counts FROM event_interested WHERE event_id='$eid' AND going=1");
    return $count[0]->counts;
}

public static function checkEventInterested($eid, $uid) {
    $exist = DB::select("SELECT * FROM event_interested WHERE event_id='$eid' AND user_id='$uid'");
    if ($exist) {
        return true;
    } else {
        return false;
    }
}

public static function eventInterestCount($id) {
    $data = DB::select("SELECT count(*) as counts FROM event_interested WHERE event_id='$id' ");

    return $data[0]->counts;
}

public static function branchExists($uid) {
    $data = DB::select("SELECT branch_id FROM user_details WHERE user_id='$uid'");

    if ($data) {
        return false;
    } else {
        return true;
    }
}

public static function groupimg($data, $dir) {

}

public static function userDetail($user_id) {
    $category = DB::select("SELECT c.name as category FROM categories c JOIN users u on u.category = c.id  WHERE u.id = '$user_id'");
    $religion = DB::select("SELECT r.name as religion FROM  religion r JOIN users u on u.religion = r.id WHERE u.id = '$user_id'");
    $user_data = array();
    if ($category) {
        $user_data['category'] = $category[0]->category;
    } else {
        $user_data['category'] = ' ';
    }
    if ($religion) {
        $user_data['religion'] = $religion[0]->religion;
    } else {
        $user_data['religion'] = ' ';
    }

    return $user_data;
}

public static function getPushToken($user_id) {
    $data = array();
    $user_data = DB::select("SELECT access_token, mobile,guardian_details FROM users WHERE id = '$user_id'");
    if ($user_data) {
        $token = $user_data[0]->access_token;
        $user_mobile = $user_data[0]->mobile;
        $guardian_details = json_decode($user_data[0]->guardian_details);
        $mobile = array();
        if ($guardian_details) {
            foreach ($guardian_details as $detail) {
                if (isset($detail->mobile)) {
                    $mobile_value = $detail->mobile;
                    $mobile[] = $mobile_value;
                }
            }
        }

        array_push($mobile, $user_mobile);
        $user_detail = array();
        $user_detail['mobile'] = $mobile;
        $user_detail['access_token'] = $token;
        if ($user_detail) {
            return $user_detail;
        }
    } else {

        return;
    }
}

public static function pushNotificationToAdmin($user_id) {
    $user_data = DB::select("SELECT access_token FROM users WHERE id = '$user_id'");

    if ($user_data) {
        $token = $user_data[0]->access_token;
        $user_detail['access_token'] = $token;
        return $user_detail;
    } else {

        return;
    }
}

public static function notifyUsers($title, $content, $access_token, $url, $sender_token) {
    $push = new SSPushNotification();
    $status = $push->pushNotification($title, $content, $access_token, $url, $sender_token);
    return $status;
}

public static function notifyUsersForAtendance($title, $content, $access_token, $url, $sender_token) {
    $push = new SSPushNotification();
    $status = $push->pushNotification($title, $content, $access_token, $url, $sender_token);
    return $status;
}

public static function groupMemberList($group_id) {
    $members = DB::select("SELECT u.id, u.name, u.email, u.pic AS img, ge.status FROM users u JOIN group_enrollments ge ON u.id=ge.user_id WHERE ge.group_id='$group_id' AND ge.group_role_id=1 AND status = 1 OR status = 2");

    if ($members) {
        return $members;
    } else {

        return;
    }
}

public static function getThread($user_id) {
    $data = DB::select("SELECT mts.* FROM msg_threads mts WHERE (sender_id='$user_id') OR (receiver_id='$user_id')");

    if (count($data) > 0) {
        return $data[0]->id;
    }
    return 0;
}

public static function collegeAdminUserId($cid) {
    $user_data = DB::select("SELECT u.id FROM users u JOIN role_user r on u.id = r.user_id WHERE u.college_id = '$cid' AND r.role_id = '7'");
    if ($user_data) {
        return $user_data[0]->id;
    }
    return 0;
}

public static function getShiftId($cid) {
    $course_data = DB::select("SELECT c.shift_id FROM courses c  WHERE c.id = '$cid' ");
    if ($course_data) {
        return $course_data[0]->shift_id;
    }
    return 0;
}

public static function getUserRole($user_id) {
    $roles = DB::select("SELECT role_id from role_user WHERE user_id = '$user_id'");
    if ($roles) {
        return $roles[0]->role_id;
    }
    return 0;
}

public static function collegeType($college_id) {
    $ctype = DB::select("SELECT type from colleges WHERE id = '$college_id'");
    if ($ctype) {
        return $ctype[0]->type;
    }
    return 0;
}

public static function courseMemberList($cid) {
    $members = DB::select("SELECT user_id FROM course_enrolments WHERE course_id='$cid' AND role_id = '5'");

    if ($members) {
        return $members;
    } else {

        return;
    }
}

public static function getCourseDetail($cid, $college_id) {
    $course_data = DB::select("SELECT c.shift_id, c.wing_id FROM courses c  WHERE c.id = '$cid' and c.college_id = '$college_id' ");
    if ($course_data) {
        return $course_data[0];
    }
    return 0;
}

public static function unique_key($array, $keyname) {

    $new_array = array();
    foreach ($array as $key => $value) {

        if (!isset($new_array[$value[$keyname]])) {
            $new_array[$value[$keyname]] = $value;
        }
    }
    $new_array = array_values($new_array);
    return $new_array;
}

public static function collegeModule($college_id, $module_id) {

    $c_module = DB::select("SELECT * from college_modules WHERE college_id = '$college_id' AND module_id = '$module_id' ");

    if ($c_module) {
        return $c_module[0];
    }
    return 0;
}

public static function imageDelete($dir, $urls) {
    $s3 = \Storage::disk('s3');
    $file = $dir . $urls;
    $exists = $s3->has($file);
    if ($exists) {
        $status = $s3->delete($file);
        return $status;
    } else {
        return true;
    }
}

public static function chatImageUpload($image, $dir, $id, $recid, $course_id) {
    list($type, $image) = explode(';', $image);
    list(, $image) = explode(',', $image);
    $image = base64_decode($image);
    $unique = $id . '_' . $recid . '_' . $course_id . '_' . time() . '.png';
    $s3 = \Storage::disk('s3');
    $path = $dir . $unique;
    $s3->put($path, $image, 'public');
    return $unique;
}

public static function getGroupDetail($gid, $college_id) {
    $course_data = DB::select("SELECT g.shift_id, g.wing_id FROM groups g  WHERE g.id = '$gid' and g.college_id = '$college_id' ");
    if ($course_data) {
        return $course_data[0];
    }
    return 0;
}

public static function allGroupMemberList($group_id) {
    $members = DB::select("SELECT u.id, u.name, u.email, u.pic AS img, ge.status FROM users u JOIN group_enrollments ge ON u.id=ge.user_id WHERE ge.group_id='$group_id' AND status = 1 OR status = 2");

    if ($members) {
        return $members;
    } else {

        return;
    }
}

public static function checkTodaysEntry($college_id,$shift_id,$module_id,$todayStart) {

    $today_entry = DB::select("SELECT * FROM daily_notification_report  WHERE notification_date ='$todayStart' AND module_id ='$module_id' AND shift_id = '$shift_id' AND college_id ='$college_id' ");

    if ($today_entry) {
        return true;
    }
    return false;
}

public static function insertTodaysEntry($college_id,$shift_id,$module_id,$module_name,$todayStart,$timestamp) {        
    $notification_query = "INSERT INTO  daily_notification_report (notification_date, module_id, module_name, push_count,sms_count,college_id,shift_id,CreatedAt, UpdatedAt) VALUES ($todayStart, $module_id, '$module_name', '0','0',$college_id,$shift_id,$timestamp, $timestamp)";
    $insert_query = DB::insert($notification_query); 
    if ($insert_query) {
        return true;
    }
    return false;
}
public static function updatePushCount($college_id,$shift_id,$module_id,$todayStart,$timestamp) {
    $upadte_daily_report = "UPDATE `daily_notification_report`  SET `push_count`= push_count + 1 , `UpdatedAt`= $timestamp WHERE notification_date ='$todayStart' AND shift_id ='$shift_id' AND module_id ='$module_id' AND college_id ='$college_id' ";  
    $update_query = DB::update($upadte_daily_report);
    if ($update_query) {
        return true;
    }
    return false;
}
public static function updateSmsCount($college_id,$shift_id,$module_id,$todayStart,$timestamp) {
    $upadte_daily_report = "UPDATE `daily_notification_report`  SET `sms_count`= sms_count + 1 , `UpdatedAt`= $timestamp WHERE notification_date ='$todayStart' AND shift_id ='$shift_id' AND module_id ='$module_id' AND college_id ='$college_id' ";  
    $update_query = DB::update($upadte_daily_report);
    if ($update_query) {
        return true;
    }
    return false;
}

public static function date_range($first, $last) {

    for ($i=$first; $i<=$last; $i+=86400) {  
      $dates[] = $i;     
  }

  return $dates;

}
public static function getTeacherAttendance($shift_id) {
    $college_id = SSAuth::user()->college_id;
    $ctime = time();
    $class_id = DB::select("SELECT id, name FROM  `courses`  WHERE college_id = '$college_id' AND shift_id = '$shift_id' AND type = '0'");

    $timestamp = time();
    $current_date = strtotime("midnight", $timestamp);

    if($class_id){
        $cid = $class_id[0]->id;  
        $teacher_attendance = DB::select("SELECT * FROM `course_attendance` WHERE course_id = '$cid' AND attendance_date = '$current_date' "); 
        
        return $teacher_attendance;    
    }
    else {
        return false;
    }    

}

public static function getStaffClassName($shift_id) {
        $college_id = SSAuth::user()->college_id;
        $class_id = DB::select("SELECT id,name FROM  `courses`  WHERE college_id = '$college_id' AND shift_id = '$shift_id' AND type = '0'");
        if($class_id)
        {
            return $class_id[0]->id;
        }
        else{
            return 0;
        }
    }


    // Live tutoring
    
    public static function connectionStatus($class_id) {
        $query = DB::select("SELECT lc.user_id, lc.joinedAt FROM live_classrooms lc WHERE lc.classroom_id = '$class_id' AND lc.status = 1");
    	return $query;    
    }

    public static function getInstruction($class_id) {
        $query = DB::select("SELECT inst_time ,instruction, priority FROM live_playlist lp WHERE classroom_id = '$class_id'");

        if (count($query)) {
            $data['status'] = 1;
            $data['inst_time'] = $query[0]->inst_time; 
            $data['instruction'] = $query[0]->instruction; 
            return $data;
        } else {
            $data['status'] = 0;
        } 
    }

    public static function teacherLiveStatus($user_id, $class_id) {
        // $TeacherPresenceCheck = DB::select("SELECT * FROM live_classrooms lc JOIN class_enrolments ce on ce.user_id = lc.user_id WHERE lc.status = 1 AND lc.user_id ='$user_id' AND classroom_id = '$class_id' AND ce.role_id = '4'");

        $TeacherPresenceCheck = DB::select("SELECT * FROM live_classrooms lc WHERE lc.status = 1 AND lc.user_id ='$user_id' AND lc.classroom_id = '$class_id'");

        if (count($TeacherPresenceCheck)) {
            return true;
        } else {
            return false;
        }
    }

    public static function userPresenceCheck($user_id, $class_id) {
    $userPresenceCheck = DB::select("SELECT * FROM live_classrooms lc WHERE `status`= 1 AND `blocked`= 1 AND user_id ='$user_id' AND classroom_id = '$class_id'");

        if (count($userPresenceCheck)) {
            return true;
        } else {
            return false;
        }
    }

    
    public static function updateConnectionStatus($user_id, $class_id, $status) {
        $entry = DB::select("SELECT * FROM live_classrooms WHERE user_id = '$user_id'");
	$joinedAt = time();
        if (count($entry)) {
            $upadte_daily_report = "UPDATE `live_classrooms` SET `status`= '$status', `joinedAt`= '$joinedAt' WHERE user_id ='$user_id' AND classroom_id = '$class_id'";  
            $update_query = DB::update($upadte_daily_report);
        } else {
            $newEntry = DB::insert("INSERT INTO `live_classrooms` (user_id, classroom_id, status, joinedAt)VALUES ('$user_id', '$class_id', '$status','$joinedAt')");
        }
        return true;
    }

    public static function updateBlock($user_id, $class_id, $block) {
        $upadte_daily_report = "UPDATE `live_classrooms` SET `blocked`= '$block' WHERE user_id ='$user_id' AND classroom_id = '$class_id'";  
        $update_query = DB::update($upadte_daily_report);
        return true;
    }

    public static function classEnrollCheck($user_id, $role_id, $classroom_id) {
        $conditions = array();
        
        $sql = "SELECT * FROM class_enrolments ce WHERE user_id = '$user_id' AND role_id = '$role_id'";

        if ($classroom_id != null) {
            $conditions[] = " AND ce.class_id = '$classroom_id' ";
            
        }

        if (count($conditions) > 0) {
            $sql .= implode(' AND ', $conditions);
        }

        $query = DB::select($sql);

        if (count($query)) {
            $data['status'] = 1;
            $data['class_id'] = $query[0]->class_id; 
            return $data;
        } 
    }

     public static function classPackageId($classroom_id) {
        $sql = "SELECT cc.class_package_id FROM classroom_content cc WHERE cc.classroom_id = '$classroom_id'";
        $query = DB::select($sql);
        return $query;
    }

    public static function userEnrolledClass($user_id, $role_id) {
        $sql = "SELECT ce.class_id, cs.access_id as cr_id, cs.name FROM class_enrolments ce JOIN classrooms cs on cs.id = ce.class_id WHERE ce.user_id = '$user_id' AND ce.role_id = '$role_id'";
        $query = DB::select($sql);
        return $query;
    }

    public static function userClassEnrolled($class_id, $role_id) {
        $sql = "SELECT ce.id FROM class_enrolments ce WHERE ce.class_id = '$class_id' AND ce.role_id = '$role_id'";
        $query = DB::select($sql);
        return $query;
    }

    public static function role($user_id) {
        $query = DB::select("SELECT ru.role_id, r.role as role_name FROM role_user ru JOIN roles r on r.id = ru.role_id WHERE ru.user_id = '$user_id'");

        $data = array();
        if (count($query)) {
            $data['role_id'] = $query[0]->role_id;
            $data['role_name'] = $query[0]->role_name;
            return $data;
        }
    }

    public static function userJoinedStatus($user_id, $cId) {
        
        $userJoinedStatus = DB::select("SELECT * FROM live_classrooms WHERE user_id = '$user_id' AND classroom_id = '$cId'");

        if (count($userJoinedStatus)) {
            return 1;
         } else {
            return 0;
         }
    }

    public static function joinliveClassroom($user_id, $cId) {
        
        $joinedAt = time();
        $joinClassroom = DB::insert("INSERT INTO live_classrooms (user_id, joinedAt, status, classroom_id) VALUES ('$user_id', '$joinedAt', 1, '$cId')");

        if ($joinClassroom) {
            return true;
        }
    }

    public static function leaveClassroom($user_id, $cId){
        $query = "DELETE FROM `live_classrooms` WHERE user_id = '$user_id' AND classroom_id = '$cId'";
        $deleteEntry = DB::delete($query);
        return $deleteEntry;
    }

    public static function deleteClassroomContent($cId){
        $query = "DELETE FROM `live_playlist` WHERE classroom_id = '$cId'";
        $deleteEntry = DB::delete($query);
        return $deleteEntry;
    }


    public static function allstudentLeave($cId){
        $query = "DELETE FROM `live_classrooms` WHERE classroom_id = '$cId'";
        $deleteEntry = DB::delete($query);
        return $deleteEntry;
    }
    
    public static function updateInstruction($userEnrolledClass, $inst){
    $entry = DB::select("SELECT * FROM live_playlist WHERE classroom_id = '$userEnrolledClass'");
	$time = time();
    if (count($entry)) {
       $upadte_daily_report = "UPDATE `live_playlist` SET inst_time = '$time', `instruction` = '$inst' WHERE classroom_id ='$userEnrolledClass'"; 
       //$upadte_daily_report = "UPDATE `live_playlist` SET `instruction` = '$inst' WHERE classroom_id ='$userEnrolledClass'";  
       
        $update_query = DB::update($upadte_daily_report);
        } else {
            $newEntry = DB::insert("INSERT INTO `live_playlist` (classroom_id, instruction, inst_time)VALUES ('$userEnrolledClass', '$inst', '$time')");
        }
        return true;
    }    
}

