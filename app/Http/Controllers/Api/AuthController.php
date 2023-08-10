<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Models\EmailVerificationCode;
use Carbon\Carbon;
use Illuminate\Foundation\Console\EventMakeCommand;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\AccountType;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class AuthController extends Controller
{
//    /**
//     * Create User
//     * @param Request $request
//     * @return User
//     */
    public function register (Request $request) {
        try {
            $validator = Validator::make($request->all(), [
                'first_name' => 'required|string|max:10|regex:/^[a-zA-Z ]*$/',
//                'middlename' => 'string|sometimes|max:10|regex:/^[a-zA-Z ]*$/',
                'surname' => 'required|string|max:10|regex:/^[a-zA-Z ]*$/',
                'email' => 'required|string|email|max:255|unique:users',
                'phone' => 'required|string|unique:users,phone|digits_between:10,13',
                'password' => 'required|string|min:8|same:password_confirmation',
                'is_agreed_to_terms' => 'required|boolean|in:1,2',
                'account_type_id' => 'required|integer',
                // 'is_human' => 'boolean|NULLABLE',
            ]);
            if ($validator->fails())
            {
                return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
            }
            if($request['is_agreed_to_terms']==0){
                return response()->json(['code' => 422, 'error'=> "You cant sign up without agreeing to our terms!"]);
            }
//            $verificationCode = Str::random(6);
            // send this code via email to user
            $verificationCode = $this->generateCode();

            // register user
            $user = User::create([
                'phone' => $request->phone,
                'first_name' => $request->first_name,
                'middle_name' => $request->middlename,
                'surname' => $request->surname,
                'user_name' => $request->username,
                'email' => $request->email,
                // 'verification_code' => $verificationCode,
                /*'email_verified_at' => now(),*/
                'password' => Hash::make($request->password),
                'is_agreed_to_terms' => $request->is_agreed_to_terms,
                'account_type_id' => $request->account_type_id,
                'is_human' => 1,
            ]);

            $token = $user->createToken('Laravel Password Grant Client')->accessToken;
            $response = ['token' => $token,];

            $this->sendEmail($request->email,$request->first_name,$verificationCode);
            if($user) {
                EmailVerificationCode::updateOrCreate(['user_id' => $user->id],
                  [  'code' => $verificationCode,
                    'expires_at' => Carbon::now()->addMinutes(5),
                ]);

                $serialNumber = strtolower(Str::random(3) . rand(100, 999));
                while (BusinessProfile::where('serial_number', $serialNumber)->exists()) {
                    $serialNumber = strtolower(Str::random(3)  . rand(100, 999));
                }

                $create_profile = BusinessProfile::create(['serial_number' => $serialNumber, 'user_id' => $user->id, 'business_name' => $request->first_name .' '. $request->surname, 'business_age' => 10, 'time_response' => 24, 'pricing' => 1000,
                    'business_category_id' => 2, 'business_city_id' => 1, 'business_country_id' => 1,
                    'contact_number' => $request->phone, 'first_name' => $request->first_name,
                    'last_name' => $request->surname, 'is_active' => 2]);

                if ($create_profile) {
                    DB::commit();
//                DB::query("");
                    $response['user_id'] = $user->id;
                    $response['user'] = $user;
                    return response()->json(['code' => 200, 'message' => 'User created successfully', 'data' => $response]);
                }else{
                    DB::rollBack();
                    return response()->json(['code' => 500, 'error' => 'User Not created']);
                }
//                return $this->buildResponse(trans('general.user_created'),$response);
            }
            DB::rollBack();
            return response()->json(['code' => 500, 'error' => 'User Not created']);
//            return $this->notfoundResponse(trans('general.user_not_created'));
        } catch (\Throwable $e) {
            dd($e->getMessage());
            DB::rollBack();
            return response()->json(['code' => 500, 'error' => 'Something Went Wrong']);
        }
    }

    public function getUserTypes(){
        try {
            $userTypes = AccountType::all();
            if($userTypes){
                $response = response()->json(['code' => 200, 'message' => 'Success', 'data' => $userTypes]);
                return $response;
            }
        } catch (\Throwable $e) {
            // dd($e->getMessage());
            return response()->json(['code' => 500, 'message' => 'Something went wrong']);
        }

    }
//    /**
//     * Login The User
//     * @param Request $request
//     * @return User
//     */
    public function login (Request $request) {
        $validator = Validator::make($request->all(), [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);
        if ($validator->fails())
        {
            return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
        }
        $user = User::where('email', $request->email)->with('businessProfile')->first();
        if ($user) {
            if (Hash::check($request->password, $user->password)) {
                $token = $user->createToken('Laravel Password Grant Client')->accessToken;
                $response = ['token' => $token, 'email' => $user->email, 'user_id' => $user->id, 'user' => $user, 'business_id' => (isset($user->businessProfile) ? $user->businessProfile->id : 0)];
                return response()->json(['code' => 200, 'message' => 'Login Successfully', 'data' => $response]);
            } else {
//                $response = ["message" => "Password mismatch"];
                return response()->json(['code' => 401, 'error' => 'Password mismatch']);
//                return response($response, 422);
            }
        } else {
//            $response = ["message" =>'User does not exist'];
            return response()->json(['code' => 404, 'error' => 'User does not exist']);
//            return response($response, 422);
        }
    }

    public function verifyEmail(Request $request){
        $user_id = $request->user_id;
        $code = $request->code;
        $user = User::find($user_id);
        if(!$user) {
            return response()->json(['code' => 404, 'error' => 'User Not Found']);
//            return $this->notfoundResponse(trans('general.user_not_found'));
        }
        $code_data = EmailVerificationCode::where('user_id', $user_id)->where('expires_at','>', Carbon::now())->first();
        if(!$code_data)
            return response()->json(['code' => 404, 'error' => 'Code is Expired']);

        if($code == $code_data->code) {
            $user->update(['email_verified_at' => Carbon::now()]);
            $token = $user->createToken('Laravel Password Grant Client')->accessToken;
            $response = ['user_id' => $user_id, 'token' => $token];
            return response()->json(['code' => 200, 'message' => 'User is verified', 'data' => $response]);
//            return $this->buildResponse(trans('general.user_verified'), $response);
        }
        return response()->json(['code' => 404, 'error' => 'verification code not matched']);

//        return $this->errorResponse(trans('general.verification_code_not_matched'));
    }

    public function resendEmail(Request $request){
        $user_id = $request->user_id;
        $email = $request->email;
        if(!$user_id)
            return response()->json(['code' => 409,'error' => 'Invalid Data']);

        $user = User::find($user_id);
        if(!$user)
            return $this->notfoundResponse(trans('general.user_not_found'));

        DB::beginTransaction();
        $verificationCode = $this->generateCode();
        try {
            EmailVerificationCode::updateOrCreate(['user_id' => $user->id],
                ['code' => $verificationCode,
                'expires_at' => Carbon::now()->addMinutes(5),
            ]);
            $this->sendEmail($user->email, $user->first_name, $verificationCode);
            $response = ['user_id' => $user->id, 'email' => $user->email];
            DB::commit();
            return response()->json(['code' => 200, 'message' => 'Email Sent Successfully', 'data' => $response]);
        }
        catch (\Throwable $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'error' => 'Something Went Wrong']);
        }
    }

    function generateCode()
    {
        $otp = '';
        for ($i = 0; $i < 6; $i++) {
            $otp .= mt_rand(0, 9);
        }
        return $otp;
    }

    public function sendEmail($email, $first_name, $code){
//        $code = $this->generateCode();
        $details = [
            'first_name' => $first_name,
//                'user_name' => $request->username,
            'title' => 'Verification Email',
            'notes' => '',
            'code' => $code,
        ];
        $test = Mail::to($email)->send(new \App\Mail\UserVerificationMail($details));
        return ['code' => $code];
    }

    public function logout(){
        $user = Auth::user()->token();
        if($user){
            $user->revoke();
        }
        else{
            return 'You are not logged in';
        }
    }

    public function forgetPasswordEmail(Request $request){
        $email = $request->email;
        $user =  User::where('email', $request->email)->first();

        if(!($user))
            return response()->json(['code' => 409,'error' => 'No User found']);
        $user = User::find($user->id);

        DB::beginTransaction();
        $verificationCode = $this->generateCode();
        try {
            EmailVerificationCode::updateOrCreate(['user_id' => $user->id],
                ['code' => $verificationCode,
                'expires_at' => Carbon::now()->addMinutes(3),
            ]);

            $this->sendEmail($user->email, $user->first_name, $verificationCode);
            $response = ['user_id' => $user->id, 'email' => $user->email];
            DB::commit();
            return response()->json(['code' => 200, 'message' => 'Email Sent Successfully', 'data' => $response]);
        }

        catch (\Throwable $e) {
            DB::rollBack();
            // dd($e->getMessage());
            return response()->json(['code' => 500, 'error' => 'Something Went Wrong']);
        }
    }

    public function forgetPasswordVerifyCode(Request $request){
        $validator = Validator::make($request->all(),
            [
                'password' => 'required|confirmed:password_confirmation',
            ]);

        if($validator->fails()){
            return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
        }

        $user_id = $request->user_id;
        $code = $request->code;
        $user = User::find($user_id);

        if(!$user) {
            return response()->json(['code' => 404, 'error' => 'User Not Found']);
        }
        $code_data = EmailVerificationCode::where('user_id', $user_id)->first();
        if(!$code_data)
            return response()->json(['code' => 404, 'error' => 'Code is Expired']);

        if($code == $code_data->code) {
                // $strRandom = Illuminate\Support\Str::random(20);

                $response = User::find($code_data->user_id)->update([
                'password' => Hash::make($request->password)
            ]);
            return response()->json(['code' => 200, 'message' => 'Password has been changed']);
        }
        return response()->json(['code' => 404, 'error' => 'verification code not matched']);
    }

    public function enterVerificationCode(Request $request){

        $user_id = $request->user_id;
        $code = $request->code;
        $user = User::find($user_id);

        if(!$user) {
            return response()->json(['code' => 404, 'error' => 'User Not Found']);
        }
        $code_data = EmailVerificationCode::where('user_id', $user_id)->first();
        if(!$code_data)
            return response()->json(['code' => 404, 'error' => 'Code is Expired']);

        if($code == $code_data->code) {

            return response()->json(['code' => 200, 'message' => 'Success', 'user_id' => $user->id,]);
        }
        return response()->json(['code' => 404, 'error' => 'verification code not matched']);
    }
}
