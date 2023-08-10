<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Country;
use App\Traits\UserTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;

//Models
use App\Models\User;
use App\Models\UserDetail;
use App\Models\NationalityDetail;
use App\Models\UserAddress;
use App\Models\TaxationDetail;
use App\Models\UserNotificationSetting;
use App\Models\UserContactDetail;
use App\Models\UserImage;
use App\Models\DateFormat;
use App\Models\UserLanguage;
use App\Models\UserGender;
use App\Models\UserPrefix;
use App\Models\ProfileCountrieOfResidence;
use App\Models\ProfileNationality;

//Facades
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

use Exception;

class ProfileController extends Controller
{
    use UserTrait;
    public function imageStore(Request $request)
    {
//        return $request->file('image');
        try {
            $validator = Validator::make($request->all(), [
                'image' => 'required|mimes:jpg,png,jpeg,gif,svg',
                'type' => 'required'
            ]);
            if ($validator->fails())
            {
                return response()->json(['code' => 422, 'error'=> $validator->errors()->first()]);
            }
            $user_detail = UserDetail::where('user_id' , Auth::user()->id)->first();
            $filename = (time()+ random_int(100, 1000));
            $extension = $request->image->getClientOriginalExtension();
            $filename = '.' . $extension;

            if($request->type == '1') { // profile
                $filePath = 'profile_image/' . $filename;
                $path = Storage::disk('s3')->put($filePath, file_get_contents($request->image));
                $path = Storage::disk('s3')->url($filePath);
                if($user_detail && !is_null($user_detail->profile_image)){
                    $url_array = preg_split("/\//", $user_detail->profile_image);
                    if(count($url_array) == 5) {
                        $delete_path = $url_array[3] . '/' . $url_array[4];
                        Storage::disk('s3')->delete($delete_path);
                    }
                }
                $data = UserDetail::updateOrCreate(['user_id' => Auth::user()->id], ['profile_image' => $path]);
            }
            else { // cover
                $filePath = 'cover_image/' . $filename;
                $path = Storage::disk('s3')->put($filePath, file_get_contents($request->image));
                $path = Storage::disk('s3')->url($filePath);
                if($user_detail && !is_null($user_detail->profile_image)){
                    $url_array = preg_split("/\//", $user_detail->cover_image);
                    if(count($url_array) == 5) {
                        $delete_path = $url_array[3] . '/' . $url_array[4];
                        Storage::disk('s3')->delete($delete_path);
                    }
                }
                $data = UserDetail::updateOrCreate(['user_id' => Auth::user()->id], ['cover_image' => $path]);
            }
            if($data){
                $response = $this->GET_USER_DATA(Auth::user()->id);
                $data = [];
                if(isset($response['status']) && $response['status'] == true) {
                    $data = $response['data'];
                }
                return response()->json(['code' => 200, 'message' => 'Image Uploaded Successfully', 'image_url' => $path, 'data' => $data]);
            }
        } catch (Exception $e) {
            return response()->json(['code' => 500, 'error' => 'Something Went Wrong']);
        }
    }

