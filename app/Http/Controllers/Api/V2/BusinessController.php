<?php

namespace App\Http\Controllers\Api\V2;

use Carbon\Carbon;

use App\Models\V2\Share;
use App\Models\V2\Business;
use App\Models\V2\Platform;
use Illuminate\Http\Request;
use App\Models\V2\Management;

use App\Models\V2\Shareholder;
use App\Models\V2\BusinessStake;
use App\Models\V2\BusinessBranch;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Models\V2\AddressAndContact;
use Illuminate\Support\Facades\Auth;
use App\Models\V2\AddressesAndContacts;
use App\Models\V2\ManagementAndDirectors;
use Illuminate\Support\Facades\Validator;

class BusinessController extends Controller
{
    public function getAllBusinesses()
    {
        $userId = auth()->id(); // Get the current authenticated user ID

        $businesses = Business::where('user_id', $userId)->get(); // Get all the businesses created by the user

        return response()->json(['code' => 200, 'message' => 'Businesses retrieved successfully', 'data' => $businesses]);
    }

    public function createBusiness(Request $request)
    {

        try {

        $id = $request->id;

            if(!$id){
                $validator = Validator::make($request->all(), [
                    'business_logo' => 'required|image|max:2048',
                    'business_country' => 'required|string|max:255',
                    'business_name' => 'required|string|max:255',
                    'business_registration_proof' => 'required|file|max:2048|mimes:pdf,jpg,png',
                    'business_legal_type' => 'required|string|max:255',
                    'business_registration_no' => 'required|string|max:255',
                    'business_registration_date' => 'required',
                    'business_registration_city' => 'required|string|max:255',
                    'business_registered_address' => 'required|string|max:255',
                    'business_tax_authority' => 'required|string|max:255',
                    'business_vat_no' => 'required|string|max:255',
                ]);

                if ($validator->fails()) {
                    return response()->json(['code' => 422, 'error' => $validator->errors()->first()]);
                }
                // Store the business logo file
                $businessLogo = $request->file('business_logo');
                $businessLogoPath = $businessLogo->store('public/business_logos');
                $businessLogoUrl = asset(str_replace('public/', 'storage/', $businessLogoPath));


                // Store the business registration file
                $businessRegistration = $request->file('business_registration_proof');
                $businessRegistrationPath = $businessRegistration->store('public/business_registrations');
                $businessRegistrationUrl = asset(str_replace('public/', 'storage/', $businessRegistrationPath));
            }

            if ($id) {
                // Update existing business record
                $business = Business::find($id);

                if (!$business) {
                    return response()->json(['code' => 404, 'message' => 'Business not found']);
                }

                $businessLogoUrl = $business->business_logo;
                $businessRegistrationUrl = $business->business_registration_proof;
            } else {
                // Create a new business record
                $business = new Business;
                $business->user_id = auth()->id();
            }

            $business->business_logo = $businessLogoUrl;
            $business->business_registration_proof = $businessRegistrationUrl;
            $business->business_country = $request->input('business_country', $business->business_country);
            $business->business_name = $request->input('business_name', $business->business_name);
            $business->business_legal_type = $request->input('business_legal_type', $business->business_legal_type);
            $business->business_registration_no = $request->input('business_registration_no', $business->business_registration_no);
            $business->business_registration_date = Carbon::parse($request->input('business_registration_date', $business->business_registration_date));
            $business->business_registration_city = $request->input('business_registration_city', $business->business_registration_city);
            $business->business_registered_address = $request->input('business_registered_address', $business->business_registered_address);
            $business->business_tax_authority = $request->input('business_tax_authority',  $business->business_tax_authority);
            $business->business_vat_no = $request->input('business_vat_no', $business->business_vat_no);
            // dd($business);
            $business->save();


            if ($business) {
                if ($id) {
                    return response()->json(['code' => 200, 'message' => 'Business updated successfully', 'data' => $business]);
                }
                return response()->json(['code' => 200, 'message' => 'Business created successfully', 'data' => $business]);
            }

            return response()->json(['code' => 500, 'message' => 'Something went wrong']);
        } catch (\Exception $e) {
            dd($e->getMessage());
            return response()->json(['code' => 500, 'message' => 'Something went wrong']);
        }
    }

    public function getAllBusinessShares(){

        $user = auth()->user();

        // Get all the business shares of the user
        $businessShares = $user->businesses;

        // Return the response
        return response()->json(['code' => 200, 'message' => 'Business shares retrieved successfully', 'data' => $businessShares]);
    }


