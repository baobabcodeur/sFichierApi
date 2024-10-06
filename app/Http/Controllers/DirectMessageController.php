<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Direct_message;

class DirectMessageController extends Controller
{
    //
    public function send(Request $request)
    {
        $request->validate([
            'reciver_id' => 'required|exists:users,id',
            'content' => 'required|string',
        ]);

        $message = Direct_message::create([
            'sender_id' => auth()->id(),
            'reciver_id' => $request->reciver_id,
            'content' => $request->content,
        ]);

        return response()->json(['message' => 'Direct message sent successfully.', 'message' => $message], 201);
    }

    public function list($userId)
    {
        $messages = Direct_message::where(function ($query) use ($userId) {
            $query->where('sender_id', auth()->id())
                  ->where('reciver_id', $userId);
        })->orWhere(function ($query) use ($userId) {
            $query->where('sender_id', $userId)
                  ->where('reciver_id', auth()->id());
        })->get();

        return response()->json($messages);
    }
}
