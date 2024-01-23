<?php

namespace App\Http\Controllers\Api\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Models\User;
use App\Models\UserPasswordReset;
use App\Notifications\User\Auth\PasswordResetEmail;
use App\Providers\Admin\BasicSettingsProvider;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class ForgotPasswordController extends Controller
{
    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile_code'     => 'required',
            'mobile'     => 'required',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $full_mobile = remove_speacial_char($request->mobile_code).remove_speacial_char($request->mobile);
        $column = "mobile";
        $user = User::where($column,$request->mobile)->where('full_mobile',$full_mobile)->first();

        if(!$user) {
            $error = ['error'=>[__("User doesn't exists.")]];
            return Helpers::error($error);
        }
        $token = generate_unique_string("user_password_resets","token",80);
        $code = generate_random_code();

        try{
            UserPasswordReset::where("user_id",$user->id)->delete();
            $password_reset = UserPasswordReset::create([
                'user_id'       => $user->id,
                'mobile'        => $full_mobile,
                'code'          => $code,
                'token'         => $token,
                'created_at'    => now(),
            ]);
            sendSms($user, 'PASS_RESET_CODE', [
                'code' => $code
            ]);
        }catch(Exception $e) {
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }

        $message =  ['success'=>[__('Verification code sent to your phone')]];
        return Helpers::onlysuccess($message);
    }

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'mobile_code'     => 'required',
            'mobile'     => 'required',
            'code' => 'required|numeric',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $full_mobile = remove_speacial_char($validated['mobile_code']).remove_speacial_char($validated['mobile']);
        $code = $request->code;
        $basic_settings = BasicSettingsProvider::get();
        $otp_exp_seconds = $basic_settings->otp_exp_seconds ?? 0;
        $password_reset = UserPasswordReset::where("mobile",$full_mobile)->where("code", $code)->first();
        if(!$password_reset) {
            $error = ['error'=>[__('Verification Otp is Invalid')]];
            return Helpers::error($error);
        }
        if(Carbon::now() >= $password_reset->created_at->addSeconds($otp_exp_seconds)) {
            foreach(UserPasswordReset::get() as $item) {
                if(Carbon::now() >= $item->created_at->addSeconds($otp_exp_seconds)) {
                    $item->delete();
                }
            }
            $error = ['error'=>[__('Time expired. Please try again')]];
            return Helpers::error($error);
        }

        $message =  ['success'=>[__('Your Verification is successful, Now you can recover your password')]];
        return Helpers::onlysuccess($message);
    }
    public function resetPassword(Request $request) {
        $basic_settings = BasicSettingsProvider::get();
        $passowrd_rule = "required|string|min:6|confirmed";
        if($basic_settings->secure_password) {
            $passowrd_rule = ["required",Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),"confirmed"];
        }

        $validator = Validator::make($request->all(), [
            'mobile_code'   => 'required',
            'mobile'        => 'required',
            'password'      => $passowrd_rule,
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $full_mobile = remove_speacial_char($validated['mobile_code']).remove_speacial_char($validated['mobile']);
        $code = $request->code;
        $password_reset = UserPasswordReset::where("mobile",$full_mobile)->first();
        if(!$password_reset) {
            $error = ['error'=>[__('Invalid request')]];
            return Helpers::error($error);
        }
        try{
            $password_reset->user->update([
                'password'      => Hash::make($request->password),
            ]);
            $password_reset->delete();
        }catch(Exception $e) {
            $error = ['error'=>[__('Something went wrong! Please try again.')]];
            return Helpers::error($error);
        }
        $message =  ['success'=>[__('Password reset success. Please login with new password.')]];
        return Helpers::onlysuccess($message);
    }


}
