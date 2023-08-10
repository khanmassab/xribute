<?php

namespace App\Http\Controllers\Api\Chat;

use App\Events\MessageSent;
use App\Events\PrivateMessageEvent;
use App\Models\BusinessAssignedCategories;
use App\Models\BusinessProduct;
use App\Models\BusinessProfile;
use App\Models\Category;
use App\Models\Chat\ChatRoom;
use App\Models\Chat\Receiver;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ChatsController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show chats
     *
     * @return \Illuminate\Http\Response
     */

    public function index(Request $request)
    {
        $id = Auth::user()->id;
//        $allClients =ChatRoom::all();
        $users = null;

        $business_id = $request->from_business_id;
        $seller_business_id = $request->to_business_id;


//        if($role === 1){
//            $findOrder = Order::where('user_id', $id)->get();
//            foreach($findOrder as $findProfessional){
//                $professional = Order_item::where('order_id', $findProfessional->id)->first();
//                $users[] = $professional->professional_id;
//            }
//        }
//        else{
//            $findOrder = Order_item::where('professional_id', $id)->get();
//            foreach($findOrder as $findClients){
//                $clients = Order::where('id', $findClients->order_id)->first();
//                $users[] = $clients->user_id;
//            }
//        }
//
//        $all_users = User::where('id','!=',$id)->get();
//        foreach($all_users as $single_user){
//            $users[] = $single_user->id;
//        }

//        if(count($allClients)){
//            dd('count');
//            foreach($allClients as $allClient){
//                if(in_array($id, explode(',', $allClient->user_ids))) {
//                    $explode = explode(',', $allClient->user_ids);
//                    if(($key = array_search($id, $explode)) !== false) {
//                        unset($explode[$key]);
//                        $explode = array_values($explode);
//                        $users[] = $explode;
//                    }
//                }
//                else {
//                    if($role === 2)
//                        $users[] = $id; //professional id
//                    else{
//                        $users[] = '3';
//                    }
//                }
//            }
//        }
//        else{
//            //empty
////            dd('else');
//            if($role === 2)
//                $users[] = $id; //professional id
//            else{
//                $users[] = '3';
//            }
//        }


//        if ($users) {
//            foreach ($users as $user) {
//            if($role === 1) {
//                $chatUsers[] = User::where(['id' => $user, 'role' => 2])->get();
//            }
//            else
//            }

        $chat_id = '';
        $chatUsers = BusinessProfile::where(['id' => $seller_business_id])->get();
//            foreach ($chatUsers as $userx) {
//                if (count($userx) > 0) {
        $roomMembers = [$seller_business_id, $business_id];
        sort($roomMembers);
        $roomMembers = implode(',', $roomMembers);
        $chatRoom = ChatRoom::where(['user_ids' => $roomMembers, 'type' => 1])->first();
        if ($chatRoom === null) {
            $chatRoom = ChatRoom::create(['room_type' => 'private', 'user_ids' => $roomMembers, 'type' => 1]);
        }

        $message = \App\Models\Chat\Message::where('chat_room_id', $chatRoom->id)->latest('created_at')->first();
        if ($chatRoom === null) {
            $message = '';
        }

        $chat_id = $chatRoom->id;
        $user1[] = array('user' => $chatUsers, 'last_msg' => $message);
//                    $user1[] = array('user' => $userx[0], 'last_msg' => $message);
//                }
//            }
        return response()->json(['code' => 200, 'users' => $user1, 'chat_id' => $chat_id]);
//        }
//        else{
//            return response()->json(['code' => 400, 'error' => 'Multiple user not exist']);
//        }
    }


    /**
     * Fetch all messages
     *
     * @return Message
     */
    public function fetchMessages()
    {
        return Message::with('user')->get();
    }

    /**
     * Persist message to database
     *
     * @param Request $request
     * @return Response
     */
    public function sendMessage(Request $request)
    {
        $user = Auth::user();

        $message = $user->messages()->create([
            'message' => $request->input('message')
        ]);

        broadcast(new MessageSent($user, $message))->toOthers();

        return ['status' => 'Message Sent!'];
    }

    public function addQuotation(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'business_id' => 'required',
                'seller_business_id' => 'required',
                'product_id' => 'required',
                'date' => 'required',
                'description' => 'required',
            ]);


            if ($validator->fails()) {
                return response()->json(['code' => 422, 'error' => $validator->errors()->first()]);
            }

            $business_id = $request->business_id;
            $seller_business_id = $request->seller_business_id;
            $product_id = $request->product_id;
            $date = $request->date;
            $description = $request->description;

            $date = date('d-m-Y h:i:s', strtotime($date));

