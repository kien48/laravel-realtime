<?php

namespace App\Http\Controllers;

use App\Events\UserOnline;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    public function chat()
    {
        $users = User::where('id', '<>', Auth::user()->id)->get();
        $messages = Message::query()
            ->with('user')
            ->latest()
            ->take(30)
            ->get()
            ->reverse();

        return view('chat', compact('users', 'messages'));
    }

    public function send(Request $request)
    {
       $message = Message::query()->create([
           "user_id" => Auth::user()->id,
           "content" =>$request->msg
       ]);
        broadcast(new UserOnline($request->user(), $request->msg, $message->created_at));

        return response()->json([
            'status' => true,
            'message' => $message
        ]);
    }
}
