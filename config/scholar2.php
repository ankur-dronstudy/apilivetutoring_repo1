<?php

$env = env('ENVIRONMENT', 'development');
if ($env === 'staging') {
    return [
        'jwt' => [
            'aud' => 'http://scholarspace.org'
            , 'iss' => 'http://api.scholarspace.org'
            , 'ssh' => 'EducationForAll'
            , 'salt' => 'sdfgdsfgsetpoermmzdsfgposdgsdsdfgsdfgopok,mbkrofld;gdfgk'
        ],
        'email' => [
            'ses' => [
                'host' => 'email-smtp.us-east-1.amazonaws.com',
                'port' => 25,
                'username' => 'AKIAIRXNQAIIYOF667JQ',
                'password' => 'AlaAfZEvGir3AARw+zuWTaW2FYsE3MBooNGyXRCstyO6'
            ]
        ],
        'sms' => [
            'txtguru' => [
                'url' => 'http://www.txtguru.in/imobile/api.php',
                'username' => 'scholarspaceorg',
                'password' => 'Educati0n4',
                'source' => 'SLRSPC',
            //Below 2 params are just for the documentation
//            'dmobile'   => '918284047608,918284047606',
//            'message'   => 'TEST+SMS+GATEWAY'
            ]
        ],
        'path' => [
            'profile' => 'images/profile/',
            'assignement' => '/assignements/',
            'learnpdf' => '/learnpdf/',
            'course' => '/images/course/',
            'event' => '/images/event/',
            'group' => '/images/group/',
            'companylogo' => '/image/company_logo/',
            'id_card' => '/images/id_card/',
            'chat_image' => '/images/chat_image/'
        ],
        'link' => [
            'staging' => [
                'fp' => 'http://scholarspace.org/auth/fp/update?',
                'ru' => 'http://scholarspace.org/auth/register?',
                'rfu' => 'http://scholarspace.org?',
                'act' => 'http://scholarspace.org/activate',
            ],
            'fp' => 'http://scholarspace.org/auth/fp/update?',
            'ru' => 'http://scholarspace.org/auth/register?',
            'rfu' => 'http://scholarspace.org?',
            'act' => 'http://scholarspace.org/activate',
        ],
        'expert' => [
            'to' => [
                'expert@scholarspace.org'
            ],
            'cc' => [
                'preparation@scholarspace.org'
            ]
        ],
        'imgcdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/',
        'asgncdn' => 'http://d26k3u4aijkphy.cloudfront.net/assignements/',
        'learnpdfcdn' => 'http://d26k3u4aijkphy.cloudfront.net/learnpdf/',
        'coursecdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/course',
        'groupcdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/group',
        'eventcdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/event/',
        'comapnycdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/company_logo',
        'idcardcdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/id_card',
        'chatimagecdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/chat_image',
        'payments' => [
            'razorpay' => [
                'key_id' => 'rzp_test_HTs4cGiZ93j88h', //TEST ID
                'key_secret' => 'H1alqGbNx8MM6g849aJQaa8a'//TEST SECRET
            ]
        ],
        'quiz' => [
            'question' => [
                'admin_search' => 100, //TEST ID
            ]
        ],
        'programs' => [
            11 => '11th Class',
            12 => '12th Class',
            13 => '12th Pass Class'
        ]
    ];
} else if ($env === 'live') {
    return [
        'jwt' => [
            'aud' => 'http://scholarspace.org'
            , 'iss' => 'http://api.scholarspace.org'
            , 'ssh' => 'EducationForAll'
            , 'salt' => 'sdfgdsfgsetpoermmzdsfgposdgsdsdfgsdfgopok,mbkrofld;gdfgk'
        ],
        'email' => [
            'ses' => [
                'host' => 'email-smtp.us-east-1.amazonaws.com',
                'port' => 25,
                'username' => 'AKIAIRXNQAIIYOF667JQ',
                'password' => 'AlaAfZEvGir3AARw+zuWTaW2FYsE3MBooNGyXRCstyO6'
            ]
        ],
        'sms' => [
            'txtguru' => [
                'url' => 'http://www.txtguru.in/imobile/api.php',
                'username' => 'scholarspaceorg',
                'password' => 'Educati0n4',
                'source' => 'SLRSPC',
            //Below 2 params are just for the documentation
//            'dmobile'   => '918284047608,918284047606',
//            'message'   => 'TEST+SMS+GATEWAY'
            ]
        ],
        'path' => [
            'profile' => 'images/profile/',
            'assignement' => '/assignements/',
            'learnpdf' => '/learnpdf/',
            'course' => '/images/course/',
            'event' => '/images/event/',
            'group' => '/images/group/',
            'companylogo' => '/image/company_logo/',
            'id_card' => '/images/id_card/',
            'chat_image' => '/images/chat_image/'
        ],
        'link' => [
            'staging' => [
                'fp' => 'http://scholarspace.org/auth/fp/update?',
                'ru' => 'http://scholarspace.org/auth/register?',
                'rfu' => 'http://scholarspace.org?',
                'act' => 'http://scholarspace.org/activate',
            ],
            'fp' => 'http://scholarspace.org/auth/fp/update?',
            'ru' => 'http://scholarspace.org/auth/register?',
            'rfu' => 'http://scholarspace.org?',
            'act' => 'http://scholarspace.org/activate',
        ],
        'expert' => [
            'to' => [
                'expert@scholarspace.org'
            ],
            'cc' => [
                'preparation@scholarspace.org'
            ]
        ],
        'imgcdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/',
        'asgncdn' => 'http://d26k3u4aijkphy.cloudfront.net/assignements/',
        'learnpdfcdn' => 'http://d26k3u4aijkphy.cloudfront.net/learnpdf/',
        'coursecdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/course',
        'groupcdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/group',
        'eventcdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/event/',
        'comapnycdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/company_logo',
        'idcardcdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/id_card',
        'chatimagecdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/chat_image',
        'payments' => [
            'razorpay' => [
                'key_id' => 'rzp_live_hv5R0Du8GbiRQg', //TEST ID
                'key_secret' => 'bCL3jjrMrRScodbtqJpFWeWt'//TEST SECRET
            ]
        ],
        'quiz' => [
            'question' => [
                'admin_search' => 100, //TEST ID
            ]
        ],
        'programs' => [
            11 => '11th Class',
            12 => '12th Class',
            13 => '12th Pass Class'
        ]
    ];
} else if ($env === 'development') {
    return [
        'jwt' => [
            'aud' => 'http://scholarspace.org'
            , 'iss' => 'http://api.scholarspace.org'
            , 'ssh' => 'EducationForAll'
            , 'salt' => 'sdfgdsfgsetpoermmzdsfgposdgsdsdfgsdfgopok,mbkrofld;gdfgk'
        ],
        'email' => [
            'ses' => [
                'host' => 'email-smtp.us-east-1.amazonaws.com',
                'port' => 25,
                'username' => 'AKIAIRXNQAIIYOF667JQ',
                'password' => 'AlaAfZEvGir3AARw+zuWTaW2FYsE3MBooNGyXRCstyO6'
            ]
        ],
        'sms' => [
            'txtguru' => [
                'url' => 'http://www.txtguru.in/imobile/api.php',
                'username' => 'scholarspaceorg',
                'password' => 'Educati0n4',
                'source' => 'SLRSPC',
            //Below 2 params are just for the documentation
//            'dmobile'   => '918284047608,918284047606',
//            'message'   => 'TEST+SMS+GATEWAY'
            ]
        ],
        'path' => [
            'profile' => 'images/profile/',
            'assignement' => '/assignements/',
            'learnpdf' => '/learnpdf/',
            'course' => '/images/course/',
            'event' => '/images/event/',
            'group' => '/images/group/',
            'companylogo' => '/image/company_logo/',
            'id_card' => '/images/id_card/',
            'chat_image' => '/images/chat_image/'
        ],
        'link' => [
            'staging' => [
                'fp' => 'http://scholarspace.org/auth/fp/update?',
                'ru' => 'http://scholarspace.org/auth/register?',
                'rfu' => 'http://scholarspace.org?',
                'act' => 'http://scholarspace.org/activate',
            ],
            'fp' => 'http://scholarspace.org/auth/fp/update?',
            'ru' => 'http://scholarspace.org/auth/register',
            'rfu' => 'http://scholarspace.org?',
            'act' => 'http://scholarspace.org/activate',
        ],
        'expert' => [
            'to' => [
                'expert@scholarspace.org'
            ],
            'cc' => [
                'preparation@scholarspace.org'
            ]
        ],
        'payments' => [
            'razorpay' => [
                'key_id' => 'rzp_test_HTs4cGiZ93j88h', //TEST ID
                'key_secret' => 'H1alqGbNx8MM6g849aJQaa8a'//TEST SECRET
            ]
        ],
        'quiz' => [
            'question' => [
                'admin_search' => 100, //TEST ID
            ]
        ],
        'imgcdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/',
        'asgncdn' => 'http://d26k3u4aijkphy.cloudfront.net/assignements/',
        'learnpdfcdn' => 'http://d26k3u4aijkphy.cloudfront.net/assignements/',
        'learnpdfcdn' => 'http://d26k3u4aijkphy.cloudfront.net/learnpdf/',
        'coursecdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/course',
        'groupcdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/group',
        'eventcdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/event/',
        'comapnycdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/company_logo',
        'idcardcdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/id_card',
        'chatimagecdn' => 'http://d26k3u4aijkphy.cloudfront.net/images/chat_image',
        'programs' => [
            11 => '11th Class',
            12 => '12th Class',
            13 => '12th Pass Class'
        ]
    ];
}