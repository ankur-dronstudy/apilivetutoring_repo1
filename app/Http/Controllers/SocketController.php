<?php

namespace App\Http\Controllers;

//use App\Http\Requests;
use App\Http\Controllers\Controller;
//use Request;
use Redis;
use App\Classes\SSMail;
use App\models\EmailTemplate;
use Illuminate\Http\Request;
use App\Classes\SSAuth;
use App\library\Functions;

class SocketController extends Controller {

//    public function __construct() {
//        $this->middleware('guest');
//    }

    public function index() {
        return view('socket');
    }

    public function writemessage() {
        return view('writemessage');
    }
    
    public function makePrivateRoom(Request $request){
        $response = ['status' => false];
        $reciever_id = $request->get('username');
        $user_id = SSAuth::user()->id;
        $min = $user_id<$reciever_id?$user_id:$reciever_id;
        $max = $user_id>$reciever_id?$user_id:$reciever_id;
        $redis = Redis::connection();
        $channel = $min.'::ROOM::'.$max;
        //echo $channel;
        $redis->publish('message2:',$channel);
        //$redis->publish('message1', $keystring);
        //$redis->publish('message', $message);
        $response['status'] = true;
        return response($response)->header('Content-Type', 'application/json');
    }

    public function sendMessage(Request $request) {
        $response = ['status' => false];
        //dd("dddd");
        $message = $request->get('message');//$request->get('message');
        $reciever_id = $request->get('reciever_id');
        echo $reciever_id;
        $redis = Redis::connection();
        $user_id = SSAuth::user()->id;
        
//        if($user_id<$reciever_id){
//            $min = $user_id;
//            $max = $reciever_id;
//        }else{
//            $max = $user_id;
//            $min = $reciever_id;
//        }
//        
//        
//        
//        $channel = $min.'::ROOM::'.$max;
        
        
        
//        $keydata = [];
//        $keydata[] = $channel;
//        $keydata[] = $message;
//        $keystring = implode('MESSAGE::',$keydata);
        
        //Functions::curlPostJson();
        //$redis->set('channel', $channel);
        //$redis->set('name1', 'Taylor');
//        $user1 = 2;
//        $user2 = 70;
        //$channel = $user1 + '::ROOM::' + $user2;
        //echo $message;
//        echo $channel;
//        $redis->publish('message2:'.$channel, $message);
        //$redis->publish('message1', $keystring);
        //$redis->publish('message', $message);
        $response['status'] = true;
        return response($response)->header('Content-Type', 'application/json');
    }
    
    
    
    
    
    public function sendMail(Request $request) {
        $response = ['status' => false];
        $email = 'slskmr007@gmail.com';
        $name = 'shailesh kumar';
        $role = 4;

        $code = 12345;//Otprequest::save_eotp($email);
        $baselink = \Config::get('scholar.link.act');
        $postpara = 'e=' . $email . '&t=' . $code.'&r='.$role;
        $jdata = '/' . base64_encode($postpara);
        $link = $baselink . $jdata;
        $to = [];
        $mail = new SSMail();
        $type = 'eotp_generate';
        $to = [$email];
        $d = [];
        $d['link'] = $link;
        $d['name'] = $name;
        $data = EmailTemplate::get_template($d, $type);
        $cc = [];
        $bcc = [];
        
        $mail->send($to, $cc, $bcc, $data['subject'], $data['html']);
//        return true;
//        die;
        $redis = Redis::connection();
        dd($redis);
//        $redis->publish('message', $request->message);
        $response['status'] = true;
        return response($response)->header('Content-Type', 'application/json');
    }

}
