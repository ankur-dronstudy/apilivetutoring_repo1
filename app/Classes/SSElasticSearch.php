<?php

namespace App\Classes;

use App\library\Functions;
use App\models\QuizQuestion;
use App\models\Quiz;

class SSElasticSearch {

    private $creds;

    public function __construct() {
        $this->creds = \Config::get('database.connections.elastic');
    }

    private function getResponseFields($fieldsArr) {
        $fields = '';
        $res = array_walk($fieldsArr, function( &$item ) {
            $item = "\"$item\"";
        });
        if ($res) {
            $fields = implode(',', $fieldsArr);
        }
        return $fields;
    }

    public function getQuizQuestions($quizId, $randomQ, $randomA, $quiz_id = null, $result = null) {
        $response = [];
        $questions = QuizQuestion::getQuestions($quizId);
        $uri = $this->creds['host']
                . $this->creds['port']
                . $this->creds['questionsData'];
        foreach ($questions as $questionId) {
            $data = Functions::curlGet($uri . $questionId, []);
            if (isset($data->_source)) {
                $response[] = $this->createQuestionData($data, $randomQ, $randomA, $quiz_id);
            }
        }
        $countSubjects = count($result['subjects']);

        if ($randomQ) {
            shuffle($response);
        } 
        //else {
//            $quizQuestions0 = [];
//            $quizQuestions1 = [];
//            $quizQuestions2 = [];
//            foreach ($response as $question) {
//                if ($question['subject']) {
//                    if (strtolower($question['subject']) == strtolower($result['subjects'][0]['subject'])) {
//                        $quizQuestions0[] = $question;
//                    }
//                    if (strtolower($question['subject']) == strtolower($result['subjects'][1]['subject'])) {
//                        $quizQuestions1[] = $question;
//                    }
//                    if (strtolower($question['subject']) == strtolower($result['subjects'][2]['subject'])) {
//                        $quizQuestions2[] = $question;
//                    }
//                }else{
//                    $quizQuestions0[] = $question;
//                }
//            }
//
//            $response = [];
//            $response = array_merge($quizQuestions0, $quizQuestions1, $quizQuestions2);
//        }

        //Add an incremental number to show in UI
        $this->addQuestionCounter($response);
        //dd($response);
        return $response;
    }

    private function addQuestionCounter(&$questions) {
        $len = count($questions);
        $counter = 0;
        for ($i = 0; $i < $len; ++$i) {
            if (isset($questions[$i]['subquestion'])) {
                $questions[$i]['count'] = $i + 1;
                $len1 = count($questions[$i]['subquestion']);
                for ($j = 0; $j < $len1; ++$j) {
                    $questions[$i]['subquestion'][$j]->count = ++$counter;
                }
            } else {
                $questions[$i]['count'] = ++$counter;
            }
        }
    }

    public function getUserQuestionsAdmin($username, $from = 0, $size = 10) {
        $arr = [
            "from" => $from,
            "size" => $size,
            "query" => [
                "bool" => [
                    "must" => [
                        "term" => [ "user_id" => "$username"]
                    ]
                ]
            ]
        ];
        $uri = $this->creds['host']
                . $this->creds['port']
                . $this->creds['questionsData']
                . '_search';

        $questions = [];
        $res = Functions::curlPostJson($uri, $arr);

        foreach ($res->hits->hits as $q) {
            $questions[] = $q->_source;
        }
        return $questions;
    }

    public function getUserQuestions($username, $randomQ, $randomA, $from = 0, $size = 10) {
        $questions = [];
        $arr = [
            "from" => $from,
            "size" => $size,
            "query" => [
                "bool" => [
                    "must" => [
                        "term" => [ "user_id" => "$username"]
                    ]
                ]
            ]
        ];
        $uri = $this->creds['host']
                . $this->creds['port']
                . $this->creds['questionsData']
                . '_search';
        $res = Functions::curlPostJson($uri, $arr);

        foreach ($res->hits->hits as $q) {
            $d = $this->createQuestionData($q, $randomQ, $randomA);
            $questions[] = $d;
        }

        if ($randomQ) {
            shuffle($questions);
        }

        return $questions;
    }