    public function nationalityPost(Request $request){
        try {
            $validatedData = $request->validate([
                // 'user_id' => 'required|exists:users,id',
                'country_id' => 'required',
                'proof_type' => 'required|string',
                'nationality_proof' => 'required|image|max:2048'
            ]);


            // Handle the nationality proof image
            $nationalityProof = $request->file('nationality_proof');
            $fileNameToStore = time().'.'.$nationalityProof->getClientOriginalExtension();
            $path = $nationalityProof->storeAs('public/nationality_proofs', $fileNameToStore);

            // Create the country of nationality record

            $nationality = ProfileNationality::updateOrCreate(
                ['user_id' => auth()->id(), 'country_id' => $request->country_id,
                'proof_type' => $request->proof_type, 'nationality_proof' => $fileNameToStore]
            );

            if($nationality){
                return response()->json([
                    'code' => 200,
                    'message' => 'Nationality added successfully',
                    'data' => $nationality
                ]);
            }

            return response()->json([
                'code' => 500,
                'message' => 'Nationality cannot be added.',
                // 'nationality' => $nationality
            ]);

        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }

    public function residencePost(Request $request){
        try {
            $validatedData = $request->validate([
                // 'user_id' => 'required|exists:users,id',
                'country_id' => 'required|exists:countries,id',
                'proof_type' => 'required|string',
                'residence_proof' => 'required|image|max:2048'
            ]);

            // Handle the nationality proof image
            $residenceProof = $request->file('nationality_proof');
            $fileNameToStore = time().'.'.$nationalityProof->getClientOriginalExtension();
            $path = $residenceProof->storeAs('public/nationality_proofs', $fileNameToStore);

            // Create the country of nationality record
            $residence = new ProfileCountriesOfResidence;
            $residence->user_id = $request->input('user_id');
            $residence->country_id = $request->input('country_id');
            $residence->proof_type = $request->input('proof_type');
            $residence->residence_proof = $fileNameToStore;
            $residence->save();

            if($nationality){
                return response()->json([
                    'code' => 200,
                    'message' => 'Countries of Residence updated successfully',
                    'data' => $nationality
                ]);
            }

            return response()->json([
                'code' => 500,
                'message' => 'Countries of Residence cannot be updated.',
                // 'nationality' => $nationality
            ]);

        } catch (Exception $th) {
            // dd($th->getMessage());
        }
    }
    public function profilePost(Request $request)
    {
        try {
            $validatedData = $request->validate([
                'first_name' => "string|min:2",
                'middle_name' => 'string|nullable',
                'surname' => 'string',
                'birth_name' => 'string|nullable',
            ]);
            $userAuth = auth();
            $userDetail = User::find($userAuth->id())->update([
                'first_name' => $request['first_name'],
                'middle_name' => $request['middle_name'],
                'surname' => $request['surname'],
            ]
            );

            $userLegalDetails = UserDetail::updateOrCreate(['user_id' => $userAuth->id()],[
                'user_id' => $userAuth->id(),
                'prefix_id' => $request['prefix'],
                'language_id' => $request['language'],
                'gender_id' => $request['gender'],
                'country_id' => $request['country'],
                'birth_name' => $request['birth_name'],
                'date_of_birth' => $request['date_of_birth'],
                'place_of_birth_id' => $request['place_of_birth'],
                'city' => $request['city'],
            ]);

            $reponse = ['user' => $userDetail, 'user_details' => $userLegalDetails];

            if($userLegalDetails){
                return response()->json([
                    'code' => 200,
                    'message' => 'Successfully updated user details',
                    'data' => $reponse//['user' => $user, 'user_details' => $userDetails]
                ]);
            }

            return response()->json([
                'code' => 500,
                'message' => 'User Details cannot be updated.',
            ]);
        } catch (\Throwable $th) {
            dd($th->getMessage());
        }
    }

    public function updateAddress(Request $req){
        try{
            $validator = Validator::make($req->all(),
            [
                'user_address' => 'required|string|regex: /^[a-zA-Z]/|regex:/([- ,\/0-9a-zA-Z]+)/|max:30',
                'building_name' => 'required|string|max:20', //regex: /^[a\-zA-Z]+/|
                'street_no' => 'required|numeric|regex:/^[0-9]+$/',
                'city' => 'required|string|regex: /^[a-zA-Z]+$/|max:15',
                'postal_code' => 'required|integer|regex:/^[0-9]+$/',
                'country' => 'required|numeric',
             // 'country' => 'required|string|regex: /^[a-zA-Z]+$/|max:15',
                'business' => 'required|boolean',
                'business_tax_number' => 'numeric|min:5|required_if:business,1|nullable',
            ]);

            // if(!$req['business']){
            //     $validator->fails();
            //     return response()->json(['code' => 422, 'error'=> 'Please check the business']);
            // }

            if ($validator->fails())
            {
                return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
            }

            if(!($req['business'] )){
                $req['business_tax_number'] = NULL;
            }

            DB::beginTransaction();
            $user = Auth::user();

            $userAddress = UserAddress::updateOrCreate(['user_id'=> $user->id],
                [
                    'user_id' => $user->id,
                    'user_address' => $req['user_address'],
                    'building_name' => $req['building_name'],
                    'street_no' => $req['street_no'],
                    'city' =>$req['city'],
                    'postal_code' => $req['postal_code'],
                    'country_id' => $req['country'],
                    'business_tax_number' => $req['business_tax_number']
                ]
            );

            if($userAddress) {
                DB::commit();
                $response = $this->GET_USER_DATA($user->id);
                $data = [];
                if(isset($response['status']) && $response['status'] == true) {
                    $data = $response['data'];
                }
                return response()->json(['code' => 200, 'message' => 'User Address Successfully Updated', 'data' => $data]);
            }

            } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'error' => 'Something Went Wrong'. $e->getMessage()]);
        }
    }

    public function updateNotificationSetting(Request $req){
        try{
            $validator = Validator::make($req->all(),
            [
                'show_notifications' => 'boolean',
                'pop_up_notifications' => 'boolean',
                'preview_notifications' => 'boolean',
                'flash_notifications' => 'boolean',
            ]);

            if($validator->fails()){
                return response()->json([
                    'message' => 'Validation Error',
                    'error' => $validator->errors()->first()
                ]);
            }

            DB::beginTransaction();
            $user = Auth::user();
            $notificationSettings = UserNotificationSetting::updateOrCreate(['user_id'=> $user->id],
                [
                    'user_id' => auth()->id(),
                    'show_notifications' => $req['show_notifications'],
                    'pop_up_notifications' => $req['pop_up_notifications'],
                    'preview_notifications' => $req['preview_notifications'],
                    'flash_notifications' => $req['flash_notifications'],
                ]
            );
            DB::commit();
            if($notificationSettings) {
                $response = $this->GET_USER_DATA($user->id);
                $data = [];
                if(isset($response['status']) && $response['status'] == true) {
                    $data = $response['data'];
                }
                return response()->json(['code' => 200, 'message' => 'Notification Settings Successfully Updated', 'data' => $data]);
            }
            } catch (Exception $e) {
             DB::rollBack();
            // dd($e->getMessage());
            return response()->json(['code' => 500, 'error' => 'Something Went Wrong']);
        }
    }

    public function updateContactDetails(Request $req){
        try{
            $validator = Validator::make($req->all(),
            [
                'telephone' => 'numeric|digits_between:10,13|nullable',
                'fax' => 'numeric|nullable|digits:11',
                'whatsapp_number' => 'nullable|digits_between:10,13|numeric',
                'additional_email' => 'email|unique:users,email|nullable',
                'additional_phone' => 'nullable|numeric|digits_between:10,13',
                // 'add_additional_phone' => 'boolean'
            ]);

            if($req['add_additional_phone'] && $req['additional_phone'] == NULL){
                $validator->fails();
            }

            if($validator->fails()){
                return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
            }

        DB::beginTransaction();
            $user = Auth::user();
            $contactDetails = UserContactDetail::updateOrCreate(['user_id'=> $user->id],
                [
                    'user_id' => $user->id,
                    'telephone' => $req['telephone'],
                    'fax' => $req['fax'],
                    'whatsapp_number' => $req['whatsapp_number'],
                    'additional_email' => $req['additional_email'],
                    // 'add_additional_phone' => $req['add_additional_phone'],
                    'additional_phone' => $req['additional_phone']
                ]
            );

            if($contactDetails) {
                DB::commit();
                $response = $this->GET_USER_DATA($user->id);
                $data = [];
                if(isset($response['status']) && $response['status'] == true) {
                    $data = $response['data'];
                }
                return response()->json(['code' => 200, 'message' => 'Contact Details Successfully Updated', 'data' => $data]);
            }
            } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['code' => 500, 'error' => 'Something Went Wrong']);
        }
    }

    public function updatePassword(Request $req){
        try{
            $validator = Validator::make($req->all(),
            [
                'previous_password' => 'required',
                'new_password' => 'required|confirmed:new_password_confirmation',
            ]);

            if($validator->fails()){
                return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
            }

            if(!Hash::check($req->previous_password, Auth::user()->password)){
                return response()->json(['code' => 401, 'error' => 'Incorrect Old Password']);
            }

            $pass = User::find(Auth::user()->id)->update([
                'password' => Hash::make($req->new_password)
            ]);

            if($pass){
                return response()->json(['code' => 200, 'error' => 'Password Updated']);
            }

            } catch (Exception $e) {
            DB::rollBack();
            // dd($e->getMessage());
            return response()->json(['code' => 500, 'error' => 'Something Went Wrong']);
        }
    }

    public function getUserData(){
            $userId = Auth::user()->id;
//            if($userId){
                //getting user data if it exists
//                $user = User::with('dateFormat', 'userDetailData','userDetailData.Country', 'nationalityDetailData', 'nationalityDetailData.Country','taxationDetailData', 'taxationDetailData.Country' ,'userAddressData', 'userNotificationSettingData', 'userContactDetailData')->find($userId);
                //$userDetail = UserDetail::where('user_id', $userId)->with('Country')->first();
                //$nationalityDetails = NationalityDetail::where('user_id', $userId)->with('Country')->first();
                //$taxationDetails = TaxationDetail::where('user_id', $userId)->with('Country')->first();
                //$userAddress = UserAddress::where('user_id', $userId)->first();
                //$userNotificationSettings = UserNotificationSetting::where('user_id', $userId)->first();
                //$userContactDetail = UserContactDetail::where('user_id', $userId)->first();
                // $userImage = UserImage::where('user_id', $userId)->get();
//                $all_countries = Country::select(['id','iso','name','nice_name','iso3','numcode','phonecode'])->get();
//                $date_formats_list = DateFormat::select(['id', 'date_format'])->get();
//            }
            // return response()->json(['code' => 400, 'error' => 'User Not Found']);
//            if($user){
                $response = $this->GET_USER_DATA($userId);
                if(isset($response['status']) && $response['status'] == true){
                    $data = $response['data'];
//                $data = ['user' => null, 'user_detail' => null, 'user_nationality' => null, 'user_taxation' => null, 'user_address' => null,'user_notification_setting' => null, 'user_contact_detail' => null, 'date_formats_list' => null, 'countries_list' => null];
//                $data['user'] = ['first_name' => $user->first_name, 'middle_name' => $user->middle_name, 'surname' => $user->surname, 'user_name' => $user->user_name, 'phone' => $user->phone, 'email' => $user->email, 'date_format_id' => $user->date_format_id, ];
//                $date_format = 'Y-m-d';
//                if(isset($user->dateFormat)){
//                    $date_format = $user->dateFormat->date_format;
//                }
//                if(isset($user->userDetailData)) {
//                    $user_detail = $user->userDetailData;
//                    $data['user_detail'] = [
//                    'title' => $user_detail->title,
//                    'gender' => $user_detail->gender,
//                    'birth_name' => $user_detail->birth_name,
//                    'date_of_birth' => date($date_format, strtotime($user_detail->date_of_birth)),
//                    'place_of_birth' => $user_detail->place_of_birth,
//                    'city' => $user_detail->city,
//                    'profile_image' => $user_detail->profile_image,
//                    'cover_image' => $user_detail->cover_image,
//                    'country_name' => (!is_null($user_detail->Country)) ? $user_detail->Country->name : null,
//                    'country_id' => $user_detail->country_id
//                        ];
//                }
//                if(isset($user->nationalityDetailData)) {
//                    $user_nationality = $user->nationalityDetailData;
//                    $data['user_nationality'] = [
//                        'nationality' => $user_nationality->nationality,
//                        'name_on_id' => $user_nationality->name_on_id,
//                        'national_id' => $user_nationality->national_id,
//                        'issue_date' => date($date_format, strtotime($user_nationality->issue_date)) ,
//                        'expiry_date' =>date($date_format, strtotime($user_nationality->expiry_date)),
//                        'country_name' => (!is_null($user_nationality->Country)) ? $user_nationality->Country->name : null,
//                        'country_id' => $user_nationality->country_id
//                    ];
//                }
//                if(isset($user->taxationDetailData)) {
//                    $user_taxation = $user->taxationDetailData;
//                    $data['user_taxation'] = [
//                        'tax_id' => $user_taxation->tax_id,
//                        'country_name' => (!is_null($user->taxationDetailData->Country)) ? $user->taxationDetailData->Country->name : null,
//                        'country_id' => $user_taxation->country_id
//                    ];
//                }
//                if(isset($user->userAddressData)) {
//                    $user_address = $user->userAddressData;
//                    $data['user_address'] = [
//                        'user_address' => $user_address->user_address,
//                        'building_name' => $user_address->building_name,
//                        'street_no' => $user_address->street_no,
//                        'city' => $user_address->city,
//                        'postal_code' => $user_address->postal_code,
//                        'country_name' => $user_address->country,
//                        'country_id' => $user_address->country_id,
//                        'business' => $user_address->business,
//                        'business_tax_number' => $user_address->business_tax_number,
//                    ];
//                }
//                if(isset($user->userNotificationSettingData)) {
//                    $user_notification = $user->userNotificationSettingData;
//                    $data['user_notification_setting'] = [
//                        'show_notifications' => $user_notification->show_notifications,
//                        'pop_up_notifications' => $user_notification->pop_up_notifications,
//                        'preview_notifications' => $user_notification->preview_notifications,
//                        'flash_notifications' => $user_notification->flash_notifications,
//                    ];
//                }
//                if(isset($user->userContactDetailData)) {
//                    $user_contact_detail = $user->userContactDetailData;
//                    $data['user_contact_detail'] = [
//                        'telephone' => $user_contact_detail->telephone,
//                        'fax' => $user_contact_detail->fax,
//                        'whatsapp_number' => $user_contact_detail->whatsapp_number,
//                        'secondary_email' => $user_contact_detail->additional_email,
//                        'secondary_phone' => $user_contact_detail->additional_phone,
//                    ];
//                }
//                $data['date_formats_list'] = $date_formats_list;
//                $data['countries_list'] = $all_countries;
//                $response = response()->json([
//                    'user' => $user->only([
//                        'date_format_id',
//                        'email',
//                        'middle_name',
//                        'first_name',
//                        'user_name',
//                        'phone',
//                        'surname',
//                    ]),
//                    'user_detail' => $user->userDetailData,
//                    'user_detail_country' => (isset($user->userDetailData) && !is_null($user->userDetailData->Country)) ? $user->userDetailData->Country->name : null,
//                    'user_nationality' => $user->nationalityDetailData,
//                    'user_nationality_country' => (isset($user->nationalityDetailData) && !is_null($user->nationalityDetailData->Country)) ? $user->nationalityDetailData->Country->name : null,
//                    'user_taxation' => $user->taxationDetailData,
//                    'user_taxation_country' => (isset($user->taxationDetailData) && !is_null($user->taxationDetailData->Country)) ? $user->taxationDetailData->Country->name : null,
//                    'user_address' => $user->userAddressData,
//                    'user_notification_setting' => $user->userNotificationSettingData,
//                    'user_contact_detail' => $user->userContactDetailData,
//                    'date_formats_list' => $date_formats_list,
//                    'countries_list' => $all_countries,
                    // 'User Image' => $userImage
//                ]);
                return response()->json(['code' => 200, 'message' => 'User Exists and Data Fetched', 'data' => $data]);
            }
        return response()->json(['code' => 404, 'error' => 'No Data Found']);
    }

