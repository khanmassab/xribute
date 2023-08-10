<?php


namespace App\Traits;


use App\Models\Country;
use App\Models\DateFormat;
use App\Models\User;
use App\Models\AccountType;
use App\Models\UserDetail;
use App\Models\UserGender;
use App\Models\UserLanguage;
use App\Models\UserPrefix;
use Illuminate\Support\Facades\Auth;
use DateTime;


trait UserTrait
{
    public function GET_USER_DATA($userId){
                //getting user data if it exists
                $user = User::with('dateFormat', 'userDetailData','userDetailData.Country', 'nationalityDetailData', 'nationalityDetailData.Country','taxationDetailData', 'taxationDetailData.Country' ,'userAddressData', 'userNotificationSettingData', 'userContactDetailData', 'accountTypes')->find($userId); //
                //$userDetail = UserDetail::where('user_id', $userId)->with('Country')->first();
                //$nationalityDetails = NationalityDetail::where('user_id', $userId)->with('Country')->first();
                //$taxationDetails = TaxationDetail::where('user_id', $userId)->with('Country')->first();
                //$userAddress = UserAddress::where('user_id', $userId)->first();
                //$userNotificationSettings = UserNotificationSetting::where('user_id', $userId)->first();
                //$userContactDetail = UserContactDetail::where('user_id', $userId)->first();
                // $userImage = UserImage::where('user_id', $userId)->get();
                $all_countries = Country::select(['id','iso','name','nice_name','iso3','numcode','phonecode'])->get();
//                $account_types = AccountType::select(['account_type_id', 'account_type_name'])->get();
                $date_formats_list = DateFormat::select(['id', 'date_format'])->get();
            // return response()->json(['code' => 400, 'error' => 'User Not Found']);
            if($user){
                $data = ['user' => null, 'user_detail' => null, 'user_nationality' => null, 'user_taxation' => null, 'user_address' => null,'user_notification_setting' => null, 'user_contact_detail' => null, 'date_formats_list' => null, 'countries_list' => null, 'account_type' => null]; //
                $data['user'] = ['first_name' => $user->first_name, 'middle_name' => $user->middle_name, 'surname' => $user->surname, 'user_name' => $user->user_name, 'phone' => $user->phone, 'email' => $user->email, 'date_format_id' => $user->date_format_id, 'account_type' => (isset($user->accountTypes)) ? $user->accountTypes->account_type_name : 'N/A',]; //
                $date_format = 'Y-m-d';
                // $date = new DateTime('0000-00-00');
                // $formatted_date = $date->format($date_format);
                if(isset($user->dateFormat)){
                    $date_format = $user->dateFormat->date_format;
                }

                        $userDetails = UserDetail::with(['userPrefix', 'userLanguage', 'userGender'])->get();
                        $userLanguage = UserDetail::with(['userLanguage'])->get()->pluck('userLanguage')->value('id');
                        $userPrefix = UserDetail::with(['userPrefix'])->get()->pluck('userPrefix')->value('id');
                        $userGender = UserDetail::with(['userGender'])->get()->pluck('userGender')->value('id');
                        $userBirth = UserDetail::with(['userBirth'])->get()->pluck('userBirth')->value('id');
                        $all_genders =  UserGender::all();
                        $all_language =  UserLanguage::all();
                        $all_prefixes =  UserPrefix::all();
                                
                if(isset($user->userDetailData)) {
                    $user_detail = $user->userDetailData;
                    $data['user_detail'] = [
                        // 'title' => $user_detail->title,
                        // 'gender' => $user_detail->gender,
                        'birth_name' => $user_detail->birth_name,
                        'date_of_birth' => date($date_format, strtotime($user_detail->date_of_birth)),
                        // 'place_of_birth' => $user_detail->place_of_birth,
                        'city' => $user_detail->city,
                        'profile_image' => $user_detail->profile_image,
                        'cover_image' => $user_detail->cover_image,
                        'date_format' => $user->dateFormat->date_format,
                        // 'date_formats' =>  $formatted_date,
                        'country_name' => (!is_null($user_detail->Country)) ? $user_detail->Country->nice_name : null,
                        'country_id' => $user_detail->country_id,
                        'language_id'  => $userLanguage,
                        'prefix_id'  => $userPrefix,
                        'gender_id'  => $userGender,
                        'place_of_birth_id'  => $userBirth,
                        'all_genders'  => $all_genders,
                        'all_language'  => $all_language,
                        'all_prefixes'  => $all_prefixes
                    ];
                }
                if(isset($user->nationalityDetailData)) {
                    $user_nationality = $user->nationalityDetailData;
                    $data['user_nationality'] = [
                        'nationality' => $user_nationality->nationality,
                        'name_on_id' => $user_nationality->name_on_id,
                        'national_id' => $user_nationality->national_id,
                        'issue_date' => date($date_format, strtotime($user_nationality->issue_date)) ,
                        'expiry_date' =>date($date_format, strtotime($user_nationality->expiry_date)),
                        'country_name' => (!is_null($user_nationality->Country)) ? $user_nationality->Country->nice_name : null,
                        'country_id' => $user_nationality->country_id
                    ];
                }
                if(isset($user->taxationDetailData)) {
                    $user_taxation = $user->taxationDetailData;
                    $data['user_taxation'] = [
                        'tax_id' => $user_taxation->tax_id,
                        'country_name' => (!is_null($user->taxationDetailData->Country)) ? $user->taxationDetailData->Country->nice_name : null,
                        'country_id' => $user_taxation->country_id
                    ];
                }
                if(isset($user->userAddressData)) {
                    $user_address = $user->userAddressData;
                    $data['user_address'] = [
                        'user_address' => $user_address->user_address,
                        'building_name' => $user_address->building_name,
                        'street_no' => $user_address->street_no,
                        'city' => $user_address->city,
                        'postal_code' => $user_address->postal_code,
                        'country_name' => (!is_null($user_address->Country)) ? $user_address->Country->nice_name : null,
                        'country_id' => $user_address->country_id,
                        'business' => $user_address->business,
                        'business_tax_number' => $user_address->business_tax_number,
                    ];
                }
                if(isset($user->userNotificationSettingData)) {
                    $user_notification = $user->userNotificationSettingData;
                    $data['user_notification_setting'] = [
                        'show_notifications' => $user_notification->show_notifications,
                        'pop_up_notifications' => $user_notification->pop_up_notifications,
                        'preview_notifications' => $user_notification->preview_notifications,
                        'flash_notifications' => $user_notification->flash_notifications,
                    ];
                }
                if(isset($user->userContactDetailData)) {
                    $user_contact_detail = $user->userContactDetailData;
                    $data['user_contact_detail'] = [
                        'telephone' => $user_contact_detail->telephone,
                        'fax' => $user_contact_detail->fax,
                        'whatsapp_number' => $user_contact_detail->whatsapp_number,
                        'secondary_email' => $user_contact_detail->additional_email,
                        'secondary_phone' => $user_contact_detail->additional_phone,
                    ];
                }
                $data['date_formats_list'] = $date_formats_list;
                $data['countries_list'] = $all_countries;
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
                return ['status' => true, 'data' => $data];
            }

            return ['status' => false];

    }

}