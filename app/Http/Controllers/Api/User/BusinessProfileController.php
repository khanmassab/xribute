<?php

namespace App\Http\Controllers\Api\User;

use App\Models\BusinessAssignedCategories;
use App\Models\BusinessCategory;
use App\Models\BusinessProduct;
use App\Models\Category;
use App\Models\Country;
use App\Models\User;
use Database\Seeders\CityList;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Models\BusinessProfile;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use function PHPUnit\Framework\isEmpty;

class BusinessProfileController extends Controller
{
    public function createBusinessProfile(Request $request){
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'business_name' => 'required|max:256',
                'business_age' => 'required',
                'business_city' => 'required',
                'business_country' => 'required',
                'time_response' => 'required',
                'pricing' => 'required',
                'bio' => 'required',
                'business_category_id' => 'required',
                'contact_number' => 'required',
                'title' => 'required',
                'first_name' => 'required',
                'last_name' => 'required',
                'taxation_country_id' => 'required',
                'business_tax_number' => 'required',
            ]);
            if ($validator->fails())
            {
                return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
            }
            // $user = User::find(Auth::user()->id);
//            $user = User::where(['account_type_id' => 2, 'id' => auth()->id()])->first(); //->findOrFail(Auth::user()->account_type_id);

            // Generate unique serial number
            $serialNumber = strtolower(Str::random(3) . rand(100, 999));
            while (BusinessProfile::where('serial_number', $serialNumber)->exists()) {
                $serialNumber = strtolower(Str::random(3)  . rand(100, 999));
            }

            $category = Category::where('id', $request->business_category_id)->first();
            if(!$category)
                return response()->json([
                    'code' => 404,
                    'error' => 'Business category not Found'
                ]);

            $business_id = $request->business_id;
            $serial_number = $request->serial_number;

            $business_name = $request->business_name;
            $business_age = $request->business_age;
            $time_response = $request->time_response;
            $pricing = $request->pricing;
            $bio = $request->bio;
            $business_category_id = $request->business_category_id;
            $business_city_id = $request->business_city;
            $business_country_id = $request->business_country;
            $contact_number = $request->contact_number;