//     public function updateAccount(Request $req){
//         try{
//             $validator = Validator::make($req->all(),
//                 [
//                     'first_name' => "string|min:2",
//                     'middle_name' => 'string|nullable',
//                     'surname' => 'string',
//                     'title' => 'string|nullable',
//                     'gender' => 'string',
//                     'date_of_birth' => 'date|required',
//                     'place_of_birth' => 'string|nullable',
//                     'name_on_id'=> 'string|required',
//                     'national_id' => 'integer',
//                     'country' => 'integer',
//                     'city' => 'string',
//                     'nationality' => 'string',
//                     'issue_date' => 'required',
//                     'expiry_date' => 'required',
//                     // 'expiry_date' => ['after:'.Carbon::createFromFormat('Y-m-d', $req->issue_date)->addYears(5)],
//                     'taxation_country' => 'integer',
//                     'business_tax_number' => 'integer',
//                 ]);


//             //Show one validation error instead of one
//             if ($validator->fails())
//             {
//                 return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
//             }

//             DB::beginTransaction();
//             $user = Auth::user();
//             $data = User::find($user->id)
//                 ->update([
//                         'first_name' => $req['first_name'],
//                         'middle_name' => $req['middle_name'],
//                         'surname' => $req['surname'],
//                         'date_format_id' => $req['date_format']
//                     ]
//                 );