    public function getUserFilteredQuestions($userId, $randomQ, $randomA, $from, $size, $exam, $subject, $category, $chapter, $topic, $tags) {

        $string = '';
        $quiz = [];
        $query = [];

        $examArray = [ "term" =>
            [
                "exam" => "$exam"
            ]
        ];

        $subjectArray = [ "term" =>
            [
                "subject" => strtolower($subject)
            ]
        ];

        $categoryArray = [ "term" =>
            [
                "category" => "$category"
            ]
        ];

        $chapterArray = [ "term" =>
            [
                "chapter" => "$chapter"
            ]
        ];

        if ($exam != null) {
            $query[] = $examArray;
        }
        if ($subject != null) {
            $query[] = $subjectArray;
        }
        if ($category != null) {
            $query[] = $categoryArray;
        }
        if ($chapter != null) {
            $query[] = $chapterArray;
        }

        $query1 = [
            "must" => $query
        ];
        //dd($query1);
        $questions = [];
        $arr = [
            "from" => $from,
            "size" => $size,
            "query" => [
                "filtered" => [
                    "filter" => [
                        "bool" => $query1
                    ]
                ]
            ]
        ];


        //$size = \Config::get('scholar.quiz.question.admin_search');
        $uri = $this->creds['host']
                . $this->creds['port']
                . $this->creds['questionsData']
                . '_search';
        //dd($arr);
        $res = Functions::curlPostJson($uri, $arr);
        foreach ($res->hits->hits as $q) {
            $d = $this->createQuestionData($q, $randomQ, $randomA);
            $questions[] = $d;
        }
        if ($randomQ) {
            shuffle($questions);
        }
        return $questions;
    }

    public function getUserSearchQuestions($username, $randomQ, $randomA, $from = 0, $size = 10, $string) {
        $questions = [];

        $arr = [
            "from" => $from,
            "size" => $size,
            "query" => [
                "query" => [
                    "filtered" => [
                        "query" => [
                            "bool" => [
                                "must" => [
                                    "multi_match" => [
                                        [
                                            "query" => "$string",
                                            "fields" => ["question", "passage"]
                                        ]
                                    ]
                                ]
                            ]
                        ],
                        "filter" => [
                            "term" => ["username" => "$username"]
                        ]
                    ]
                ]
            ]
        ];
        $uri = $this->creds['host']
                . $this->creds['port']
                . $this->creds['questionsData']
                . '_search';

        $res = Functions::curlPostJson($uri, $arr);

        foreach ($res->hits->hits as $q) {
            $d = $this->createQuestionData($q, $randomQ, $randomA);
            $questions[] = $d;
        }

        if ($randomQ) {
            shuffle($questions);
        }

        return $questions;
    }

