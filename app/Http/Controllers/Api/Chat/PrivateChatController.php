<?php

namespace App\Http\Controllers\Api\Chat;

use App\Models\BusinessProfile;
use App\Models\Chat\ChatRoom;
use App\Events\PrivateMessageEvent;
use App\Http\Controllers\Controller;
use App\Models\Chat\Message;
use App\Models\Chat\Receiver;
use App\Models\MessageFile;
use App\Models\MessageFileType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PrivateChatController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function get(ChatRoom $chatroom)
    {
        $senderId = request('business_id');
        if (in_array($senderId, explode(',', $chatroom->user_ids))) {
            return $chatroom->messages;
        }

        return '';
    }

    public function index(Request $request)
    {
        $services = null;
        $receiverId = $request->chat_id;
        $id = $request->business_id;


        $all_users = BusinessProfile::get();

        foreach ($all_users as $single_user) {
            $users[] = $single_user;
        }

        $users = array_unique($users);
        $chat_user = [];
        $user1 = [];
        $message = null;
        foreach ($users as $user) {
            $roomMembers = [$id, $user->id];
            sort($roomMembers);
            $roomMembers = implode(',', $roomMembers);

            $chatRoom = ChatRoom::where(['user_ids' => $roomMembers, 'type' => 1])->first();
            if ($chatRoom) {
                $message = Message::where(['chat_room_id' => $chatRoom->id, 'type' => 1])->latest('created_at')->first();

                $user1[] = array('user' => $user, 'last_msg' => $message);
            }
        }

        $receiver = BusinessProfile::find($id);
        $senderUserId = $id;
        $roomMembers = [$receiverId, $senderUserId];
        sort($roomMembers);
        $roomMembers = implode(',', $roomMembers);

        $chatRoom = ChatRoom::where(['user_ids' => $roomMembers, 'type' => 1])->first();

        return response()->json(['code' => 200, 'chatRoom' => $chatRoom, 'receiver' => $receiver, 'users' => $user1]);
    }

    public function store(Request $request)
    {

//        $senderId = auth()->user()->id;
        $chat_id = $request->chat_id;
        $senderId = $request->business_id;
        $file = $request->file;

        $chatroom = ChatRoom::where('id', $chat_id)->first();

        if ($chatroom) {

            $roomMembers = collect(explode(',', $chatroom->user_ids));
            $roomMembers->forget($roomMembers->search($senderId));
            $receiverId = $roomMembers->first();

            $message = new Message;
            $message->chat_room_id = $chatroom->id;
            $message->sender_id = $senderId;
            $message->type = '1';
            $message->message = $request->message;
            $message->save();



            if (isset($file) && $request->hasFile('file')) {
                $file_path = $this->save_image_to_s3($request);
                $get_message_type = MessageFileType::where(['name' => $file_path['type'], 'is_active' => 1])->first();

                $file_extension_type = 1;

                if ($get_message_type)
                    $file_extension_type = $get_message_type->id;

                    $file_save = MessageFile::create(['message_id' => $message->id,'file_type_id' => $file_extension_type , 'file' => $file_path['file_url'], 'file_name' => $file_path['name']]);
            }

            $receiver = new Receiver;
            $receiver->message_id = $message->id;
            $receiver->receiver_id = $receiverId;

            $response = [];
            if ($receiver->save()) {

                $message = Message::with(['sender' , 'files'])->find($message->id);
                $response = [
                    'message' => $message->message . "",
                    'file' => (isset($message->files) ? $message->files->file : '') . "",
                    'file_name' => (isset($message->files) ? $message->files->name : '') . "",
                    'message_id' => $message->id,
                    'chat_room_id' => $message->chat_room_id,
                    'sender_id' => $message->sender_id,
                    'sender_first_name' => $message->sender->first_name,
                    'sender_last_name' => $message->sender->last_name,
                    'sender_business_name' => $message->sender->business_name,
                    'sender_image' => $message->sender->image,

                ];
//                $test = broadcast(new PrivateMessageEvent($message))->toOthers();
                return response()->json(['code' => 200, 'msg' => 'message send successfully', 'message' => $response]);
            }
        } else {
            return response()->json(['code' => 400, 'error' => 'user chat not found']);
        }

        return 'Something went wrong!!';
    }

    private function save_image_to_s3($request)
    {
        $file = $request->file('file');
        $file_extension = $file->getClientOriginalExtension();
        $file_originalName = $file->getClientOriginalName();
        $filename = (time() + random_int(100, 1000));
        $filename_path = $filename . '.' . $file_extension;


        $file_url = Storage::disk('s3')->putFileAs('chat_file/' . $request->chat_id . '/' . $filename, $file, $filename_path);
        if (isset($file_url)) {
            $file_url = Storage::disk('s3')->url($file_url);
        }

        if ($file_url)
            return ['name' => $file_originalName, 'type' => $file_extension, 'file_url' => $file_url];

        return false;

    }
}