//             // date('d-m-Y', strtotime($user->from_date));

//             $userDetail = UserDetail::updateOrCreate(['user_id' => $user->id],
//                 [
//                     'user_id' => $user->id,
//                     'title' => $req['title'],
//                     'gender' => $req['gender'],
//                     'birth_name' => $req['birth_name'],
//                     'date_of_birth' => Carbon::parse($req['date_of_birth']),
//                     'place_of_birth' => $req['place_of_birth'],
//                     'country_id' => $req['country'],
//                     'city' => $req['city']
//                 ]
//             );

//             //implode or foreach
//             // for($i = 0; $i != NULL; $i++){
//             // }

//             $nationalityDetails = NationalityDetail::updateOrCreate(['user_id' => $user->id, 'country_id' => $req->national_country],
//                 [
//                     'user_id' => $user->id,
//                     'nationality' => $req['nationality'],
//                     'national_id' => $req['national_id'],
//                     'name_on_id' => $req['name_on_id'],
//                     'issue_date' => Carbon::parse($req['issue_date']),
//                     'expiry_date' => Carbon::parse($req['expiry_date']),
//                     'country_id' => $req['national_country'],
//                 ]
//             );

//             $taxationDetails = TaxationDetail::updateOrCreate(['user_id' => $user->id, 'country_id' => $req->taxation_country],
//                 [
//                     'user_id' => $user->id,
//                     'country_id' => $req['taxation_country'],
//                     'tax_id' => $req['business_tax_number'],
//                 ]
//             );
//             // for($i = 0; $i != NULL; $i++){
//             // }
//             // dd($taxationDetails);

