<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Admin\BasicSettings;
use Exception;
use App\Lib\SendSms;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;

class SetupSmsController extends Controller
{
    public function configuration() {
        $page_title = "Sms Method";
        $general = BasicSettings::first();
        return view('admin.sections.sms-method.config',compact(
            'page_title',
            'general',
        ));
    }
    public function update(Request $request)
    {

        $validator = Validator::make($request->all(),[
            'sms_method' => 'required|in:clickatell,infobip,messageBird,nexmo,smsBroadcast,twilio,textMagic',
            'clickatell_api_key' => 'required_if:sms_method,clickatell',
            'message_bird_api_key' => 'required_if:sms_method,messageBird',
            'nexmo_api_key' => 'required_if:sms_method,nexmo',
            'nexmo_api_secret' => 'required_if:sms_method,nexmo',
            'infobip_username' => 'required_if:sms_method,infobip',
            'infobip_password' => 'required_if:sms_method,infobip',
            'sms_broadcast_username' => 'required_if:sms_method,smsBroadcast',
            'sms_broadcast_password' => 'required_if:sms_method,smsBroadcast',
            'text_magic_username' => 'required_if:sms_method,textMagic',
            'apiv2_key' => 'required_if:sms_method,textMagic',
            'account_sid' => 'required_if:sms_method,twilio',
            'auth_token' => 'required_if:sms_method,twilio',
            'from' => 'required_if:sms_method,twilio',
        ]);

        $validated = $validator->validate();

        $basic_settings = BasicSettings::first();
        if(!$basic_settings) {
            return back()->with(['error' => ['Basic settings not found!']]);
        }

        // Make object of firebase data
        $request->merge(['name'=>$request->sms_method]);
        $data = array_filter($request->except('_token','sms_method'));
        try{
            $basic_settings->update([
                'sms_config'       => $data,
            ]);
        }catch(Exception $e) {
            return back()->with(['error' => ['Something went worng! Please try again.']]);
        }
        return back()->with(['success' => ['Information updated successfully!']]);
    }
    public function sendTestSMS(Request $request)
    {
        $request->validate(['mobile' => 'required']);
        $general = BasicSettings::first(['sms_verification', 'sms_config','sms_api','site_name']);
        
        try{
            if ($general->sms_verification == 1) {
                $gateway = $general->sms_config->name;
                $sendSms = new SendSms;
                $message = shortCodeReplacer("{{name}}", 'Admin', $general->sms_api);
                $message = shortCodeReplacer("{{message}}", 'This is a test sms', $message);
                $sendSms->$gateway($request->mobile,$general->sitename,$message,$general->sms_config);
                return back()->with(['success' => ['You should receive a test sms at ' . $request->mobile . ' shortly.']]);
            }else{
                return back()->with(['error' => ['Sms notification system is off!.']]);
            }
        }catch(Exception $e){
            return back()->with(['error' => [$e->getMessage()]]);
        }

    }
}
