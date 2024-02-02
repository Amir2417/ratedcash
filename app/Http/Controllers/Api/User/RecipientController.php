<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Receipient;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Http\Helpers\Api\Helpers;
use App\Models\Admin\BasicSettings;
use App\Models\RemitanceCashPickup;
use App\Http\Controllers\Controller;
use App\Models\Admin\ReceiverCounty;
use App\Models\RemitanceBankDeposit;
use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Validator;

class RecipientController extends Controller
{
    /**
     * Method for bank list data
     */
    public function bankList(){
        $banks = getFlutterwaveBanks("NG");
        $data =[
            'banks'   => $banks,
        ];
        $message =  ['success'=>[__('Bank List Data Fetch Successfully.')]];
        return Helpers::success($data,$message);
    }
    public function recipientList(){
        $recipients = Receipient::auth()->orderByDesc("id")->get()->map(function($data){
            return[
                'id' => $data->id,
                'user_id' => $data->user_id,
                'bank_name' => $data->bank_name,
                'bank_code' => $data->bank_code,
                'account_name' => $data->account_name,
                'account_number' => $data->account_number,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,

            ];
        });
        
        $data =[
            'recipients'   => $recipients,
        ];
        $message =  ['success'=>[__('All Recipient')]];
        return Helpers::success($data,$message);
    }
    public function saveRecipientInfo(){
        $basic_settings = BasicSettings::first();
        $transactionType = [
            [
                'id'    => 1,
                'field_name' => Str::slug(GlobalConst::TRX_BANK_TRANSFER),
                'label_name' => "Bank Transfer",
            ],
            [
                'id'    => 2,
                'field_name' =>Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER),
                'label_name' => $basic_settings->site_name.' Wallet',
            ],

            [
                'id'    => 3,
                'field_name' => Str::slug(GlobalConst::TRX_CASH_PICKUP),
                'label_name' => "Cash Pickup",
            ]
         ];
          $transaction_type = (array) $transactionType;

