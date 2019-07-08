<?php

namespace App\Classes;

use LaravelFCM\Message\OptionsBuilder;
use LaravelFCM\Message\PayloadDataBuilder;
use LaravelFCM\Message\PayloadNotificationBuilder;
use LaravelFCM\Message\Topics;
use FCM;
use App\library\Functions;

class SSPushNotification{
    private $push;

    public function pushNotification($title,$content,$access_token,$url,$sender_token) {

        $optionBuilder = new OptionsBuilder();
        $optionBuilder->setTimeToLive(60 * 20);

        $notificationBuilder = new PayloadNotificationBuilder($title);
        $notificationBuilder->setBody($content)->setSound('default')->setIcon('notification_icon')->setClickAction('FCM_PLUGIN_ACTIVITY');

        $data = array();


        $data['title'] = $title;
        $data['content'] = $content;
        $data['sender_token'] = $sender_token;
        $data['url'] = $url;

        $dataBuilder = new PayloadDataBuilder();
        $dataBuilder->addData(['a_data' => $data]);

        $option = $optionBuilder->build();
        $notification = $notificationBuilder->build();
        $data = $dataBuilder->build();

        $token = $access_token;

        $downstreamResponse = FCM::sendTo($token, $option, $notification, $data);
        $downstreamResponse->numberSuccess();
        $downstreamResponse->numberFailure();
        $downstreamResponse->numberModification();

//return Array - you must remove all this tokens in your database
        $downstreamResponse->tokensToDelete();

//return Array (key : oldToken, value : new token - you must change the token in your database )
        $downstreamResponse->tokensToModify();

//return Array - you should try to resend the message to the tokens in the array
        $downstreamResponse->tokensToRetry();

        if (count($downstreamResponse->numberSuccess()) != 0 ) {
           return true;
       }
       else
       {
        return false;
    }
}



}
