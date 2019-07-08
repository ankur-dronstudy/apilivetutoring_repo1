<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Input;
use Illuminate\Http\Request;
use App\models\News;
use App\Classes\SSAuth;
use Validator;
use App\library\Functions;

class NewsController extends Controller {

    public function __construct() {
        
    }

    public function __destruct() {
        
    }

    public function update(Request $request) {
        $response = [ 'status' => false];

        $user = SSAuth::user();
        $data = $request->all();
        if (!isset($data['active']))
            $data['active'] = 0;
        $id = $data['id'];
        unset($data['id']);
        $validation = Validator::make($data, News::$rules);
        if ($validation->fails()) {
            $response['error'] = $validation->messages();
            return response($response)->header('Content-Type', 'application/json');
        } else {
            $data['user_id'] = $user->id;
            $status = News::where('id', $id)->update($data);
            $newsId = $this->newsId($id);
            $ec = \Config::get('database.connections.elastic');
            if ($newsId) {
                $url = $ec['protocol'] . $ec['host'] . $ec['port'] . $ec['newsData'] . $newsId;
            } else {
                $url = $ec['protocol'] . $ec['host'] . $ec['port'] . $ec['newsData'];
            }

            if ($data['active'] == 0) {
                Functions::curlDelete($url);
            } else {
                unset($data['active']);
                $data['id'] = $id;
                $data['tags'] = explode(",", $data['tags']);
                $data['created'] = date('Y-m-d');
                Functions::curlPostJson($url, $data);
            }
            if ($status) {
                $response['status'] = true;
            }
            return response($response)->header('Content-Type', 'application/json');
        }
    }

    public function get(Request $request) {
        $response = [ 'status' => false];
        $news = News::get(['id', 'title', 'active']);
        if ($news) {
            $response['data'] = $news;
            $response['status'] = true;
        }
        return response($response)->header('Content-Type', 'application/json');
    }

    public function create(Request $request) {
        $response = [ 'status' => false];

        $user = SSAuth::user();
        $data = $request->all();
        $validation = Validator::make($data, News::$rules);
        if ($validation->fails()) {
            $response['error'] = $validation->messages();
            return response($response)->header('Content-Type', 'application/json');
        } else {
            $data['user_id'] = $user->id;
            $news = News::create($data);
            if ($news) {
                if (isset($data['active'])) {
                    $ec = \Config::get('database.connections.elastic');
                    $url = $ec['protocol'] . $ec['host'] . $ec['port'] . $ec['newsData'];

                    $data['id'] = $news->id;
                    $data['tags'] = explode(",", $data['tags']);
                    $data['created'] = date('Y-m-d');
                    Functions::curlPostJson($url, $data);
                }
            }
            if ($news) {
                $response['status'] = true;
            }
            return response($response)->header('Content-Type', 'application/json');
        }
    }

    public function detail(Request $request) {
        $response = [ 'status' => false];

        $user = SSAuth::user();
        $id = $request->get('id');
        $notification = News::where('id', $id)->where('user_id', $user->id)->first(['id', 'title', 'url', 'description', 'tags', 'active']);
        if ($notification) {
            $response['data'] = $notification;
            $response['status'] = true;
        }
        return response($response)->header('Content-Type', 'application/json');
    }
    
    private function newsId($id) {
        $arr = [
            "from" => 0,
            "size" => 1,
            "query" => [
                "bool" => [
                    "must" => [
                        "term" => [ "id" => "$id"]
                    ]
                ]
            ]
        ];
        $ec = \Config::get('database.connections.elastic');
        $uri = $ec['host']
                . $ec['port']
                . $ec['newsData']
                . '_search';

        $res = Functions::curlPostJson($uri, $arr);

        if (count($res->hits->hits) > 0) {
            $newsid = $res->hits->hits[0]->_id;
            return $newsid;
        } else {
            return false;
        }
    }

}
