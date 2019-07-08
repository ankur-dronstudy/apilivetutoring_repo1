<?php

namespace App\Http\Controllers;

use App\models\Subscriber;
use Illuminate\Http\Request;
use DB;
use App\models\PaperSubject;
use App\models\ContactUs;
use Validator;

class ListController extends Controller {

    public function __construct() {
        
    }

    public function __destruct() {
        
    }

    public function addSubscriber(Request $request) {
        $response = [ 'status' => false];
        $data = $request->all();
        $subscriber = Subscriber::where('email', $data['email'])->first();
        if (!$subscriber) {
            Subscriber::create($data);
        }
        $response['status'] = true;
        return response($response)->header('Content-Type', 'application/json');
    }

    public function getSubscribers() {
        $response = [ 'status' => false];
        $response['data'] = Subscriber::where('active', 1)->get();
        if ($response['data']) {
            $response['status'] = true;
        }
        return response($response)->header('Content-Type', 'application/json');
    }

    public function removeSubscriber(Request $request) {
        $response = [ 'status' => false];
        $email = $request->get('email');
        $status = Subscriber::where('email', $email)->update(['active' => 0]);
        if ($status) {
            $response['status'] = true;
        }
        return response($response)->header('Content-Type', 'application/json');
    }

    //its tempory for fill database
    public function addEntry(Request $request) {

        $response = [ 'status' => false];

        for ($i = 1; $i < 80; $i++) {
            PaperSubject::create(['paper_id' => 2, 'subject_id' => $i]);
        }
        return response($response)->header('Content-Type', 'application/json');
    }

    public function contactUs(Request $request) {
        $response = [ 'status' => false];
        $data = $request->all();
        $validation = Validator::make($data, ContactUs::$rules);

        if ($validation->fails()) {
            $response['message'] = [];
            foreach ($validation->messages()->getMessages() as $mes) {
                $response['message'][] = $mes[0];
            }
        } else {
            ContactUs::create($data);
            $response['status'] = true;
        }

        return response($response)->header('Content-Type', 'application/json');
    }

}
