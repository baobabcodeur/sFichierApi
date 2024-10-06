<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\GroupController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\DirectMessageController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('register', [AuthController::class, 'register']);
Route::post('verify-otp', [AuthController::class, 'verifyOtp']);
Route::post('login', [AuthController::class, 'login']);
Route::post('invite', [AuthController::class, 'invite']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('groups', [GroupController::class, 'create']);
    Route::get('groups', [GroupController::class, 'list']);
    Route::post('groups/{groupId}/members', [GroupController::class, 'addMember']);
    Route::get('groups/{groupId}/members', [GroupController::class, 'listMembers']);
    
    Route::post('groups/{groupId}/messages', [MessageController::class, 'sendMessage']);
    Route::get('groups/{groupId}/messages', [MessageController::class, 'listMessages']);
    Route::post('groups/{groupId}/files', [MessageController::class, 'uploadFile']);

    Route::post('direct-messages', [DirectMessageController::class, 'send']);
    Route::get('direct-messages/{userId}', [DirectMessageController::class, 'list']);
    Route::get('groups/{group}/non-members', [GroupController::class, 'getNonMembers']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/user/groups', [GroupController::class, 'getUserGroups']);
    Route::get('/groups/{groupId}/files', [MessageController::class, 'listFiles']);
 
});

