<?php

namespace App\Http\Controllers;

use App\Events\ChatPrivate;
use App\Events\UserOnline;
use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessagePrivate;
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

    public function chatPrivate($id)
    {
       $user = User::find($id);
        $conversation = Conversation::query()
            ->where(function ($query) use ($id) {
                $query->where('user1_id', $id)
                    ->where('user2_id', Auth::user()->id);
            })
            ->orWhere(function ($query) use ($id) {
                $query->where('user1_id', Auth::user()->id)
                    ->where('user2_id', $id);
            })
            ->first();
        if(!$conversation){
            $conversation = Conversation::create([
                'user1_id' => $id,
                'user2_id' => Auth::user()->id
            ]);
        }
//        dd($conversation->id);
       $messages     = MessagePrivate::query()->with('user')->where('conversation_id', $conversation->id)->latest()->take(30)->get()->reverse();
        return view('chatprivate',compact('user','conversation','messages'));
    }

    public function sendPrivate(Request $request, $idUser)
    {
        $message = MessagePrivate::query()->create([
            "conversation_id" => $request->conversation_id,
            "user_id" => Auth::user()->id,
            "content" => $request->msg
        ]);

        // Sử dụng findOrFail để tìm kiếm người dùng với $idUser
        $recipient = User::findOrFail($idUser);

        // Phát sóng sự kiện ChatPrivate
        broadcast(new ChatPrivate($request->user(), $request->msg, $recipient,$message->created_at));

        return response()->json([
            'status' => true,
        ]);
    }

    public function leaving(Request $request, $id)
    {
        $user = User::find($id);

        if ($user) {
            $user->update([
                'last_seen' => now()
            ]);

            return response()->json([
                'status' => true
            ]);
        } else {
            return response()->json([
                'status' => false,
                'message' => 'Không tìm thấy người dùng.'
            ], 404);
        }
    }



}