    private function createQuestionData($q, $randomQ, $randomA, $quiz_id = null) {
        $d = [];
        $d["id"] = $q->_id;
        $d["user_id"] = $q->_source->user_id;

        $d["exam"] = $q->_source->exam;
        $d["subject"] = $q->_source->subject;
        if (isset($q->_source->chapter)) {
            $d["chapter"] = $q->_source->chapter;
        }
        $d["topic"] = $q->_source->topic;
        $d["category"] = $q->_source->category;

        if (isset($q->_source->subquestion)) {
            $d["passage"] = $q->_source->passage;
            $d["image"] = isset($q->_source->image) ? $q->_source->image : '';
            if ($randomQ) {
                $subques = (array) $q->_source->subquestion;
                //dd($q->_source->subquestion);
                shuffle($subques);
            }
            $subQuestions = [];
            if ($randomA) {
                foreach ($q->_source->subquestion as $subQ) {
                    shuffle($subQ->options);
                    $subQuestions[] = $subQ;
                }
            } else {
                $subQuestions = $q->_source->subquestion;
            }
            $updateSubQuestions = [];

            foreach ($subQuestions as $subQ) {

                $optionsArr = [];
                foreach ($subQ->options as $option) {
                    $type = isset($subQ->type) ? $subQ->type : '';
                    $optionsArr[] = ['text' => $option,
                        'selected' => false
                        , 'attemptedAnswer' => $this->setAttemptedOption($type, $option, $subQ->answer)
                    ];
                }
                $subQ->options = $optionsArr;

                $updateSubQuestions[] = $subQ;
            }
            unset($subQuestions);

            $d["subquestion"] = $updateSubQuestions;
        } else {
            $d["type"] = $q->_source->type;
            $d["duration"] = $q->_source->duration;
            $d["question"] = $q->_source->question;
            $d["image"] = $q->_source->image;
            $d["answer"] = $q->_source->answer;
            $d["solution"] = $q->_source->solution;
            //echo $d["question"];
            if (($randomA) && (count($q->_source->options) > 1)) {
                shuffle($q->_source->options);
            }

            $optionsArr = [];
            if (count($q->_source->options) > 1) {
                foreach ($q->_source->options as $option) {
                    $type = isset($q->_source->type) ? $q->_source->type : '';
                    $optionsArr[] = ['text' => $option,
                        'selected' => false,
                        'attemptedAnswer' =>
                        $this->setAttemptedOption($type, $option, $q->_source->answer)];
                }
            }
            $d["options"] = $optionsArr;
            $marks = Quiz::getPerQuesMarks($quiz_id);
            $d["marks"] = $marks;
            $d["difficulty"] = $q->_source->difficulty;
        }
        return $d;
    }

    private function setAttemptedOption($type, $option, $answer) {
        $status = false;
        $parts = explode('::', $answer);
        if (($type === 'Single') || ($type === '')) {
            if ($option === $parts[0]) {
                $status = true;
            }
        }
        return $status;
    }

