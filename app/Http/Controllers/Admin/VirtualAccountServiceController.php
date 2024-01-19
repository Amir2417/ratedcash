<?php

namespace App\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\VirtualAccountService;

class VirtualAccountServiceController extends Controller
{
    /**
     * Method for view the virtual account service page
     */
    public function index(){
        $page_title     = "Setup Virtual Account Service";
        $service        = VirtualAccountService::first();
       
        return view('admin.sections.virtual-account-service.index',compact(
            'page_title',
            'service'
        ));
    }
    /**
     * Method for update the virtual account service 
     * @param \Illuminate\Http\Request $request
     */
    public function update(Request $request){
        $validator = Validator::make($request->all(), [
            'name'              => 'required',
            'username'          => 'required',
            'password'          => 'required',
            'clientId'          => 'required',
            'clientSecret'      => 'required',
            'publickey'         => 'required',
            'privatekey'        => 'required',
            'apiKey'            => 'required',
            'secretKey'         => 'required|string',
            'service_url'       => 'required',
            'card_details'      => 'required',
            'image'             => "nullable|mimes:png,jpg,jpeg,webp,svg",
            
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $data = array_filter($request->except('_token','_method','card_details','image','name'));
        
        $account_service = VirtualAccountService::first();
        $account_service->card_details = $request->card_details;
        $account_service->config = $data;

        if ($request->hasFile("image")) {
            try {
                $image = get_files_from_fileholder($request, "image");
                $upload_file = upload_files_from_path_dynamic($image, "virtual-account-service");
                $account_service->image = $upload_file;
            } catch (Exception $e) {
                return back()->with(['error' => ['Ops! Failed To Upload Image.']]);
            }
        }
        $account_service->save();

        return back()->with(['success' => ['Virtual Account Service Has Been Updated.']]);
    }
}