//            $account_type_id = $request->account_type_id;
            $title = $request->title;
            $first_name = $request->first_name;
            $last_name = $request->last_name;
            $taxation_country_id = $request->taxation_country_id;
            $business_tax_number = $request->business_tax_number;

            $business_image_url = $this->save_image_to_s3($request, 'business_profile');

            $message = 'Business profile created successfully';
            if(is_null($business_id)) {
                $business_profile = BusinessProfile::create(['serial_number' => $serialNumber, 'user_id' => auth()->id(), 'business_name' => $business_name, 'business_age' => $business_age, 'time_response' => $time_response, 'pricing' => $pricing,
                    'bio' => $bio, 'business_category_id' => $business_category_id, 'business_city_id' => $business_city_id, 'business_country_id' => $business_country_id,
                    'contact_number' => $contact_number, 'title' => $title, 'first_name' => $first_name,
                    'last_name' => $last_name, 'taxation_country_id' => $taxation_country_id, 'business_tax_number' => $business_tax_number, 'image' => $business_image_url]);
                    BusinessAssignedCategories::updateOrCreate(['business_id' => $business_profile->id, 'category_id' => $category->id], ['is_active' => 1]);
            }else {
                $business_profile = BusinessProfile::updateOrCreate(['id' => $business_id], ['user_id' => auth()->id(), 'business_name' => $business_name, 'business_age' => $business_age, 'time_response' => $time_response, 'pricing' => $pricing,
                    'bio' => $bio, 'business_category_id' => $business_category_id, 'business_city_id' => $business_city_id, 'business_country_id' => $business_country_id,
                    'contact_number' => $contact_number, 'title' => $title, 'first_name' => $first_name,
                    'last_name' => $last_name, 'taxation_country_id' => $taxation_country_id, 'business_tax_number' => $business_tax_number, 'image' => $business_image_url]);
                $message = 'Business profile updated successfully';
            }


            if ($business_profile) {
                DB::commit();
                return response()->json([
                    'code' => 200,
                    'message' => $message,
                    'business_profile' => ['business_id' => $business_profile->id, 'unique_no' => $serialNumber, 'business_name' => $request->business_name, 'pricing' => $request->pricing, 'bio' => $request->bio]
                ]);
            }else{
                DB::rollBack();
                return response()->json([
                    'code' => 400,
                    'error' => 'SomeThing went wrong! tryAgain.',
                    'business_profile' => ['business_id' => $business_profile->id, 'unique_no' => $serialNumber, 'business_name' => $request->business_name, 'pricing' => $request->pricing, 'bio' => $request->bio]
                ]);
            }

        }catch (\Exception $e) {
            DB::rollBack();
            // Log::error($e->getMessage());
            return response()->json([
                'code' => 500,
                'error' => 'An error occurred while creating the business profile'. $e->getMessage(),
            ]);
        }
    }

    public function deleteBusinessProfile(Request $request){
        try {
            DB::beginTransaction();
            $validator = Validator::make($request->all(), [
                'business_id' => 'required',
                'action' => 'required',
            ]);
            if ($validator->fails())
            {
                return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
            }

            $business_id = $request->business_id;
            $action = $request->action;

                $business_profile = BusinessProfile::updateOrCreate(['id' => $business_id], ['is_active' => $action]);

                if ($action == 1)
                    $message = 'Business profile activate successfully';
                elseif ($action == 2)
                    $message = 'Business profile deActivate successfully';
                else
                    $message = 'Business profile deActivate successfully';

            if ($business_profile) {
                DB::commit();
                return response()->json([
                    'code' => 200,
                    'message' => $message,
                ]);
            }else{
                DB::rollBack();
                return response()->json([
                    'code' => 400,
                    'error' => 'SomeThing went wrong! tryAgain.',
                ]);
            }

        }catch (\Exception $e) {
            DB::rollBack();
            // Log::error($e->getMessage());
            return response()->json([
                'code' => 500,
                'error' => 'An error occurred while creating the business profile'. $e->getMessage(),
            ]);
        }
    }

    public function createBusinessCategories(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'category_id' => 'required|max:256',
            ]);
            if ($validator->fails())
            {
                return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
            }

            $category_id = $request->category_id;
            $categories = BusinessCategory::create(['user_id' => Auth::user()->id,'category_id' => $category_id]);

            return response()->json([
                'code' => 200,
                'message' => 'Business category created successfully',
            ]);

        }catch (\Exception $e) {
//             dd($e->getMessage());
            // Log::error($e->getMessage());
            return response()->json([
                'code' => 500,
                'error' => 'An error occurred while creating the business category',
            ]);
        }
    }

    public function createBusinessProducts(Request $request){
        try {

            $validator = Validator::make($request->all(), [
                'business_id' => 'required',
                'business_category_id' => 'required',
                'name' => 'required|max:256',
                'details' => 'required|max:256',
                'mrp' => 'required',
                'srp' => 'required',
//                'unit' => 'required',
                'type' => 'required',
            ]);

            if ($request->submit_type == 'add')
                $validator = Validator::make($request->all(), [
                    'image' => 'required|mimes:jpg,png,jpeg,gif,svg',
                ]);


            if ($validator->fails())
            {
                return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
            }
            $category = Category::where('id', $request->business_category_id)->first();
            if(!$category)
                return response()->json([
                    'code' => 404,
                    'error' => 'Business category not Found'
                ]);
            $product_id = $request->product_id;
            $business_id = $request->business_id;
            $category_id = $request->business_category_id;
            $name = $request->name;
            $details = $request->details;
            $mrp = $request->mrp;
            $srp = $request->srp;
            $unit = $request->unit;
            $type = $request->type;
            DB::beginTransaction();
            $product_image_url = $this->save_image_to_s3($request, '');
            $message = 'Business product created successfully';

            if(is_null($product_id)) {
                $business_products = BusinessProduct::create(['business_id' => $business_id, 'business_category_id' => $category_id, 'name' => $name, 'mrp' => $mrp, 'srp' => $srp, 'details' => $details, 'unit' => $unit, 'type' => $type, 'image' => $product_image_url]);
            }
            else {
                $business_products = BusinessProduct::updateOrCreate(['id' => $product_id], ['business_id' => $business_id, 'business_category_id' => $category_id, 'name' => $name, 'mrp' => $mrp, 'srp' => $srp, 'details' => $details, 'unit' => $unit, 'type' => $type, 'image' => $product_image_url]);
                $message = 'Business product updated successfully';
            }

            $BusinessAssignedCategories = BusinessAssignedCategories::updateOrCreate(['business_id' => $business_id, 'category_id' => $category_id], ['is_active' => 1]);

            if ($business_products && $BusinessAssignedCategories) {
                DB::commit();
                return response()->json([
                    'code' => 200,
                    'message' => $message,
                    'business_products' => $business_products
                ]);
            } else {
                DB::rollBack();
                return response()->json([
                    'code' => 500,
                    'error' => 'Something went wrong.. Try Again!',
                ]);
            }


        }catch (\Exception $e) {
            DB::rollBack();
//             dd($e->getMessage());
            // Log::error($e->getMessage());
            return response()->json([
                'code' => 500,
                'error' => 'An error occurred while creating the business products',
            ]);
        }
    }

    private function save_image_to_s3($request, $type)
    {
        $image_path_name = 'product_image';
        $image_path_id = $request->business_id;

        if ($type == 'business_profile')
            $image_path_name = 'business_image';
            $image_path_id = auth()->id();

        $product_image_url = null;

        if ($request->hasFile('image')) {
            $product_image = $request->file('image');
            $product_image_extension = $product_image->getClientOriginalExtension();
            $filename = (time()+ random_int(100, 1000));
            $product_image_filename = $filename . '.' . $product_image_extension;

            $product_image_image = Storage::disk('s3')->putFileAs($image_path_name.'/' . $image_path_id, $product_image, $product_image_filename);
            if (isset($product_image_image)) {
                $product_image_url = Storage::disk('s3')->url($product_image_image);
            }

            return $product_image_url;
        }

        return $request->image;

    }

    public function businessCategory(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'type' => 'required|max:256',
            ]);
            if ($validator->fails())
            {
                return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
            }

            $type = $request->type;
            if($type == '0')
                $category = Category::where('is_active', 1)->get();
            else
                $category = Category::where(['type' => $type, 'is_active' => 1])->get();
            $all_data = [];
            if ($category){
                foreach ($category as $index => $single_category){
                    $all_data[] = ['category_id' => $single_category->id."",'name' => $single_category->name];
                }
            }
            return response()->json([
                'code' => 200,
                'message' => 'Business Categories fetched successfully',
                'business_categories' => $all_data
            ]);

        }catch (\Exception $e) {
            // dd($e->getMessage());
            // Log::error($e->getMessage());
            return response()->json([
                'code' => 500,
                'error' => 'An error occurred while fetching the business categories',
            ]);
        }
    }

    public function getBusinessProduct(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'business_id' => 'required|max:256',
            ]);
            if ($validator->fails())
            {
                return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
            }

            $business_id = $request->business_id;
            $type = $request->type;
            
            $business_product = BusinessProduct::where(['business_id' => $business_id])->get();


            // $active_products = [1];

            // if (isset($type) && $type == 'business')
            //     $active_products = [1,2];

            // $business_product = BusinessProduct::where(['business_id' => $business_id])->whereIn('is_active' , $active_products)->get();

            $all_data = [
                'category' => [],
                'services' => [],
                'asset' => []
            ];
            if ($business_product){
                foreach ($business_product as $index => $single_product){
                    $type = 'category';

                    if ($single_product->type == 2)
                        $type = 'services';
                    elseif ($single_product->type == 3)
                        $type = 'asset';

                    $all_data[$type][] = [
                        'product_id' => $single_product->id."",
                        'business_category_id' => $single_product->business_category_id."",
                        'business_id' => $single_product->business_id."",
                        'name' => $single_product->name,
                        'mrp' => $single_product->mrp,
                        'srp' => $single_product->srp,
                        'details' => $single_product->details,
                        'unit' => $single_product->unit,
                        'type' => ($single_product->type == 1) ? "Offers" : ($single_product->type == 2 ? 'Service' : ($single_product->type == 3 ? 'Assets' : '')),
                        'image' => $single_product->image,
                        'is_active' => $single_product->is_active,
                    ];
                }
            }
            return response()->json([
                'code' => 200,
                'message' => 'Business Categories fetched successfully',
                'business_products' => $all_data
            ]);

        }catch (\Exception $e) {
//             dd($e->getMessage());
            // Log::error($e->getMessage());
            return response()->json([
                'code' => 500,
                'error' => 'An error occurred while fetching the business products',
            ]);
        }
    }

    public function getSingleProduct(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required|max:256',
            ]);
            if ($validator->fails())
            {
                return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
            }

            $product_id = $request->product_id;
            $type = $request->type;

            $active_products = [1];

            if (isset($type) && $type == 'business')
                $active_products = [1,2];

            $single_product = BusinessProduct::where(['id' => $product_id])->whereIn('is_active' , $active_products)->first();

            $all_data = [];
            if ($single_product){
                    $all_data = [
                        'product_id' => $single_product->id."",
                        'business_category_id' => $single_product->business_category_id."",
                        'business_id' => $single_product->business_id."",
                        'name' => $single_product->name."",
                        'mrp' => $single_product->mrp."",
                        'srp' => $single_product->srp."",
                        'details' => $single_product->details."",
                        'unit' => $single_product->unit."",
                        'type' => ($single_product->type == 1) ? "Offers" : ($single_product->type == 2 ? 'Service' : 'Assets'),
                        'image' => $single_product->image."",
                        'is_active' => $single_product->is_active."",
                    ];
            }
            return response()->json([
                'code' => 200,
                'message' => 'Business Product fetched successfully',
                'business_products' => $all_data
            ]);

        }catch (\Exception $e) {
//             dd($e->getMessage());
            // Log::error($e->getMessage());
            return response()->json([
                'code' => 500,
                'error' => 'An error occurred while fetching the business products',
            ]);
        }
    }

    public function businessProfilesList(Request $request){
        try {

            $type = $request->type;

            $business_profile = BusinessProfile::where(['user_id' => Auth::user()->id])->whereIn('is_active' , [1,2])->with('business_category', 'business_city', 'business_country')->get();

            if ($type == 'all')
                $business_profile = BusinessProfile::where('is_active' , 1)->with('business_category', 'business_city', 'business_country')->get();


            $all_data = [];
            if ($business_profile){
                foreach ($business_profile as $index => $single_profile){
                    $all_data[] = [
                        'business_id' => $single_profile->id."",
                        'serial_number' => $single_profile->serial_number."",
                        'business_name' => $single_profile->business_name."",
                        'image' => $single_profile->image."",
                        'business_category' => !is_null($single_profile->business_category) ? $single_profile->business_category->name : ''."",
                        'years_in_industory' => $single_profile->business_age."",
                        'responsiveness' => $single_profile->time_response."",
                        'is_active' => $single_profile->is_active."",
                        'created_at' => $single_profile->created_at."",
                    ];
                }
            }
            return response()->json([
                'code' => 200,
                'message' => 'Business profiles list fetched successfully',
                'business_profiles' => $all_data
            ]);

        }catch (\Exception $e) {
            // dd($e->getMessage());
            // Log::error($e->getMessage());
            return response()->json([
                'code' => 500,
                'error' => 'An error occurred while fetching the business profiles',
            ]);
        }
    }

    public function businessProfile(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'business_id' => 'required|max:256',
            ]);
            if ($validator->fails())
            {
                return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
            }

            $profile_id = $request->business_id;
            $single_profile = BusinessProfile::where(['id' => $profile_id])->whereIn('is_active' , [1,2])->with('business_category', 'business_city', 'business_country', 'BusinessAssignedCategories', 'texationCountry')->first();

            $all_data = [];
            $category_list = [];
            if ($single_profile){
                if ($single_profile->BusinessAssignedCategories){
                    foreach ($single_profile->BusinessAssignedCategories as $business_assigned_categories){
                        if ($business_assigned_categories->category){
                            $category_detail = $business_assigned_categories->category;
                            $category_type = (($category_detail->type == '2') ? 'service' : (($category_detail->type == '3') ? 'assets' : 'offers'));
                                $category_list[$category_type][] = [
                                    'name' => $category_detail->name
                                ];
                        }
                    }
                }

//                foreach ($business_profile as $index => $single_profile){
                    $all_data = [
                        'business_id' => $single_profile->id."",
                        'serial_number' => $single_profile->serial_number."",
                        'business_name' => $single_profile->business_name."",
                        'business_category_id' => $single_profile->business_category_id,
                        'business_country_id' => $single_profile->business_country_id,
                        'business_city_id' => $single_profile->business_city_id,
                        'account_type_id' => $single_profile->account_type_id,
                        'taxation_country_id' => $single_profile->taxation_country_id,
                        'taxation_country_name' => !is_null($single_profile->texationCountry) ? $single_profile->texationCountry->name : ''."",
                        'business_category' => !is_null($single_profile->business_category) ? $single_profile->business_category->name : ''."",
                        'business_city' => !is_null($single_profile->business_city) ? $single_profile->business_city->name : ''."",
                        'business_country' => !is_null($single_profile->business_country) ? $single_profile->business_country->name : ''."",
                        'pricing' => $single_profile->pricing."",
                        'years_in_industory' => $single_profile->business_age."",
                        'responsiveness' => $single_profile->time_response."",
                        'about' => $single_profile->bio."",
                        'title' => $single_profile->title."",
                        'first_name' => $single_profile->first_name."",
                        'last_name' => $single_profile->last_name."",
                        'contact_number' => $single_profile->contact_number."",
                        'image' => $single_profile->image."",
                        'business_tax_number' => $single_profile->business_tax_number."",
                        'is_active' => $single_profile->is_active."",
                        'category_list' => $category_list
                    ];
                return response()->json([
                    'code' => 200,
                    'message' => 'Business profiles fetched successfully',
                    'business_profiles' => $all_data
                ]);
                }
            else
                return response()->json([
                    'code' => 404,
                    'error' => 'No Business profile Found.',
                ]);
