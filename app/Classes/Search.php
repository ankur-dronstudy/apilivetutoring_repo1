<?php

namespace App\Classes;

use App\library\Functions;
use App\models\News;
use App\models\Quiz;
use App\models\ForumPost;
use App\models\ForumThread;
use App\models\Books;

class Search {

    private $creds;

    public function __construct() {
        $this->creds = \Config::get('database.connections.elastic');
    }

    public function __destruct() {
        ;
    }

    public function getItems($query) {
        $items = [];
        return $items;
    }

    public function getNews($query, $tag, $from, $size) {

        $ssElastic = new SSElasticSearch();
        $items = [];
        $items['news'] = $ssElastic->getNews($query, $tag, false, false, $from, $size);
        return $items['news'];
    }

    public function getQuiz($query, $tag, $from, $size) {

        $ssElastic = new SSElasticSearch();
        $items = [];
        $items['quiz'] = $ssElastic->getQuiz($query, $tag, false, false, $from, $size);
        return $items['quiz'];
    }

    public function getLearn($query, $from, $size) {

        $ssElastic = new SSElasticSearch();
        $items = [];
        $items['studymaterial'] = $ssElastic->getMaterial($query, false, false, $from, $size);
        return $items['studymaterial'];
    }

    public function getDiscuss($query) {
        $items = [];
        return $items;
    }

    public function quizId($id) {
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
        $uri = $this->creds['host']
                . $this->creds['port']
                . $this->creds['quizData']
                . '_search';
        
        $res = Functions::curlPostJson($uri, $arr);
        if (count($res->hits->hits) > 0) {
            $quizid = $res->hits->hits[0]->_id;
            return $quizid;
        } else {
            return false;
        }
    }

    public function getCity($query) {

        $ssElastic = new SSElasticSearch();
        $items = [];
        $items['cities'] = $ssElastic->getCity($query);
        return $items['cities'];
    }

    public function getCollege($query) {

        $ssElastic = new SSElasticSearch();
        $items = [];
        $items['college'] = $ssElastic->getCollege($query);
        return $items['college'];
    }

    public function getCityName($query) {

        $ssElastic = new SSElasticSearch();
        $items = [];
        $items['cities'] = $ssElastic->getCityName($query);
        return $items['cities'];
    }

    public function getCollegeName($query) {
        $ssElastic = new SSElasticSearch();
        $items = [];
        $items['college'] = $ssElastic->getCollegeName($query);
        return $items['college'];
    }

    public function getForumPost($query) {

        $items = [];
        $items['posts'] = ForumPost::where('active', 1)
                ->where('title', 'LIKE', '%' . $query . '%')
                ->orWhere('content', 'LIKE', '%' . $query . '%')
                ->orderBy('created', 'DESC')
                ->get(['title', 'content', 'created']);
        return $items['posts'];
    }

    public function getForumThreadByExam($exam_id) {

        $items = [];
        $items['threads'] = ForumThread::join('users', 'users.id', '=', 'forum_threads.user_id')
                ->where('active', 1)
                ->where('exam_id',$exam_id)
                ->orderBy('created', 'DESC')
                ->take(20)
                ->get(['forum_threads.id as id', 'first_name', 'last_name', 'title',
            'description', 'vote_up', 'vote_down', 'views', 'updated', 'posts', 'comments','users.pic as pic']);
        return $items['threads'];
    }

    public function getBook($query) {

    }

}
