<?php

namespace App\Http\Controllers\Api\V2;

use DateTime;
use App\Models\V2\Role;
use App\Models\User;
use App\Models\V2\Team;
use App\Models\V2\Business;
use App\Models\V2\TeamUser;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class TeamsController extends Controller
{
    public function createTeam(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'access' => 'required|string|max:255',
            'visibility' => 'nullable|boolean',
            'image' => 'required|image',
            'weekdays' => 'nullable|array',
            'weekdays.*' => 'nullable|string|in:Mon,Tue,Wed,Thu,Fri,Sat,Sun',
            // 'starttime' => 'nullable|date_format:H:i A',
            // 'endtime' => 'nullable|date_format:H:i A',
        ]);

        if($request->business_id){
            $business_id = auth()->user()->business()->find($request->business_id)->id;
        }

        if(!$request->business_id){
            $business_id = auth()->user()->business()->first()->id;
        }

        $image = $request->file('image');
        $imagePath = $image->store('public/team/images');
        $imageUrl = asset(str_replace('public/', 'storage/', $imagePath));

        $team = Team::updateOrCreate(['id' => $request['id']],[
            'business_id' => $business_id,
            'administrator_id' => auth()->id(),
            'name' => $request->input('name'),
            'role' => $request->input('role'),
            'access' => $request->input('access'),
            'image' => $imageUrl,
            'visiblity' => $request->input('visibility'),
            'weekdays' => implode(',', $request->input('weekdays')),
            'starttime' =>  DateTime::createFromFormat('h:i A', $request->starttime)->format('H:i'),
            'endtime' =>  DateTime::createFromFormat('h:i A', $request->endtime)->format('H:i'),
        ]);

        if($team){
            return response()->json(['code' => 200, 'message' => 'Team created successfully', 'data' => $team]);
        }

        return response()->json(['code' => 500, 'message' => 'Team could not be created']);
    }

    public function addUser(Request $request){
        $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|string|max:255',
            'email' => 'required|email',
            'team_id' => 'required|integer',
            'picture' => 'required',
        ]);

        if($request->business_id){
            $business = auth()->user()->business()->find($request->business_id);
        }

        if(!$business){
            $business = auth()->user()->business()->first();
        }

        $token = Str::random(40);

        $sender = auth()->user();
        $user = User::where('email', $request->input('email'))->first();
        if(!$user){
            $user = User::create([
                'first_name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make(12345678),
                'is_human' => 1,
                'is_agreed_to_terms' => 1,
                'account_type_id' => 1,
            ]);
        }

        $pic = $request->file('picture');
        $picPath = $pic->store('public/team_users/pictures');
        $picUrl = asset(str_replace('public/', 'storage/', $picPath));

        $sendingDetails = [
            'name' => $request->input('name'),
            'role' => $request->input('role'),
            'email' => $request->input('email'),
            'user_id' => $user->id,
            'sender_name' => $sender->first_name,
            'business' => $business->business_name,
            'token' => $token,
            'url' => url('/join_team').'?token='.$token,
        ];
        $sendEmail = Mail::to($sendingDetails['email'])->send(new \App\Mail\UserInvitationMail($sendingDetails));

        if($sendEmail){
            $teamUser = TeamUser::updateOrCreate(['email' => $sendingDetails['email']],[
                'name' => $sendingDetails['name'],
                'role' => $sendingDetails['role'],
                'email' => $sendingDetails['email'],
                'picture' => $picUrl,
                'team_id' => $request->input('team_id'),
                'user_id' => $user->id,
                'business_id' => $business->id,
                'status' => 0,
                'token' => $token,
            ]);
        }

        $teamUser['url'] = $sendingDetails['url'];

        if($teamUser){
            return response()->json(['code' => 200, 'message' => 'Team created successfully', 'data' => $teamUser]);
        }

        return response()->json(['code' => 500, 'message' => 'User could not be created or Invited. Try Again!']);
    }

    public function acceptInvitation(Request $request)
    {
        $token = $request->query('token');
        $teamUser = TeamUser::where('token', $token)->first();

        if (!$teamUser) {
            return response()->json(['code'=> 401, 'Invalid or Expired Token']);
        }

        $teamUser->status = 1;
        $teamUser->save();
        return response()->json(['code' => 200, 'message' => 'Invitation Accepted Successfully']);

    }

    public function updateRole(Request $request)
    {
        $validatedData = $request->validate([
            'role' => 'required|in:admin,profile',
            'team_id' => 'required',
            'team_user_id' => 'required',
        ]);

        $role = Role::where('role', $validatedData['role'])->first();

        if (!$role) {
            return response()->json(['error' => 'Invalid role'], 400);
        }

        $teamUser = TeamUser::where('team_id', $request->team_id)
                            ->where('id', $request->team_user_id)
                            ->first();

                            // dd($teamUser);
        if (!$teamUser) {
            return response()->json(['error' => 'User not found in the team'], 404);
        }

        $teamUser->update(['role_id' => $role->id]);

        return response()->json(['message' => 'Role updated successfully']);
    }

    public function getRoles(){
        $roles = Role::all();

        return response()->json(['code' => 200, 'message' => 'Roles fetched successfully', 'data' => $roles]);

    }


    public function manageUser(Request $request)
    {
        $request->validate([
            'team_id' => 'required|integer',
            'user_id' => 'required|integer',
            'role' => 'required|string',
        ]);

        $team =  Team::find($request['team_id']);
        if(!$team->administrator_id == auth()->id){
            return response()->json(['code'=> 401, 'message' => 'You are not authorized to perform the task']);
        }


        $teamUser = TeamUser::updateOrCreate(['team_id' => $request->input('team_id'), 'user_id' => $request->input('user_id')],[
            'team_id' => $request['team_id'],
            'user_id' => $request['user_id'],
            'role' => $request['role'],
            'administrator_id' => auth()->id(),
        ]);
    }

    public function getCompleteTeam(Request $request, $Id){
        if(!$Id){
            return response()->json(['code' => 422, 'message' => 'Please specify a team id in the URL.']);
        }

        $name = $request->name;
        $teamId = $request->team_id;

        $business = Business::with(['teamUsers' => function ($query) use ($name, $teamId) {

            if ($name) {
                $query->where('name', 'like', '%' . $name . '%');
            }

            if ($teamId) {
                $query->where('team_id', $teamId);
            }
            $query->take(12);
        }])->find($Id);

        $teamUsersCount = $business->team_users_count;

        return response()->json(['code' => 200, 'message' => 'Successfully fetched Team Data', 'count' => $teamUsersCount, 'data' => $business]);
    }

    public function getTeamNames($Id){
        if(!$Id){
            return response()->json(['code' => 422, 'message' => 'Please specify a team id in the URL.']);
        }

        $teams = Team::select('id', 'name')->where('business_id', $Id)->get();

        return response()->json(['code' => 200, 'message' => 'Successfully fetched Team Names', 'data' => $teams]);

    }

    public function getInvitedUsers($Id){
        if(!$Id){
            return response()->json(['code' => 422, 'message' => 'Please specify a team id in the URL.']);
        }

        $teams = TeamUser::where('business_id', $Id)->get();

        return response()->json(['code' => 200, 'message' => 'Successfully fetched Invited Users', 'data' => $teams]);

    }

    public function deleteInvitation($Id){
        if(!$Id){
            return response()->json(['code' => 422, 'message' => 'Please specify a team id in the URL.']);
        }

        $teams = TeamUser::find($Id);
        $teams->delete();

        return response()->json(['code' => 200, 'message' => 'Successfully deleted team user']);

    }

    public function resendInvitation($id){

        $sender = auth()->user();
        $user = TeamUser::find($id);

        if(!$user){
            return response()->json(['code' => 404, 'message' => 'No user found against this ID']);
        }

        $business = $user->business;

        $token = Str::random(40);

        $sendingDetails = [
            'name' => $user->name,
            'role' => $user->role,
            'email' => $user->email,
            'user_id' => $user->id,
            'sender_name' => $sender->first_name,
            'business' => $business->business_name,
            'token' => $token,
            'url' => url('/join_team').'?token='.$token,
        ];

        $sendEmail = Mail::to($sendingDetails['email'])->send(new \App\Mail\UserInvitationMail($sendingDetails));

        if($sendEmail){
            $user->update([
                'token' => $token,
            ]);
        }

        $teamUser['url'] = $sendingDetails['url'];

        if($teamUser){
            return response()->json(['code' => 200, 'message' => 'Invitation resent', 'data' => $teamUser]);
        }

        return response()->json(['code' => 500, 'message' => 'User could not be created or Invited. Try Again!']);
    }

}
