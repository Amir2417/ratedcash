<?php

namespace App\Http\Controllers\User;

use Exception;
use App\Models\User;
use App\Models\Receipient;
use App\Models\UserWallet;
use App\Models\Transaction;
use Jenssegers\Agent\Agent;
use Illuminate\Http\Request;
use App\Models\Admin\Currency;
use App\Models\UserNotification;
use Illuminate\Support\Facades\DB;
use App\Models\Admin\BasicSettings;
use App\Constants\NotificationConst;
use App\Http\Controllers\Controller;
use App\Models\Admin\PaymentGateway;
use App\Constants\PaymentGatewayConst;
use App\Models\Admin\AdminNotification;
use App\Models\Admin\TransactionSetting;
use App\Notifications\User\SendMoney\SenderMail;
use App\Notifications\User\SendMoney\ReceiverMail;
use App\Events\User\NotificationEvent as UserNotificationEvent;

class SendMoneyController extends Controller
{
    protected  $trx_id;

    public function __construct()
    {
        $this->trx_id = 'SM'.getTrxNum();
    }
    public function index() {
        $page_title         = __("Send Money");
        $sendMoneyCharge    = TransactionSetting::where('slug','transfer')->where('status',1)->first();
        $receipients        = Receipient::auth()->get();
        $transactions       = Transaction::auth()->senMoney()->latest()->take(10)->get();
        return view('user.sections.send-money.index',compact("page_title",'sendMoneyCharge','transactions','receipients'));
    }
    public function checkUser(Request $request){
        $email = $request->email;
        $exist['data'] = User::where('email',$email)->first();

        $user = auth()->user();
        if(@$exist['data'] && $user->email == @$exist['data']->email){
            return response()->json(['own'=>__("Can't send money to your own")]);
        }
        return response($exist);
    }
    public function confirmed(Request $request){
        $request->validate([
            'amount' => 'required|numeric|gt:0',
            'recipient' => 'required'
        ]);
        $basic_setting = BasicSettings::first();
        $user = auth()->user();
        if($basic_setting->kyc_verification){
            if( $user->kyc_verified == 0){
                return redirect()->route('user.profile.index')->with(['error' => [__('Please submit kyc information!')]]);
            }elseif($user->kyc_verified == 2){
                return redirect()->route('user.profile.index')->with(['error' => [__('Please wait before admin approved your kyc information')]]);
            }elseif($user->kyc_verified == 3){
                return redirect()->route('user.profile.index')->with(['error' => [__('Admin rejected your kyc information, Please re-submit again')]]);
            }
        }
        $amount = $request->amount;
        $user = auth()->user();
        $sendMoneyCharge = TransactionSetting::where('slug','transfer')->where('status',1)->first();
        $userWallet = UserWallet::where('user_id',$user->id)->first();
        if(!$userWallet){
            return back()->with(['error' => [__('User wallet not found')]]);
        }

        $baseCurrency = Currency::default();
        $rate = $baseCurrency->rate;
        if(!$baseCurrency){
            return back()->with(['error' => [__('Default currency not found')]]);
        }
        
        $minLimit =  $sendMoneyCharge->min_limit *  $rate;
        $maxLimit =  $sendMoneyCharge->max_limit *  $rate;
        if($amount < $minLimit || $amount > $maxLimit) {
            return back()->with(['error' => [__("Please follow the transaction limit")]]);
        }
        //charge calculations
        $fixedCharge = $sendMoneyCharge->fixed_charge *  $rate;
        $percent_charge = ($request->amount / 100) * $sendMoneyCharge->percent_charge;
        $total_charge = $fixedCharge + $percent_charge;
        $payable = $total_charge + $amount;
        
        if($payable > $userWallet->balance ){
            return back()->with(['error' => [__('Sorry, insufficient balance')]]);
        }
        
        $recipient     = Receipient::auth()->where('account_number',$request->recipient)->first();
        if(!$recipient) return back()->with(['error' => ['Receipient not found! Please add a receipient']]);
        $cardApi = PaymentGateway::where('type',"AUTOMATIC")->where('alias','flutterwave-money-out')->first();
        $secret_key = getPaymentCredentials($cardApi->credentials,'Secret key');
        $base_url =getPaymentCredentials($cardApi->credentials,'Base Url');
        $callback_url = getPaymentCredentials($cardApi->credentials,'Callback Url');
        $ch = curl_init();
        $url =  $base_url.'/transfers';
        $data = [
            "account_bank"   => $recipient->bank_name,
            "account_number" => $recipient->account_number,
            "amount"         => $amount,
            "narration"      => "Send from wallet",
            "currency"       => "NGN",
            "reference"      => generateTransactionReference(),
            "callback_url"   => $callback_url,
            "debit_currency" => "NGN"
        ];
        $headers = [
            'Authorization: Bearer '.$secret_key,
            'Content-Type: application/json'
        ];

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $response = curl_exec($ch);
        if (curl_errno($ch)) {
            return back()->with(['error' => [curl_error($ch)]]);
        } else {
            $result = json_decode($response,true);
            if($result['status'] && $result['status'] == 'success'){
                try{
                    $trx_id = $this->trx_id;
                    
                    $inserted_id = $this->insertSender($trx_id,$user,$userWallet,$amount,$recipient,$payable);
                    if($inserted_id){
                        $this->insertSenderCharges($fixedCharge,$percent_charge, $total_charge, $amount,$user,$inserted_id,$recipient);
                        $this->insertDevice($inserted_id);
                        //sms notification
                        sendSms(auth()->user(),'Send Money',[
                            'amount'=> get_amount($amount,get_default_currency_code()),
                            'trx' => $trx_id,
                            'time' =>  now()->format('Y-m-d h:i:s A'),
                            'will_get' => get_amount($amount,get_default_currency_code()),
                            'currency' => get_default_currency_code(),
                        ]);
                    }
                    return redirect()->route("user.send.money.index")->with(['success' => [__('Send Money successful to').' '.$recipient->account_name]]);
                }catch(Exception $e) {
                    return back()->with(['error' => [$e->getMessage()]]);
                }
            }else{
                return back()->with(['error' => [$result['message']]]);
            }
        }
        curl_close($ch);

    }
    //sender transaction
    public function insertSender($trx_id,$user,$userWallet,$amount,$recipient,$payable) {
        $trx_id = $trx_id;
        $authWallet = $userWallet;
        $afterCharge = ($authWallet->balance - $payable);
        $details =[
            'recipient_amount' => $amount,
            'recipient'        => $recipient
        ];
        DB::beginTransaction();
        try{
            $id = DB::table("transactions")->insertGetId([
                'user_id'                       => $user->id,
                'user_wallet_id'                => $authWallet->id,
                'payment_gateway_currency_id'   => null,
                'type'                          => PaymentGatewayConst::TYPETRANSFERMONEY,
                'trx_id'                        => $trx_id,
                'request_amount'                => $amount,
                'payable'                       => $payable,
                'available_balance'             => $afterCharge,
                'remark'                        => ucwords(remove_speacial_char(PaymentGatewayConst::TYPETRANSFERMONEY," ")) . " To " .$recipient->account_name,
                'details'                       => json_encode($details),
                'attribute'                      =>PaymentGatewayConst::SEND,
                'status'                        => true,
                'created_at'                    => now(),
            ]);
            $this->updateSenderWalletBalance($authWallet,$afterCharge);

            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
        return $id;
    }
    public function updateSenderWalletBalance($authWalle,$afterCharge) {
        $authWalle->update([
            'balance'   => $afterCharge,
        ]);
    }
    public function insertSenderCharges($fixedCharge,$percent_charge, $total_charge, $amount,$user,$id,$recipient) {
        DB::beginTransaction();
        try{
            DB::table('transaction_charges')->insert([
                'transaction_id'    => $id,
                'percent_charge'    => $percent_charge,
                'fixed_charge'      =>$fixedCharge,
                'total_charge'      =>$total_charge,
                'created_at'        => now(),
            ]);
            DB::commit();

            //store notification
            $notification_content = [
                'title'         => __("Send Money"),
                'message'       => __('Transfer Money to')." ".$recipient->account_name.' ' .$amount.' '.get_default_currency_code()." ".__('Successful'),
                'image'         =>  get_image($user->image,'user-profile'),
            ];
            UserNotification::create([
                'type'      => NotificationConst::TRANSFER_MONEY,
                'user_id'  => $user->id,
                'message'   => $notification_content,
            ]);

            
            DB::commit();

        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception(__("Something went wrong! Please try again."));
        }
    }
    public function insertDevice($id) {
        $client_ip = request()->ip() ?? false;
        $location = geoip()->getLocation($client_ip);
        $agent = new Agent();

        // $mac = exec('getmac');
        // $mac = explode(" ",$mac);
        // $mac = array_shift($mac);
        $mac = "";

        DB::beginTransaction();
        try{
            DB::table("transaction_devices")->insert([
                'transaction_id'=> $id,
                'ip'            => $client_ip,
                'mac'           => $mac,
                'city'          => $location['city'] ?? "",
                'country'       => $location['country'] ?? "",
                'longitude'     => $location['lon'] ?? "",
                'latitude'      => $location['lat'] ?? "",
                'timezone'      => $location['timezone'] ?? "",
                'browser'       => $agent->browser() ?? "",
                'os'            => $agent->platform() ?? "",
            ]);
            DB::commit();
        }catch(Exception $e) {
            DB::rollBack();
            throw new Exception($e->getMessage());
        }
    }
    
}