    public function businessShares(Request $request){

        $id = $request->id;

        if(!$id){
            // Define validation rules
            $rules = [
                'proof_document' => 'required|file|max:10240',
                'picture' => 'required|file|max:10240',
                'name' => 'required|string',
                'share_percent' => 'required|numeric|min:0',
                'no_of_shares' => 'required|numeric|min:1',
                'share_value' => 'required|numeric',
                'registered_capital' => 'nullable|numeric|min:1',
                'email' => 'required|email',
            ];

            // Validate request data
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'message' => $validator->errors()->first(),
                ]);
            }
        }


        try {
            DB::beginTransaction();

            if($request->business_id){
                $business_id = auth()->user()->business()->find($request->business_id)->id;
            }
            if(!$request->business_id){

                $business_id = auth()->user()->business()->first()->id;
            }

            if ($id) {
                // Update existing business record
                $shareholder = Shareholder::find($id);

                if (!$shareholder) {
                    return response()->json(['code' => 404, 'message' => 'Business not found']);
                }

                $path = $shareholder->proof_document;
                $shareholderPictureUrl = $shareholder->picture;

                if($request->hasFile('proof_document')){
                    $path = $request->file('proof_document')->store('public/proof_document');
                    $path = asset(str_replace('public/', 'storage/', $path));

                    // dd($path);
                }

                if($request->hasFile('picture')){
                    $shareholderPicture = $request->file('picture');
                    $shareholderPicturePath = $shareholderPicture->store('public/shareholders/picture');
                    $shareholderPictureUrl = asset(str_replace('public/', 'storage/', $shareholderPicturePath));
                }
            } else {
                // Create a new business record
                $shareholder = new Shareholder;
                // $shareholder->business_id = auth()->id();
                $shareholder->business_id =  $business_id;

                if($request->hasFile('proof_document')){
                    $path = $request->file('proof_document')->store('public/proof_document');
                    $path = asset(str_replace('public/', 'storage/', $path));

                    // dd($path);
                }

                if($request->hasFile('picture')){
                    $shareholderPicture = $request->file('picture');
                    $shareholderPicturePath = $shareholderPicture->store('public/shareholders/picture');
                    $shareholderPictureUrl = asset(str_replace('public/', 'storage/', $shareholderPicturePath));
                }

            }



            $shareholder->proof_document = $path;
            $shareholder->picture = $shareholderPictureUrl;
            $shareholder->name = $request->input('name', $shareholder->name);
            $shareholder->share_percent = $request->input('share_percent', $shareholder->share_percent);
            $shareholder->no_of_shares = $request->input('no_of_shares', $shareholder->no_of_shares);
            $shareholder->share_value = $request->input('share_value', $shareholder->share_value );
            $shareholder->registered_capital = $request->input('registered_capital', $shareholder->registered_capital);
            $shareholder->email = $request->input('email', $shareholder->email);
            $shareholder->save();

            DB::commit();

            if ($shareholder) {
                if ($id) {
                    return response()->json(['code' => 200, 'message' => 'Shareholders updated successfully', 'data' => $shareholder]);
                }
                return response()->json(['code' => 200, 'message' => 'Shareholders created successfully', 'data' => $shareholder]);
            }

            return response()->json(['code'=> 500, 'message' => 'Something went wrong']);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['code'=> 500, 'message' => 'Something went wrong']);
        }
    }

    public function businessManagement(Request $request){


        $id = $request->id;
        if(!$id){
            // Define validation rules
            $rules = [
                'proof_document' => 'required|file|max:10240',
                'picture' => 'required|file|max:10240',
                'name' => 'required|string',
                'share_percent' => 'required|numeric|min:0',
                'no_of_shares' => 'required|numeric|min:1',
                'share_value' => 'required|numeric',
                // 'position' => 'required|string',
                'email' => 'required|email',
            ];

            // Validate request data
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'message' => $validator->errors()->first(),
                ]);
            }


        }

        try {
            DB::beginTransaction();

            if($request->business_id){
                $business_id = auth()->user()->business()->find($request->business_id)->id;
            }
            if(!$request->business_id){
                $business_id = auth()->user()->business()->first()->id;
            }

            if ($id) {
                // Update existing business record
                $manager = Management::find($id);
                if (!$manager) {
                    return response()->json(['code' => 404, 'message' => 'Business not found']);
                }

                $managerPictureUrl = $manager->picture;
                $path = $manager->proof_document;

                if($request->hasFile('picture')){
                    $managerPicture = $request->file('picture');
                    $managerPicturePath = $managerPicture->store('public/managers/picture');
                    $managerPictureUrl = asset(str_replace('public/', 'storage/', $managerPicturePath));
                }

                if($request->hasFile('proof_document')){
                    $path = $request->file('proof_document')->store('public/proof_document');
                    $path = asset(str_replace('public/', 'storage/', $path));
                }


            } else {
                // Create a new business record
                $manager = new Management;
                $manager->business_id =  $business_id;
                // Store the shareholder image file
                if($request->hasFile('picture')){
                    $managerPicture = $request->file('picture');
                    $managerPicturePath = $managerPicture->store('public/managers/picture');
                    $managerPictureUrl = asset(str_replace('public/', 'storage/', $managerPicturePath));
                }

                if($request->hasFile('proof_document')){
                    $path = $request->file('proof_document')->store('public/proof_document');
                    $path = asset(str_replace('public/', 'storage/', $path));
                }


                // $manager->business_id = auth()->id();
            }


            $manager->picture = $managerPictureUrl;
            $manager->proof_document =  $path;
            $manager->name = $request->input('name', $manager->name);
            $manager->share_percent = $request->input('share_percent', $manager->share_percent);
            $manager->no_of_shares = $request->input('no_of_shares', $manager->no_of_shares);
            $manager->share_value = $request->input('share_value', $manager->share_value);
            $manager->position = $request->input('registered_capital', $manager->position);
            $manager->email = $request->input('email', $manager->email);
            $manager->save();

            DB::commit();

            if ($manager) {
                if ($id) {
                    return response()->json(['code' => 200, 'message' => 'Management updated successfully', 'data' => $manager]);
                }
                return response()->json(['code' => 200, 'message' => 'Management created successfully', 'data' => $manager]);
            }

            return response()->json(['code'=> 500, 'message' => 'Something went wrong']);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['code'=> 500, 'message' => 'Something went wrong']);
        }
    }

    public function businessAddressContact(Request $request) {

        $id = $request->id;
        if(!$id){
            $rules = [
                'address_label' => 'required|string',
                'address' => 'required|string',
                'building_no' => 'required|string|max:20',
                'town_city' => 'required|string|max:20',
                'postal_code' => 'required|integer',
                'country' => 'required|string|max:10',
                'website' => 'required|string|max:20',
                // 'contact_label' => 'required|string|max:20',
                // 'mobile' => 'required|string|max:15',
                // 'telefone' => 'required|string|max:15',
                // 'fax' => 'nullable|string|max:12',
                'email' => 'required|email',
            ];

            // Validate request data
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'message' => $validator->errors()->first(),
                ]);
            }
        }

        try {
            DB::beginTransaction();

            if($request->business_id){
                $business_id = auth()->user()->business()->find($request->business_id)->id;
            }
            if(!$request->business_id){
                $business_id = auth()->user()->business()->first()->id;
            }
            if ($id) {
                // Update existing business address record
                $businessAddress = AddressAndContact::find($id);
                if (!$businessAddress) {
                    return response()->json(['code' => 404, 'message' => 'Business address not found']);
                }
            } else {
                // Create a new business address record
                $businessAddress = new AddressAndContact;
                $businessAddress->business_id = $business_id;
            }

            $businessAddress->address_label = $request->input('address_label', $businessAddress->address_label);
            $businessAddress->address = $request->input('address', $businessAddress->address);
            $businessAddress->building_no = $request->input('building_no', $businessAddress->building_no);
            $businessAddress->town_city = $request->input('town_city', $businessAddress->town_city);
            $businessAddress->postal_code = $request->input('postal_code', $businessAddress->postal_code);
            $businessAddress->country = $request->input('country', $businessAddress->country);
            $businessAddress->website = $request->input('website', $businessAddress->website);
            $businessAddress->contact_label = $request->input('contact_label', $businessAddress->contact_label);
            $businessAddress->mobile = $request->input('mobile', $businessAddress->mobile);
            $businessAddress->telefone = $request->input('telefone', $businessAddress->telefone);
            $businessAddress->fax = $request->input('fax', $businessAddress->fax);
            $businessAddress->email = $request->input('email',$businessAddress->email);
            $businessAddress->save();

            DB::commit();

            if ($businessAddress) {
                if ($id) {
                    return response()->json(['code' => 200, 'message' => 'Business address updated successfully', 'data' => $businessAddress]);
                }
                return response()->json(['code' => 200, 'message' => 'Business address created successfully', 'data' => $businessAddress]);
            }

            return response()->json(['code'=> 500, 'message' => 'Something went wrong']);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['code'=> 500, 'message' => 'Something went wrong']);
        }
    }


    public function businessPlatform(Request $request){

        $id = $request->id;

        if(!$id){
            // Define validation rules
            $rules = [
                'name' => 'required|string',
                // 'followers' => 'required|string',
                'icon' => 'required|file|max:10240',
                'url' => 'required|string',
                // 'business_id' => 'required|numeric',
            ];

            // Validate request data
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'message' => $validator->errors()->first(),
                ]);
            }
        }

        try {
            DB::beginTransaction();

            if($request->business_id){
                $business_id = auth()->user()->business()->find($request->business_id)->id;
            }
            if(!$request->business_id){
                $business_id = auth()->user()->business()->first()->id;
            }


            if ($id) {
                // Update existing platform record
                $platform = Platform::find($id);
                $icon = $request->file('icon');
                $iconPath = $icon->store('public/platforms/icons');
                $iconUrl = asset(str_replace('public/', 'storage/', $iconPath));
                $iconUrl = $platform->icon;
                if (!$platform) {
                    return response()->json(['code' => 404, 'message' => 'Platform not found']);
                }
            } else {
                // Create a new platform record
                $platform = new Platform;
                $platform->business_id = $business_id;
            }

            // Store the platform icon file


            $platform->name = $request->input('name', $platform->name);
            $platform->followers = $request->input('followers', $platform->followers);
            $platform->icon = $iconUrl;
            $platform->url = $request->input('url', $platform->url);
            $platform->save();

            DB::commit();

            if ($platform) {
                if ($id) {
                    return response()->json(['code' => 200, 'message' => 'Platform updated successfully', 'data' => $platform]);
                }
                return response()->json(['code' => 200, 'message' => 'Platform created successfully', 'data' => $platform]);
            }

            return response()->json(['code'=> 500, 'message' => 'Something went wrong']);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['code'=> 500, 'message' => 'Something went wrong']);
        }
    }

    public function businessBranch(Request $request) {

        $id = $request->id;

        if(!$id){
            $rules = [
                'branch_address' => 'required|string',
                'address' => 'required|string',
                'location' => 'required|string',
                'building_no' => 'required|string|max:20',
                'town_city' => 'required|string|max:20',
                'postal_code' => 'required',
                'country' => 'required|string|max:10',
                'website' => 'required|string|max:20',
                'branch_contact' => 'required',
                'mobile' => 'required',
                'telefone' => 'required',
                'fax' => 'nullable',
                'email' => 'required|email',
            ];

            // Validate request data
            $validator = Validator::make($request->all(), $rules);

            if ($validator->fails()) {
                return response()->json([
                    'code' => 422,
                    'message' => $validator->errors()->first(),
                ]);
            }
        }

        try {
            DB::beginTransaction();

            if($request->business_id){
                $business_id = auth()->user()->business()->find($request->business_id)->id;
            }
            if(!$request->business_id){
                $business_id = auth()->user()->business()->first()->id;
            }

            if ($id) {
                // Update existing business address record
                $businessBranch = BusinessBranch::find($id);
                if (!$businessBranch) {
                    return response()->json(['code' => 404, 'message' => 'Business address not found']);
                }
            } else {
                // Create a new business address record
                $businessBranch = new BusinessBranch;
                $businessBranch->business_id =  $business_id;
            }

            $businessBranch->branch_address = $request->input('branch_address', $businessBranch->branch_address);
            $businessBranch->address = $request->input('address', $businessBranch->address);
            $businessBranch->location = $request->input('location', $businessBranch->location);
            $businessBranch->building_no = $request->input('building_no', $businessBranch->building_no);
            $businessBranch->town_city = $request->input('town_city', $businessBranch->town_city);
            $businessBranch->postal_code = $request->input('postal_code', $businessBranch->postal_code);
            $businessBranch->country = $request->input('country', $businessBranch->country);
            $businessBranch->website = $request->input('website', $businessBranch->website);
            $businessBranch->branch_contact = $request->input('branch_contact', $businessBranch->branch_contact);
            $businessBranch->mobile = $request->input('mobile', $businessBranch->mobile);
            $businessBranch->telefone = $request->input('telefone', $businessBranch->telefone);
            $businessBranch->fax = $request->input('fax', $businessBranch->fax);
            $businessBranch->email = $request->input('email', $businessBranch->email) ;
            $businessBranch->save();

            DB::commit();

            if ($businessBranch) {
                if ($id) {
                    return response()->json(['code' => 200, 'message' => 'Business address updated successfully', 'data' => $businessBranch]);
                }
                return response()->json(['code' => 200, 'message' => 'Business address created successfully', 'data' => $businessBranch]);
            }

            return response()->json(['code'=> 500, 'message' => 'Something went wrong']);

        } catch (Exception $e) {
            DB::rollBack();
            return response()->json(['code'=> 500, 'message' => 'Something went wrong']);
        }
    }

    public function getBusinessSummary() {
        $businesses = auth()->user()->business;

        if (!$businesses) {
            return response()->json(['code' => 404, 'message' => 'No Business found on this account']);
        }


        return response()->json(['code' => 200, 'message' => 'success', 'data' => $businesses]);
    }

    public function getBusinessDetail($businessId) {
        $businesses = auth()->user()->business()->with('platforms', 'management', 'addressAndContact', 'shareholder', 'businessBranches')->findOrFail($businessId);

        if (!$businesses) {
            return response()->json(['code' => 404, 'message' => 'No Business found on this account']);
        }

        // foreach ($businesses as $business) {
        //     $business->platforms;
        //     $business->management;
        //     $business->addressAndContact;
        //     $business->shareholder;
        //     $business->businessBranches;
        // }

        return response()->json(['code' => 200, 'message' => 'success', 'data' => $businesses]);
    }

    public function deleteBusiness($businessId) {
        $business = auth()->user()->business()->find($businessId);

        if (!$business) {
            return response()->json(['code' => 404, 'message' => 'Business not found']);
        }

        // Delete the business and all its relationships
        $business->platforms()->delete();
        $business->management()->delete();
        $business->addressAndContact()->delete();
        $business->shareholder()->delete();
        $business->businessBranches()->delete();
        $business->delete();

        return response()->json(['code' => 200, 'message' => 'Business deleted successfully']);
    }

    public function deleteShareholder($id) {
        $shareholder = Shareholder::find($id);

        if (!$shareholder || !$shareholder->business->user_id === auth()->id()) {
            return response()->json(['code' => 404, 'message' => 'Shareholder not found']);
        }

        $shareholder->delete();

        return response()->json(['code' => 200, 'message' => 'Shareholder deleted successfully']);
    }

    public function deleteAddressAndContact(Request $request, $id)
    {
        $addressAndContact = AddressAndContact::find($id);

        if (!$addressAndContact || !$addressAndContact->business->user_id === auth()->id()) {
            return response()->json(['code' => 404, 'message' => 'Address and contact not found']);
        }

        $addressAndContact->delete();

        return response()->json(['code' => 200, 'message' => 'Address and contact deleted successfully']);
    }

    public function deleteManagement($id)
    {
        $management = Management::find($id);

        if (!$management || !$management->business->user_id === auth()->id()) {            return response()->json(['code' => 404, 'message' => 'Management not found']);
            return response()->json(['code' => 404, 'message' => 'Management not found']);
        }


        $management->delete();

        return response()->json(['code' => 200, 'message' => 'Management deleted successfully']);
    }

    public function deletePlatform($id)
    {
        $platform = Platform::find($id);


        if (!$platform || !$platform->business->user_id === auth()->id()) {
            return response()->json(['code' => 404, 'message' => 'Platform not found']);
        }

        // Delete the platform
        $platform->delete();

        return response()->json(['code' => 200, 'message' => 'Platform deleted successfully']);
    }


    public function deleteBranch($id)
    {
        $branch = BusinessBranch::find($id);

        if (!$branch || !$branch->business->user_id === auth()->id()) {
            return response()->json(['code' => 404, 'message' => 'Branch not found']);
        }

        // Delete the branch
        $branch->delete();

        return response()->json(['code' => 200, 'message' => 'Branch deleted successfully']);
    }

    public function toggleBusinessStatus($id){
        $business = auth()->user()->business()->find($id);

        if (!$business) {
            return response()->json(['code' => 404, 'message' => 'Business not found']);
        }

        $business->is_active = !$business->is_active;
        $business->save();

        return response()->json(['code' => 200, 'message' => 'Success', 'data' => $business->is_active]);
    }
}