//             if($user &&  $userDetail && $nationalityDetails && $taxationDetails) {
// //                $user = User::with('dateFormat', 'userDetailData','userDetailData.Country', 'nationalityDetailData', 'nationalityDetailData.Country','taxationDetailData', 'taxationDetailData.Country')->find($user->id);
// //                $response = response()->json([
// //                    'user' => $user,
// //                    'user_details' => $user->userDetailData,
// //                    'user_detail_country' => (isset($user->userDetailData) && !is_null($user->userDetailData->Country)) ? $user->userDetailData->Country->name : null,
// //                    'nationality_detail' => $user->nationalityDetailData,
// //                    'user_nationality_country' => (isset($$user->nationalityDetailData) && !is_null($user->nationalityDetailData->Country)) ? $user->nationalityDetailData->Country->name : null,
// //                    'tax_detail' => $user->taxationDetailData,
// //                    'user_taxation_country' => (isset($user->taxationDetailData) && !is_null($user->taxationDetailData->Country)) ? $user->taxationDetailData->Country->name : null,
// //                ])->getData();

//                 DB::commit();
//                 $response = $this->GET_USER_DATA($user->id);
//                 $data = [];
//                 if(isset($response['status']) && $response['status'] == true) {
//                     $data = $response['data'];
//                 }
//                 return response()->json(['code' => 200, 'message' => 'Account Updated Successfully', 'data' => $data]);
//             }
//         } catch (Exception $e) {
//             dd($e->getMessage());
//             DB::rollBack();
//             return response()->json(['code' => 500, 'error' => 'Something Went Wrong']);
//         }
//     }
}
