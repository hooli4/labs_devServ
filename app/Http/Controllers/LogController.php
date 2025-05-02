<?php

namespace App\Http\Controllers;

use App\DTOS\LogCollectionDTO;
use Illuminate\Http\Request;
use App\Models\ChangeLog;
use Illuminate\Support\Facades\Auth;

class LogController extends Controller
{
    public function showRoleStory(Request $request) {
        if (!in_array('GSR', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message' => 'Your role need permission `get-story-role`'], 403);
        }

        $entity_type = 'App\Models\Role';
        $entity_id = $request->id;

        $logs = ChangeLog::where('entity_type', $entity_type)->where('entity_id', $entity_id)->get();
        if ($logs) {
            $logs_collectionDTO = LogCollectionDTO::fromCollection($logs);
            return response()->json($logs_collectionDTO);
        }
        
        return response()->json(['message'=> 'Role logs are not found'], 404);
    }

    public function showUserStory(Request $request) {
        if (!in_array('GSU', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message' => 'Your role need permission `get-story-user`'], 403);
        }

        $entity_type = 'App\Models\User';
        $entity_id = $request->id;

        $logs = ChangeLog::where('entity_type', $entity_type)->where('entity_id', $entity_id)->get();
        if ($logs) {
            $logs_collectionDTO = LogCollectionDTO::fromCollection($logs);
            return response()->json($logs_collectionDTO);
        }
        
        return response()->json(['message'=> 'User logs are not found'], 404);
    }

    public function showPermissionStory(Request $request) {
        if (!in_array('GSP', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message' => 'Your role need permission `get-story-permission`'], 403);
        }

        $entity_type = 'App\Models\Permission';
        $entity_id = $request->id;

        $logs = ChangeLog::where('entity_type', $entity_type)->where('entity_id', $entity_id)->get();
        if ($logs) {
            $logs_collectionDTO = LogCollectionDTO::fromCollection($logs);
            return response()->json($logs_collectionDTO);
        }
        
        return response()->json(['message'=> 'Permission logs are not found'],404);
    }

    public function getBackToUserLog(Request $request) {
        if (!in_array('GBUL', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message' => 'Your role needs permission `get-back-user-log`'], 403);
        }

        if (!ChangeLog::where('entity_type', 'App\Models\User')->
        where('entity_id', $request->user_id)->
        where('id', $request->log_id)->get()->toArray()) {
            return response()->json(['message' => 'Log is not found'], 404);
        }

        Controller::getBackToLog(
            'App\Models\User',
            $request->user_id,
            $request->log_id    
        );

        return response()->json(['message' => 'Entity backed successfully']);
    }

    public function getBackToPermissionLog(Request $request) {
        if (!in_array('GBPL', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message' => 'Your role needs permission `get-back-permission-log`'], 403);
        }

        if (!ChangeLog::where('entity_type', 'App\Models\Permission')->
        where('entity_id', $request->permission_id)->
        where('id', $request->log_id)->get()->toArray()) {
            return response()->json(['message' => 'Log is not found'], 404);
        }

        Controller::getBackToLog(
            'App\Models\Permission',
            $request->permission_id,
            $request->log_id
        );

        return response()->json(['message' => 'Entity backed successfully']);
    }

    public function getBackToRoleLog(Request $request) {
        if (!in_array('GBRL', Controller::check_right(Auth::user()->id))) {
            return response()->json(['message' => 'Your role needs permission `get-back-role-log`'], 403);
        }

        if (!ChangeLog::where('entity_type', 'App\Models\Role')->
        where('entity_id', $request->role_id)->
        where('id', $request->log_id)->get()->toArray()) {
            return response()->json(['message' => 'Log is not found'], 404);
        }

        Controller::getBackToLog(
            'App\Models\Role',
            $request->role_id,
            $request->log_id
        );

        return response()->json(['message' => 'Entity backed successfully']);
    }


}