//            }

        }catch (\Exception $e) {
            // dd($e->getMessage());
            // Log::error($e->getMessage());
            return response()->json([
                'code' => 500,
                'error' => 'An error occurred while fetching the business profiles',
            ]);
        }
    }

    public function citiesList(Request $request){
        try {

            $cities_list = \App\Models\cityList::where('is_active' , 1)->get();

            $all_data = [];
            if ($cities_list){
                foreach ($cities_list as $index => $single_city){
                    $all_data[] = [
                       'city_id' => $single_city->id."",
                       'name' => $single_city->name."",
                    ];
                }
            }
            return response()->json([
                'code' => 200,
                'message' => 'Cities list fetched successfully',
                'cities_list' => $all_data
            ]);

        }catch (\Exception $e) {
            // dd($e->getMessage());
            // Log::error($e->getMessage());
            return response()->json([
                'code' => 500,
                'error' => 'An error occurred while fetching the cities list',
            ]);
        }
    }

    public function countriesList(Request $request){
        try {

            $countries_list = Country::get();

            $all_data = [];
            if ($countries_list){
                foreach ($countries_list as $index => $single_country){
                    $all_data[] = [
                       'country_id' => $single_country->id."",
                       'name' => $single_country->name."",
                    ];
                }
            }

            return response()->json([
                'code' => 200,
                'message' => 'Country list fetched successfully',
                'countries_list' => $all_data
            ]);

        }catch (\Exception $e) {
//             dd($e->getMessage());
            // Log::error($e->getMessage());
            return response()->json([
                'code' => 500,
                'error' => 'An error occurred while fetching the countries list',
            ]);
        }
    }

    public function ActiveDeActiveProduct(Request $request){
        try {
            $validator = Validator::make($request->all(), [
                'product_id' => 'required',
                'type' => 'required',
            ]);
            if ($validator->fails())
            {
                return response()->json(['code' => 422, 'error'=>$validator->errors()->first()]);
            }

            $id = $request->product_id;
            $type = $request->type;
            $business_product = BusinessProduct::where(['id' => $id])->update(['is_active' => $type]);

            if ($business_product) {
                return response()->json([
                    'code' => 200,
                    'message' => 'Product updated  successfully',
                ]);
            }

        }catch (\Exception $e) {
//             dd($e->getMessage());
            // Log::error($e->getMessage());
            return response()->json([
                'code' => 500,
                'error' => 'An error occurred. Try Again',
            ]);
        }
    }

    public function getAllProducts(){
        try {

            $all_products = BusinessProduct::where('is_active', 1)->get();

            $all_data = [];
            if (count($all_products)) {
                foreach ($all_products as $single_product) {
                    $all_data[] = [
                        'product_id' => $single_product->id . "",
                        'business_category_id' => $single_product->business_category_id . "",
                        'business_id' => $single_product->business_id . "",
                        'name' => $single_product->name . "",
                        'mrp' => $single_product->mrp . "",
                        'srp' => $single_product->srp . "",
                        'details' => $single_product->details . "",
                        'unit' => $single_product->unit . "",
                        'type' => ($single_product->type == 1) ? "Offers" : ($single_product->type == 2 ? 'Service' : ($single_product->type == 3 ? 'Assets' : '')),
                        'image' => $single_product->image . "",
                        'is_active' => $single_product->is_active . "",
                    ];
                }
                return response()->json([
                    'code' => 200,
                    'message' => 'All Products fetched successfully',
                    'business_products' => $all_data
                ]);
            }
            return response()->json([
                'code' => 404,
                'error' => 'No Products Found',
            ]);

        }catch (\Exception $e) {
            // Log::error($e->getMessage());
            return response()->json([
                'code' => 500,
                'error' => 'An error occurred while fetching the all products',
            ]);
        }
    }
}
