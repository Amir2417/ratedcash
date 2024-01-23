<?php

namespace App\Http\Controllers\Api\User;

use App\Constants\NotificationConst;
use App\Constants\PaymentGatewayConst;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;
use App\Models\Admin\AdminNotification;
use App\Models\Admin\BasicSettings;
use App\Models\Admin\Currency;
use App\Models\Admin\TransactionSetting;
use App\Models\TopupCategory;
use App\Models\Transaction;
use App\Models\UserNotification;
use App\Models\UserWallet;
use App\Notifications\User\MobileTopup\TopupMail;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class MobileTopupController extends Controller
{
    public function topUpInfo(){
        $user = auth()->user();
        $userWallet = UserWallet::where('user_id',$user->id)->get()->map(function($data){
            return[
                'balance' => getAmount($data->balance,2),
                'currency' => get_default_currency_code(),
            ];
        })->first();
        $topupCharge = TransactionSetting::where('slug','mobile_topup')->where('status',1)->get()->map(function($data){
            return[
                'id' => $data->id,
                'slug' => $data->slug,
                'title' => $data->title,
                'fixed_charge' => getAmount($data->fixed_charge,2),
                'percent_charge' => getAmount($data->percent_charge,2),
                'min_limit' => getAmount($data->min_limit,2),
                'max_limit' => getAmount($data->max_limit,2),
                'monthly_limit' => getAmount($data->monthly_limit,2),
                'daily_limit' => getAmount($data->daily_limit,2),
            ];
        })->first();
        $topupType      = getBillPayCategories('airtime');
        
        $billerNames = [];
        foreach ($topupType['data'] as $item) {
            $billerNames[] = (object)['biller_name' => $item['biller_name']];
        }
       
        $transactions = Transaction::auth()->mobileTopup()->latest()->take(5)->get()->map(function($item){
            $statusInfo = [
                "success" =>      1,
                "pending" =>      2,
                "rejected" =>     3,
                ];
            return[
                'id' => $item->id,
                'trx' => $item->trx_id,
                'transaction_type' => $item->type,
                'request_amount' => getAmount($item->request_amount,2).' '.get_default_currency_code() ,
                'payable' => getAmount($item->payable,2).' '.get_default_currency_code(),
                'topup_type' => $item->details->topup_type_name,
                'mobile_number' =>$item->details->mobile_number,
                'total_charge' => getAmount($item->charge->total_charge,2).' '.get_default_currency_code(),
                'current_balance' => getAmount($item->available_balance,2).' '.get_default_currency_code(),
                'status' => $item->stringStatus->value ,
                'date_time' => $item->created_at ,
                'status_info' =>(object)$statusInfo ,
                'rejection_reason' =>$item->reject_reason??"" ,

            ];
        });
        $data =[
            'base_curr' => get_default_currency_code(),
            'base_curr_rate' => get_default_currency_rate(),
            'topupCharge'=> (object)$topupCharge,
            'userWallet'=>  (object)$userWallet,
            'topupTypes'=>  $billerNames,
            'transactions'   => $transactions,
        ];
        $message =  ['success'=>[__('Mobile Top Up Information')]];
        return Helpers::success($data,$message);
    }
    public function topUpConfirmed(Request $request){
        $validator = Validator::make(request()->all(), [
            'topup_type' => 'required|string',
            'mobile_number' => 'required|min:10|max:13',
            'amount' => 'required|numeric|gt:0',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $basic_setting = BasicSettings::first();
        $user = auth()->user();
        if($basic_setting->kyc_verification){
            if( $user->kyc_verified == 0){
                $error = ['error'=>[__('Please submit kyc information!')]];
                return Helpers::error($error);
            }elseif($user->kyc_verified == 2){
                $error = ['error'=>[__('Please wait before admin approved your kyc information')]];
                return Helpers::error($error);
            }elseif($user->kyc_verified == 3){
                $error = ['error'=>[__('Admin rejected your kyc information, Please re-submit again')]];
                return Helpers::error($error);
            }
        }
        $amount = $request->amount;
        $topup_type = $request->topup_type;
        $mobile_number = $request->mobile_number;
        $topupCharge = TransactionSetting::where('slug','mobile_topup')->where('status',1)->first();
        $userWallet = UserWallet::where('user_id',$user->id)->first();
        if(!$userWallet){
            $error = ['error'=>[__('User Wallet not found')]];
            return Helpers::error($error);
        }
        $baseCurrency = Currency::default();
        if(!$baseCurrency){
             $error = ['error'=>[__('Default currency not found')]];
            return Helpers::error($error);
        }
        $rate = $baseCurrency->rate;
        $minLimit =  $topupCharge->min_limit *  $rate;
        $maxLimit =  $topupCharge->max_limit *  $rate;
        if($amount < $minLimit || $amount > $maxLimit) {
            $error = ['error'=>[__("Please follow the transaction limit")]];
            return Helpers::error($error);
        }
        //charge calculations
        $fixedCharge = $topupCharge->fixed_charge *  $rate;
        $percent_charge = ($request->amount / 100) * $topupCharge->percent_charge;
        $total_charge = $fixedCharge + $percent_charge;
        $payable = $total_charge + $amount;
        if($payable > $userWallet->balance ){
            $error = ['error'=>[__('Sorry, insufficient balance')]];
            return Helpers::error($error);
        }
        try{
            $trx_id = 'MP'.getTrxNum();
            $mobileTopUp = payBill($topup_type,$mobile_number,$amount);
            if($mobileTopUp['status'] == 'success'){
                $sender = $this->insertSender( $trx_id,$user,$userWallet,$amount, $topup_type, $mobile_number,$payable);
                $this->insertSenderCharges( $fixedCharge,$percent_charge, $total_charge, $amount,$user,$sender);
                if( $basic_setting->sms_notification == true){
                    //send notifications
                    // sendSms($user,'MOBILE_TOPUP',[
                    //     'amount'=> get_amount($amount,get_default_language_code()),
                    //     'topup_type' => $topUpType??'',
                    //     'mobile_number' =>$mobile_number,
                    //     'trx' => $trx_id,
                    //     'time' =>  now()->format('Y-m-d h:i:s A'),
                    //     'balance' => get_amount($userWallet->balance,$userWallet->currency->code),
                    // ]);
                } 
            }else{
                $error = ['error'=>[$mobileTopUp['message']]];
                return Helpers::error($error);
            }
            
            $message =  ['success'=>[__('Mobile topup request send to admin successful successful')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }

    }
    public function insertSender( $trx_id,$user,$userWallet,$amount, $topup_type, $mobile_number,$payable) {
        $trx_id = $trx_id;
        $authWallet = $userWallet;
        $afterCharge = ($authWallet->balance - $payable);
        $details =[
            'topup_type_name' => $topup_type ?? '',
            'mobile_number' => $mobile_number,
            'topup_amount' => $amount ?? "",
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::MOBILETOPUP,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $payable,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::MOBILETOPUP," ")) . " Request To Admin",
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::SEND,
                'status'                        => 2,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
        return $id;
    }
    public function updateSenderWalletBalance($authWalle,$afterCharge) {
        $authWalle->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertSenderCharges($fixedCharge,$percent_charge, $total_charge, $amount,$user,$id) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $percent_charge,
                'fixed_charge'      => $fixedCharge,
                'total_charge'      => $total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //notification
            $notification_content = [
                'title'         =>__("Mobile Topup"),
                'message'       => __('Mobile topup request send to admin successful')." " .$amount.' '.get_default_currency_code()." ".__("Successful"),
                'image'         => files_asset_path('profile-default'),
            ];

            UserNotification::create([
                'type'      => NotificationConst::MOBILE_TOPUP,
                'user_id'  => $user->id,
                'message'   => $notification_content,
            ]);
            //admin notification
            $notification_content['title'] = __("Mobile topup request send to admin successful")." ".$amount.' '.get_default_currency_code().' '.__("Successful").' ('.$user->username.')';
           AdminNotification::create([
               'type'      => NotificationConst::MOBILE_TOPUP,
               'admin_id'  => 1,
               'message'   => $notification_content,
           ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
}