        $receiverCountries = ReceiverCounty::active()->get()->map(function($data){
            return[
                'id' => $data->id,
                'country' => $data->country,
                'name' => $data->name,
                'code' => $data->code,
                'mobile_code' => $data->mobile_code,
                'symbol' => $data->symbol,
                'flag' => $data->flag,
                'rate' => getAmount( $data->rate,2),
                'status' => $data->status,
                'created_at' => $data->created_at,
                'updated_at' => $data->updated_at,

            ];
        });
        $banks = RemitanceBankDeposit::active()->latest()->get();
        $cashPickups = RemitanceCashPickup::active()->latest()->get();
        $data =[
            'base_curr' => get_default_currency_code(),
            'countryFlugPath'   => 'public/backend/images/country-flag',
            'default_image'    => "public/backend/images/default/default.webp",
            'transactionTypes'   => $transaction_type,
            'receiverCountries'   => $receiverCountries,
            'banks'   => $banks,
            'cashPickupsPoints'   => $cashPickups,
        ];
        $message =  ['success'=>[__('Save Recipient Information')]];
        return Helpers::success($data,$message);
    }
    public function dynamicFields(){
        $bank_deposit = [
            [
                'field_name' => "transaction_type",
                'label_name' => "Transaction Type",
            ],
            [
                'field_name' => "firstname",
                'label_name' => "First Name",
            ],
            [
                'field_name' => "lastname",
                'label_name' => "Last Name",
            ],
            [
                'field_name' => "country",
                'label_name' => "Country",
            ],
            [
                'field_name' => "address",
                'label_name' => "Address ",
            ],
            [
                'field_name' => "state",
                'label_name' => "State",
            ],
            [
                'field_name' => "city",
                'label_name' => "City",
            ],
            [
                'field_name' => "zip",
                'label_name' => "Zip Code",
            ],
            [
                'field_name' => "mobile_code",
                'label_name' => "Dial Code",
            ],
            [
                'field_name' => "mobile",
                'label_name' => "Phone Number",
            ],
            [
                'field_name' => "email",
                'label_name' => "Email Address",
            ],
            [
                'field_name' => "bank",
                'label_name' => "Select Bank",
            ],

        ];
        $bank_deposit = (array) $bank_deposit;

        $wallet_to_wallet = [
            [
                'field_name' => "transaction_type",
                'label_name' => "Transaction Type",
            ],
            [
                'field_name' => "country",
                'label_name' => "Country",
            ],
            [
                'field_name' => "mobile_code",
                'label_name' => "Dial Code",
            ],
            [
                'field_name' => "email",
                'label_name' => "Email Address",
            ],
            [
                'field_name' => "mobile",
                'label_name' => "Phone Number",
            ],

            [
                'field_name' => "firstname",
                'label_name' => "First Name",
            ],
            [
                'field_name' => "lastname",
                'label_name' => "Last Name",
            ],

            [
                'field_name' => "address",
                'label_name' => "Address ",
            ],
            [
                'field_name' => "state",
                'label_name' => "State",
            ],
            [
                'field_name' => "city",
                'label_name' => "City",
            ],
            [
                'field_name' => "zip",
                'label_name' => "Zip Code",
            ]


        ];
        $wallet_to_wallet = (array) $wallet_to_wallet;

        $cash_pickup = [
            [
                'field_name' => "transaction_type",
                'label_name' => "Transaction Type",
            ],
            [
                'field_name' => "firstname",
                'label_name' => "First Name",
            ],
            [
                'field_name' => "lastname",
                'label_name' => "Last Name",
            ],
            [
                'field_name' => "country",
                'label_name' => "Country",
            ],
            [
                'field_name' => "address",
                'label_name' => "Address ",
            ],
            [
                'field_name' => "state",
                'label_name' => "State",
            ],
            [
                'field_name' => "city",
                'label_name' => "City",
            ],
            [
                'field_name' => "zip",
                'label_name' => "Zip Code",
            ],
            [
                'field_name' => "mobile_code",
                'label_name' => "Dial Code",
            ],
            [
                'field_name' => "mobile",
                'label_name' => "Phone Number",
            ],
            [
                'field_name' => "email",
                'label_name' => "Email Address",
            ],
            [
                'field_name' => "cash_pickup",
                'label_name' => "Pickup Point",
            ],

         ];
          $cash_pickup = (array) $cash_pickup;
      $message =  ['success'=>[__('Recipient Store/Update Fields Name')]];
      $data = [
        Str::slug(GlobalConst::TRX_BANK_TRANSFER) => $bank_deposit,
        Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER) => $wallet_to_wallet,
        Str::slug(GlobalConst::TRX_CASH_PICKUP) => $cash_pickup,
      ];
      return Helpers::success($data,$message);

    }
    public function checkUser(Request $request){
        $validator = Validator::make(request()->all(), [
            'email' => 'required|email',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validate();
        $field_name = "email";
        try{
            $user = User::where($field_name,$validated['email'])->first();
            if($user != null) {
                if(auth()->user()->email ===  $user->email){
                    $error = ['error'=>[__("Can't send remittance to your own")]];
                    return Helpers::error($error);
                }
                if(@$user->address->country === null ||  @$user->address->country != get_default_currency_name()) {
                    $error = ['error'=>[__("This User Country doesn't match with default currency country!")]];
                    return Helpers::error($error);
                }
            }
            if(!$user){
                $error = ['error'=>[__('User not found')]];
                return Helpers::error($error);
            }
            $data =[
                'user' => $user,
            ];
            $message =  ['success'=>[__('Successfully get user')]];
            return Helpers::success($data,$message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }
    }
    public function storeRecipient(Request $request){
        $validator = Validator::make(request()->all(), [
            'bank_name'      => 'required',
            'account_number' => 'required|unique:receipients,account_number',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $validated = $validator->validated();
        $bank_info = explode('|', $validated['bank_name']);
        try{
            $exist['data'] = checkBankAccount($validated['account_number'],$validated['bank_name']);
           
            if($exist['data']['status'] == 'success'){
                $validated['user_id']        = auth()->user()->id;
                $validated['bank_name']      = $bank_info[1];
                $validated['bank_code']      = $bank_info[0];
                $validated['account_name']   = $exist['data']['data']['account_name'];
                $validated['account_number'] = $exist['data']['data']['account_number'];
                $data = Receipient::create($validated);
            }else{
                $error = ['error'=>[$exist['data']['message']]];
                return Helpers::error($error);
            }
            $message =  ['success'=>[__('Receipient save successfully')]];

            return Helpers::success($data,$message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }

    }
    public function editRecipient(){
        $validator = Validator::make(request()->all(), [
            'id'              =>'required',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $recipient =  Receipient::auth()->where('id',request()->id)->get()->map(function($item){
            return[
                'id' => $item->id,
                'user_id' => $item->user_id,
                'bank_code' => $item->bank_code,
                'bank_name' => $item->bank_name,
                'account_name' => $item->account_name,
                'account_number' => $item->account_number,
                

            ];
        })->first();
        if( !$recipient){
            $error = ['error'=>[__('Invalid request, recipient not found!')]];
            return Helpers::error($error);
        }
        $basic_settings = BasicSettings::first();
        $banks          = getFlutterwaveBanks("NG");

        
        $data =[
            'recipient' => (object)$recipient,
            'base_curr' => get_default_currency_code(),
            'banks'     => $banks
        ];
        $message =  ['success'=>[__('Successfully get recipient')]];
        return Helpers::success($data,$message);
    }
    public function updateRecipient(Request $request){

        $validator = Validator::make(request()->all(), [
            'id'        => 'required',
            'bank_name' => 'required',
            'account_number' => 'required|unique:receipients,account_number',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $data           = Receipient::auth()->where('id',$request->id)->first();
        if(!$data){
            $error = ['error'=>[__("Recipient not found.")]];
            return Helpers::error($error);
        }
        $validated = $validator->validated();
        
        $bank_info = explode('|', $validated['bank_name']);

        try{
            $exist['data'] = checkBankAccount($validated['account_number'],$validated['bank_name']);
           
            if($exist['data']['status'] == 'success'){
                $validated['user_id']        = auth()->user()->id;
                $validated['bank_name']      = $bank_info[1];
                $validated['bank_code']      = $bank_info[0];
                $validated['account_name']   = $exist['data']['data']['account_name'];
                $validated['account_number'] = $exist['data']['data']['account_number'];
                $data->update($validated);
            }else{
                $error = ['error'=>[$exist['data']['message']]];
                return Helpers::error($error);
            }
            $message =  ['success'=>[__('Receipient updated successfully')]];
            return Helpers::success($data,$message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }

    }
    public function deleteRecipient(Request $request){
        $validator = Validator::make(request()->all(), [
            'id'              =>'required',
        ]);
        if($validator->fails()){
            $error =  ['error'=>$validator->errors()->all()];
            return Helpers::validation($error);
        }
        $recipient = Receipient::where('id',$request->id)->first();
        if(!$recipient){
            $error = ['error'=>[__('Invalid request')]];
            return Helpers::error($error);
        }
        try{
            $recipient->delete();
            $message =  ['success'=>[__('Receipient deleted successfully!')]];
            return Helpers::onlysuccess($message);
        }catch(Exception $e) {
            $error = ['error'=>[__("Something went wrong! Please try again.")]];
            return Helpers::error($error);
        }

    }

}