    public function getQuiz($query, $tag, $randomQ, $randomA, $from = 0, $size = 10) {
        $quiz = [];
        if (($query) || ($query && $tag)) {
            $q = [
                "query" => "$query",
                "fields" => ["title", "description"]
            ];
        } else if ($tag) {
            $q = [
                "query" => "$tag",
                "fields" => [ "tags"]
            ];
        }
        $arr = [
            "from" => $from,
            "size" => $size,
            "query" => [
                "filtered" => [
                    "query" => [
                        "bool" => [
                            "must" => [
                                "multi_match" => [
                                    $q
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $uri = $this->creds['host']
                . $this->creds['port']
                . $this->creds['quizData']
                . '_search';


        $res = Functions::curlPostJson($uri, $arr);

        foreach ($res->hits->hits as $q) {
            $d = $this->createQuizData($q, $randomQ, $randomA);
            $quiz[] = $d;
        }

        return $quiz;
    }

    private function createQuizData($q, $randomQ, $randomA) {
        $d = [];
        $d["id"] = $q->_id;
        $d["username"] = $q->_source->username;

        $d["exam"] = $q->_source->exam;
        $d["subject"] = $q->_source->subject;
        $d["topic"] = $q->_source->topic;
        $d["subtopic"] = $q->_source->subtopic;
        $d["title"] = $q->_source->title;
        $d["description"] = $q->_source->description;
        $d["created"] = $q->_source->created;
        return $d;
    }

    public function getNews($query, $tag, $randomQ, $randomA, $from = 0, $size = 10) {
        $news = [];
        if (($query) || ($query && $tag)) {
            $q = [
                "query" => "$query",
                "fields" => ["title", "description", "tags"]
            ];
        } else if ($tag) {
            $q = [
                "query" => "$tag",
                "fields" => [ "tags"]
            ];
        }
        $arr = [
            "from" => $from,
            "size" => $size,
            "query" => [
                "filtered" => [
                    "query" => [
                        "bool" => [
                            "must" => [
                                "multi_match" => [
                                    $q
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $uri = $this->creds['host']
                . $this->creds['port']
                . $this->creds['newsData']
                . '_search';

        $res = Functions::curlPostJson($uri, $arr);
        foreach ($res->hits->hits as $q) {
            $d = $this->createNewsData($q, $randomQ, $randomA);
            $news[] = $d;
        }

        return $news;
    }

    private function createNewsData($q, $randomQ, $randomA) {
        $d = [];
        $d["id"] = $q->_id;
        $d["title"] = $q->_source->title;
        $d["description"] = $q->_source->description;
        $d["url"] = $q->_source->url;
        $d["created"] = $q->_source->created;
        return $d;
    }

    public function getMaterial($query, $randomQ, $randomA, $from = 0, $size = 10) {
        $material = [];
        $arr = [
            "from" => 0,
            "size" => 20,
            "query" => [
                "filtered" => [
                    "query" => [
                        "bool" => [
                            "must" => [
                                "multi_match" => [
                                    [
                                        "query" => "$query",
                                        "fields" => ["title", "content"]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $uri = $this->creds['host']
                . $this->creds['port']
                . $this->creds['learnData']
                . '_search';

        $res = Functions::curlPostJson($uri, $arr);

        foreach ($res->hits->hits as $q) {
            $d = $this->createMaterialData($q, $randomQ, $randomA);
            $material[] = $d;
        }
        return $material;
    }

    private function createMaterialData($q, $randomQ, $randomA) {
        $d = [];
        $d["id"] = $q->_id;
        $d["title"] = $q->_source->title;
        $d["description"] = $q->_source->content;
        $d["created"] = $q->_source->updated;
        return $d;
    }

    public function getCity($query) {
        $cities = [];
        $arr = [
            "from" => 0,
            "size" => 20,
            "query" => [
                "filtered" => [
                    "query" => [
                        "bool" => [
                            "must" => [
                                "multi_match" => [
                                    [
                                        "query" => "$query",
                                        "type" => "phrase_prefix",
                                        "max_expansions" => 5,
                                        "fields" => ["name", "pincode", "state"]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $uri = $this->creds['host']
                . $this->creds['port']
                . $this->creds['cityData']
                . '_search';

        $res = Functions::curlPostJson($uri, $arr);
        foreach ($res->hits->hits as $q) {
            $d = $this->createCityData($q);
            $cities[] = $d;
        }
        return $cities;
    }

    private function createCityData($q) {

        $d = [];
        $d["name"] = $q->_source->name;
        $d["pincode"] = $q->_source->pincode;
        $d["state"] = $q->_source->state;
        return $d;
    }

    public function getCollege($query) {
        $colleges = [];
        $arr = [
            "from" => 0,
            "size" => 20,
            "query" => [
                "filtered" => [
                    "query" => [
                        "bool" => [
                            "must" => [
                                "multi_match" => [
                                    [
                                        "query" => "$query",
                                        "fields" => ["name", "address", "pincode"]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $uri = $this->creds['host']
                . $this->creds['port']
                . $this->creds['collegeData']
                . '_search';

        $res = Functions::curlPostJson($uri, $arr);
        foreach ($res->hits->hits as $q) {
            $d = $this->createCollegeData($q);
            $colleges[] = $d;
        }
        return $colleges;
    }

    private function createCollegeData($q) {

        $d = [];
        $d["name"] = $q->_source->name;
        $d["address"] = $q->_source->address;
        $d["city"] = $q->_source->city;
        $d["pincode"] = $q->_source->pincode;
        return $d;
    }

    public function getCityName($query) {
        $cities = [];
        $arr = [
            "from" => 0,
            "size" => 20,
            "query" => [
                "filtered" => [
                    "query" => [
                        "bool" => [
                            "must" => [
                                "multi_match" => [
                                    [
                                        "query" => "$query",
                                        "fields" => ["city"]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $uri = $this->creds['host']
                . $this->creds['port']
                . $this->creds['collegeData']
                . '_search';

        $res = Functions::curlPostJson($uri, $arr);
        foreach ($res->hits->hits as $q) {
            $cities[] = trim($q->_source->city);
        }

        $cities = array_unique($cities);
        return array_values($cities);
    }

    public function getCollegeName($query) {
        $colleges = [];
        $arr = [
            "from" => 0,
            "size" => 20,
            "query" => [
                "filtered" => [
                    "query" => [
                        "bool" => [
                            "must" => [
                                "multi_match" => [
                                    [
                                        "query" => "$query",
                                        "fields" => ["city"]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $uri = $this->creds['host']
                . $this->creds['port']
                . $this->creds['collegeData']
                . '_search';

        $res = Functions::curlPostJson($uri, $arr);
        foreach ($res->hits->hits as $q) {
            $college['id'] = $q->_source->id;
            $college['name'] = $q->_source->name;
            $colleges[] = $college;
        }
        return $colleges;
    }

    public function getStudyMaterial($randomQ, $randomA, $from = 0, $size = 10, $subject, $topic, $subtopic) {

        $conditions = array();

        if ($subject != '')
            $conditions[] = "$subject";
        if ($topic != '')
            $conditions[] = "$topic";
        if ($subtopic != '')
            $conditions[] = "$subtopic";

        //dd($conditions);
        if (count($conditions) > 0) {
            $string = implode(' AND ', $conditions);
        }

        $questions = [];
        $arr = [
            "from" => $from,
            "size" => $size,
            "query" => [
                "query" => [
                    "filtered" => [
                        "query" => [
                            "query_string" => [
                                "query" => "$string",
                                "fields" => [ "subject_name", "topic", "subtopic"]
                            ]
                        ]
                    ]
                ]
            ]
        ];
        $uri = $this->creds['host']
                . $this->creds['port']
                . $this->creds['learnData']
                . '_search';

        $res = Functions::curlPostJson($uri, $arr);

        $content = [];
        $data = [];

        foreach ($res->hits->hits as $material) {
            $content['id'] = $material->_id;
            $content['title'] = $material->_source->title;
            // $content['content'] = $material->_source->content;
            $data[] = $content;
        }
        return $data;
    }

    public function getTopic($chapter_id, $randomQ, $randomA, $from = 0, $size = 10) {

        $topics = [];
        $arr = [
            "from" => $from,
            "size" => $size,
            "query" => [
                "bool" => [
                    "must" => [
                        "term" => [ "chapter_id" => "$chapter_id"]
                    ]
                ]
            ]
        ];
        $uri = $this->creds['host']
                . $this->creds['port']
                . $this->creds['learnData']
                . '_search';

        $res = Functions::curlPostJson($uri, $arr);
        foreach ($res->hits->hits as $q) {
            $topic = [];
            $topic['id'] = $q->_id;
            $topic['topic'] = $q->_source->topic;
            $topic['order'] = $q->_source->sequence;
            $topics[] = $topic;
        }
        return $topics;
    }
    
    public function getquizQuestion($exam,$subject,$category,$chapter,$from,$size){
        $string = '';
        $quiz = [];
        $query = [];

        $examArray = [ "term" =>
            [
                "exam" => "$exam"
            ]
        ];

        $subjectArray = [ "term" =>
            [
                "subject" => strtolower($subject)
            ]
        ];

        $categoryArray = [ "term" =>
            [
                "category" => "$category"
            ]
        ];

        $chapterArray = [ "term" =>
            [
                "chapter" => "$chapter"
            ]
        ];

        if ($exam != null) {
            $query[] = $examArray;
        }
        if ($subject != null) {
            $query[] = $subjectArray;
        }
        if ($category != null) {
            $query[] = $categoryArray;
        }
        if ($chapter != null) {
            $query[] = $chapterArray;
        }

        $query1 = [
            "must" => $query
        ];
        //dd($query1);
        $questions = [];
        $arr = [
            "from" => $from,
            "size" => $size,
            "query" => [
                "filtered" => [
                    "filter" => [
                        "bool" => $query1
                    ]
                ]
            ]
        ];


        //$size = \Config::get('scholar.quiz.question.admin_search');
        $uri = $this->creds['host']
                . $this->creds['port']
                . $this->creds['questionsData']
                . '_search';
        //dd($arr);
        $res = Functions::curlPostJson($uri, $arr);
        foreach ($res->hits->hits as $q) {
            $d = $this->createQuestionData($q, false, false);
            $questions[] = $d;
        }
        
        return $questions;
    }

}
