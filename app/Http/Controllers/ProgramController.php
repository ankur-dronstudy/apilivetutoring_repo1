<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\models\Program;
use App\models\Department;
use App\models\CollegeProgram;
use App\Classes\SSAuth;
use DB;
use stdClass;

class ProgramController extends Controller {

    public function programList(Request $request) {
        $response = ['status' => false];
        $college_id = $request->get('ci');
        $query = "SELECT p.* FROM college_programs cp LEFT JOIN programs p on p.id = cp.program_id WHERE cp.college_id='$college_id'";

//        $conditions = array();
//
//        if ($college_id != 1) {
//            $conditions[] = "cp.college_id='$college_id'";
//        }
//
//        $sql = $query;
//        
//        if (count($conditions) > 0) {
//            $sql .= " WHERE " . implode(' AND ', $conditions);
//        }

        $pls = DB::select($query);


        if (count($pls) > 0) {
            $response['data'] = $pls;
            $response['status'] = true;
        }
        return response($response)->header('Content-Type', 'application/json');
    }

    public function DepartmentList(Request $request) {
        $response = ['status' => false];
        $college_id = $request->get('ci');
        $query = "SELECT * FROM departments WHERE college_id='$college_id'";
        $dls = DB::select($query);


        if (count($dls) > 0) {
            $response['data'] = json_decode($dls[0]->name);
            $response['status'] = true;
        }
        return response($response)->header('Content-Type', 'application/json');
    }

    public function DepartmentUpdate(Request $request) {
        $response = ['status' => false];
        $college_id = $request->get('ci');
        $role_id = $request->get('rid');
        $name = json_encode($request->get('name'));
        $update_data = DB::update("UPDATE college_onboard SET onboard_details = '$name' WHERE college_id='$college_id' AND role_id='$role_id'");

        if ($update_data) {
            $response['status'] = true;
        }

        return response($response)->header('Content-Type', 'application/json');
    }

}
