<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\LogRequest;
use Illuminate\Support\Facades\Auth;

class LogRequestController extends Controller
{
    public function showListLog(Request $request, 
    $sortBy = ['key' => '', 'order' => 'desc'],
    $filter = ['key' => '', 'value' => '18'],
    $page = 1, $count = 10) {

        if (!in_array('SLLR', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "show-list-log-requests"'],403);
        }

        $validate_filter = ['USER_ID', 'CODE_STATUS_RESPONSE', 'USER_IP_ADDRESS', 'USER_USER-AGENT', 'CONTROLLER_PATH'];

        if (!in_array($filter['key'], $validate_filter)) {
            return response()->json([
                'message' => 'Second parameter is filter, array type ["key" => "example_key", "value" => "example_value"].
                Possible example_key: 
                1) USER_ID;
                2) CODE_STATUS_RESPONSE;
                3) USER_ID_ADDRESS;
                4) USER_USER-AGENT;
                5) CONTROLLER_PATH
                '], 403);
        }

        $validate_sortBy = [
        'URL_API_METHOD',
        'NAME_METHOD_CONTROLLER',
        'CODE_STATUS_RESPONSE',
        'TIME_CALL',
        ];

        $validate_sortBy_order = ['asc', 'desc'];

        if (!in_array($sortBy['key'], $validate_sortBy) || (!in_array($sortBy['order'], $validate_sortBy_order))) {
            return response()->json([
                'message' => 'First parameter is sortBy, array type ["key" => "example_key", "order" => "example_order"].
                Possible example_key: 
                1) URL_API_METHOD;
                2) NAME_METHOD_CONTROLLER;
                3) CODE_STATUS_RESPONSE;
                4) TIME_CALL
                Possible example_order:
                1) asc;
                2) desc;
                '], 403);
        }

        if (!is_int($page) || !is_int($count)) {
            return response()->json(['message' => 'Third and fourth parameters (page and count) must be integer type']);
        }

        $skip = ($page - 1) * $count;

        $response = LogRequest::where($filter['key'], $filter['value'])->
        orderBy($sortBy['key'], $sortBy['order'])->
        select('URL_API_METHOD', 'NAME_METHOD_CONTROLLER', 'CONTROLLER_PATH', 'CODE_STATUS_RESPONSE', 'TIME_CALL')->
        skip($skip)->
        take($count)->get();

        return response()->json($response);
    }

    public function showLog(Request $request) {

        if (!in_array('SLR', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "show-log-request"'],403);
        }

        $response = LogRequest::find($request->id);

        if (!$response) {
            return response()->json(['message' => 'Unable to find log with that id'], 404);
        }

        return response()->json($response);
    }

    public function deleteLog(Request $request) {

        if (!in_array('DLR', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message'=> 'Your role need permission "delete-log-request"'],403);
        }

        $LogRequest = LogRequest::find($request->id);

        if (!$LogRequest) {
            return response()->json(['message' => 'Unable to find log with that id'], 404);
        }

        $LogRequest->delete();

        return response()->json(['message' => 'successfully deleted']);
    }
}