//            check business profile exist or not
            $business_profile = BusinessProfile::where('id', $business_id)->first();
            $seller_business_profile = BusinessProfile::where('id', $seller_business_id)->first();

            if ($business_profile && $seller_business_profile) {

//                check product exist or not
                $product = BusinessProduct::where(['id' => $product_id, 'business_id' => $seller_business_id])->first();

                if ($product) {

                    $roomMembers = [$seller_business_id, $business_id];
                    sort($roomMembers);
                    $roomMembers = implode(',', $roomMembers);
                    $chatRoom = ChatRoom::where('user_ids', $roomMembers)->first();
                    if ($chatRoom === null) {
                        $chatRoom = ChatRoom::create(['room_type' => 'private', 'user_ids' => $roomMembers]);
                    }

                    $message = new Message;
                    $message->chat_room_id = $chatRoom->id;
                    $message->sender_id = $business_id;
                    $message->product_id = $product_id;
                    $message->message = $description;
                    $message->type = '2';
                    $message->save();

                    $receiver = new Receiver;
                    $receiver->message_id = $message->id;
                    $receiver->receiver_id = $seller_business_id;

                    if ($receiver->save()) {
                        $message = \App\Models\Chat\Message::with('sender')->find($message->id);
                        $test = broadcast(new PrivateMessageEvent($message))->toOthers();
                    }

//                    $message = \App\Models\Chat\Message::where('chat_room_id', $chatRoom->id)->latest('created_at')->first();
//                    if ($chatRoom === null) {
//                        $message = '';
//                    }

                    return response()->json(['code' => 200, 'message' => 'Successfully send Quotations', 'msg' => $message]);

                } else {
                    return response()->json(['code' => 400, 'error' => 'Business product not exist']);
                }
            } else {
                return response()->json(['code' => 400, 'error' => 'Business profile not exist']);
            }


        } catch (\Exception $e) {
            DB::rollBack();
//             dd($e->getMessage());
            return response()->json([
                'code' => 500,
                'error' => 'An error occurred while creating the Quotation',
            ]);
        }
    }

    public function chatList(Request $request)
    {
        try {

            $validator = Validator::make($request->all(), [
                'business_id' => 'required',
                'type' => 'required',
            ]);


            if ($validator->fails()) {
                return response()->json(['code' => 422, 'error' => $validator->errors()->first()]);
            }

            $business_id = $request->business_id;
            $type = $request->type;
            $message = '';
            $user_list = [];

            $chatUsers = BusinessProfile::where(['is_active' => 1])->get();
            if (count($chatUsers) > 0) {
                foreach ($chatUsers as $userx) {
                    $message = '';
                    $roomMembers = [$business_id, $userx->id];
                    sort($roomMembers);
                    $roomMembers = implode(',', $roomMembers);
                    $chatRoom = ChatRoom::where(['user_ids' => $roomMembers, 'type' => $type])->first();

                    if ($chatRoom != null) {
                        $message = \App\Models\Chat\Message::where('chat_room_id', $chatRoom->id)->latest('created_at')->first();
                        $user_list[] = array('user' => $userx, 'last_msg' => $message);
                    }
                }
            }
            return response()->json(['code' => 200, 'users' => $user_list]);


        } catch (\Exception $e) {
            DB::rollBack();
//             dd($e->getMessage());
            return response()->json([
                'code' => 500,
                'error' => 'An error occurred while Getting user list',
            ]);
        }
    }
}
