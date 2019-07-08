<?php

    Route::get('/', function () {
    $data['success'] = 'false';
    $data['message'] = 'Not supported!';
    $res = response($data)->header('Content-Type', 'application/json');
    return $res;
    });

    Route::group(['prefix' => 'v1'], function() {
    Route::get('/', function () {
        $data['success'] = 'false';
        $data['message'] = 'Not supported!';
        $res = response($data)->header('Content-Type', 'application/json');
        return $res;
    });

    //Testing API
    Route::get('dummy.list', "DummyController@dummyList");
    Route::post('desktop.getInstruction', "ContentController@contentInstruction");

    /* Login */
    Route::post('auth.user.login', "AuthController@login");
    Route::post('auth.logout', "AuthController@logout");

    // After Authentication API
    Route::group(['middleware' => 'ssauth'], function () {
    Route::get('user.list', 'UserController@userList');
    Route::post('classroom.access', 'ClassRoomController@classRoomAccess');
    Route::get('user.access.ids', 'ClassRoomController@classRoomAccessId');
    Route::get('classroom.member', 'ClassRoomController@classRoomMember');
    Route::post('leave.classroom', 'ClassRoomController@leaveClassRoom');
    Route::get('classroom.playlist', 'ClassRoomController@classRoomPlaylist');
    Route::get('subtopic.detail', 'ContentController@subTopicDetails');
    Route::post('submit.instruction', 'ClassRoomController@submitLiveInstruction');
    Route::get('block.member', 'ClassRoomController@blockMember');;
    });
    
});

