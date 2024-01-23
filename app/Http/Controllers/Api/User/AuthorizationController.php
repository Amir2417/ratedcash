<?php

namespace App\Http\Controllers\Api\User;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Models\Admin\SetupKyc;
use Illuminate\Support\Carbon;
use App\Http\Helpers\Api\Helpers;
use App\Models\UserAuthorization;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Traits\ControlDynamicInputFields;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Notification;
use App\Providers\Admin\BasicSettingsProvider;
use Illuminate\Validation\ValidationException;
use App\Notifications\User\Auth\SendVerifyCode;
use App\Notifications\User\Auth\SendAuthorizationCode;

class AuthorizationController extends Controller
{
    use ControlDynamicInputFields;
    protected $basic_settings;

    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function sendSmsCode()
    {
        $user = auth()->user();
        if($user->sms_verified == true){
            $error = ['error'=>[__("Your Account Already Verified.")]];
            return Helpers::error($error);
        }
        $resend = UserAuthorization::where("user_id",$user->id)->first();
        if( $resend){
            if(Carbon::now() <= $resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)) {
                $error = ['error'=>[ __("You can resend verification code after").' '.Carbon::now()->diffInSeconds($resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)). ' '.__('seconds')]];
                return Helpers::error($error);
            }
        }
        $code =  generate_random_code();
        $data = [
            'user_id'       =>  $user->id,
            'code'          =>  $code,
            'mobile'        =>  $user->full_mobile,
            'token'         => generate_unique_string("user_authorizations","token",200),
            'created_at'    => now(),
        ];
        DB::beginTransaction();
        try{
            if($resend) {
                UserAuthorization::where("user_id", $user->id)->delete();
            }
            DB::table("user_authorizations")->insert($data);
            sendSms($user, 'SVER_CODE', [
                'code' => $code
            ]);
            DB::commit();
            $message =  ['success'=>[__('Verification code send success')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    public function smsVerify(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'code' => 'required|numeric',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $user = auth()->user();
        $code = $request->code;
        $otp_exp_sec = BasicSettingsProvider::get()->otp_exp_seconds ?? GlobalConst::DEFAULT_TOKEN_EXP_SEC;
        $auth_column = UserAuthorization::where("user_id",$user->id)->where("code",$code)->first();

        if(!$auth_column){
             $error = ['error'=>[__('Verification code does not match')]];
            return Helpers::error($error);
        }
        if($auth_column->created_at->addSeconds($otp_exp_sec) < now()) {
            $error = ['error'=>[__('Time expired. Please try again')]];
            return Helpers::error($error);
        }
        try{
            $auth_column->user->update([
                'sms_verified'    => true,
            ]);
            $auth_column->delete();
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        $message =  ['success'=>[__('Account successfully verified')]];
        return Helpers::onlysuccess($message);
    }
    public function showKycFrom(){
        $user = auth()->user();
        $kyc_status = $user->kyc_verified;
        $user_kyc = SetupKyc::userKyc()->first();
        $status_info = "1==verified, 2==pending, 0==unverified; 3=rejected";
        $kyc_data = $user_kyc->fields;
        $kyc_fields = [];
        if($kyc_data) {
            $kyc_fields = array_reverse($kyc_data);
        }
        $data =[
            'status_info' => $status_info,
            'kyc_status' => $kyc_status,
            'userKyc' => $kyc_fields
        ];
        $message = ['success'=>[ __("KYC Verification")]];
        return Helpers::success($data,$message);

    }
    public function kycSubmit(Request $request){
        $user = auth()->user();
        if($user->kyc_verified == GlobalConst::VERIFIED){
            $message = ['error'=>[__('You are already KYC Verified User')]];
            return Helpers::error($message);

        }
        $user_kyc_fields = SetupKyc::userKyc()->first()->fields ?? [];
        $validation_rules = $this->generateValidationRules($user_kyc_fields);
        $validated = Validator::make($request->all(), $validation_rules);

        if ($validated->fails()) {
            $message =  ['error' => $validated->errors()->all()];
            return Helpers::error($message);
        }
        $validated = $validated->validate();
        $get_values = $this->placeValueWithFields($user_kyc_fields, $validated);
        $create = [
            'user_id'       => auth()->user()->id,
            'data'          => json_encode($get_values),
            'created_at'    => now(),
        ];

        DB::beginTransaction();
        try{
            DB::table('user_kyc_data')->updateOrInsert(["user_id" => $user->id],$create);
            $user->update([
                'kyc_verified'  => GlobalConst::PENDING,
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $user->update([
                'kyc_verified'  => GlobalConst::DEFAULT,
            ]);
            $message = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($message);
        }
        $message = ['success'=>[__('KYC information successfully submitted')]];
        return Helpers::onlysuccess($message);

    }

    //========================before registration======================================
    public function checkExist(Request $request){
        $validator = Validator::make($request->all(), [
            'mobile_code'     => 'required',
            'mobile'     => 'required',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $full_mobile = remove_speacial_char($request->mobile_code).remove_speacial_char((int) $request->mobile);

        $column = "mobile";

        if(mobileUniqueCheck((int) $request->mobile) == false){
            $error = ['error'=>[__('User already exist, please select another email address')]];
            return Helpers::validation($error);
        }
        $message = ['success'=>[__('Now,You can register')]];
        return Helpers::onlysuccess($message);

    }
    public function sendMobileOtp(Request $request){
        $basic_settings = $this->basic_settings;
        if($basic_settings->agree_policy){
            $agree = 'required';
        }else{
            $agree = '';
        }

        if( $request->agree != 1){
            return Helpers::error(['error' => [__('Terms Of Use & Privacy Policy Field Is Required!')]]);
        }

        $validator = Validator::make($request->all(), [
            'mobile_code'   => 'required',
            'mobile'        => 'required',
            'agree'         =>  $agree,
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();

        $full_mobile = remove_speacial_char($validated['mobile_code']).remove_speacial_char($validated['mobile']);
        $field_name = "mobile";
        
        $exist = User::where($field_name,$validated['mobile'])->where('full_mobile',$full_mobile)->active()->first();
        if( $exist){
            $message = ['error'=>[__('User already exist, please select another email address')]];
            return Helpers::error($message);
        }

        $code = generate_random_code();
        $data = [
            'user_id'       =>  0,
            'mobile'         => $full_mobile,
            'code'          => $code,
            'token'         => generate_unique_string("user_authorizations","token",200),
            'created_at'    => now(),
        ];
        DB::beginTransaction();
        try{
            $oldToken = UserAuthorization::where("mobile", $full_mobile)->get();
            if($oldToken){
                foreach($oldToken as $token){
                    $token->delete();
                }
            }
            DB::table("user_authorizations")->insert($data);
            sendSmsNotAuthUser($full_mobile, 'SVER_CODE', [
                'code' => $code
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            
            $message = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($message);
        };
        $message = ['success'=>[__('Verification code sended to your Mobile.')]];
        return Helpers::onlysuccess($message);
    }
    public function verifyMobileOtp(Request $request){
        $validator = Validator::make($request->all(), [
            'mobile_code'     => 'required',
            'mobile'     => 'required',
            'code'    => "required|max:6",
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $full_mobile = remove_speacial_char($validated['mobile_code']).remove_speacial_char($validated['mobile']);
        $code = $request->code;
        $otp_exp_sec = BasicSettingsProvider::get()->otp_exp_seconds ?? GlobalConst::DEFAULT_TOKEN_EXP_SEC;
        $auth_column = UserAuthorization::where("mobile",$full_mobile)->where("code",$code)->first();
        if(!$auth_column){
            $message = ['error'=>[__('Verification code does not match')]];
            return Helpers::error($message);
        }
        if($auth_column->created_at->addSeconds($otp_exp_sec) < now()) {
            $auth_column->delete();
            $message = ['error'=>[__('Verification code is expired')]];
            return Helpers::error($message);
        }
        try{
            $auth_column->delete();
        }catch(Exception $e) {
            $message = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($message);
        }
        $message = ['success'=>[__('Otp successfully verified')]];
        return Helpers::onlysuccess($message);
    }
    public function resendMobileOtp(Request $request){
        $validator = Validator::make($request->all(), [
            'mobile_code'     => 'required',
            'mobile'     => 'required',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $full_mobile = remove_speacial_char($validated['mobile_code']).remove_speacial_char($validated['mobile']);
        $resend = UserAuthorization::where("mobile", $full_mobile)->first();
        if($resend){
            if(Carbon::now() <= $resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)) {
                $message = ['error'=>[__("You can resend verification code after").' '.Carbon::now()->diffInSeconds($resend->created_at->addMinutes(GlobalConst::USER_VERIFY_RESEND_TIME_MINUTE)). ' '.__('seconds')]];
                return Helpers::error($message);
            }
        }
        $code = generate_random_code();
        $data = [
            'user_id'       =>  0,
            'mobile'         => $full_mobile,
            'code'          => $code,
            'token'         => generate_unique_string("user_authorizations","token",200),
            'created_at'    => now(),
        ];
        DB::beginTransaction();
        try{
            $oldToken = UserAuthorization::where("mobile", $full_mobile)->get();
            if($oldToken){
                foreach($oldToken as $token){
                    $token->delete();
                }
            }
            DB::table("user_authorizations")->insert($data);
            sendSmsNotAuthUser($full_mobile, 'SVER_CODE', [
                'code' => $code
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $message = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($message);
        }
        $message = ['success'=>[__('Verification code resend success')]];
        return Helpers::onlysuccess($message);
    }
    public function sendVerifyPin(Request $request){
        
        $request->validate([
            'code'      => "required",
        ]);
        $code = $request->code;
        try{
            $user = auth()->user();
            $user->pin_code = Hash::make($code);
            $user->pin_status = true;
            $user->update();

        }catch(Exception $e) {
            $error = ['error'=>['Something went wrong! Please try again']];
            return Helpers::error($error);
        }

        $success = ['success'=>['Pin Successfully Setup!']];
        return Helpers::onlySuccess($success);
    }


    public function checkPin(Request $request){
        $pin = $request->pin;
        $user = auth()->user();

        if(!Hash::check($pin, $user->pin_code)){
            $error = ['error'=>['Invalid PIN Code!']];
            $data = ['status' => false];
            return Helpers::error($error, $data);
        }else{
            $data = ['status' => true];
            $success = ['success'=>['Valid PIN Code!']];
            return Helpers::success($data, $success);
        }
    }
}
