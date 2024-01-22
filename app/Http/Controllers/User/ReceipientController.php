<?php

namespace App\Http\Controllers\User;

use Exception;
use App\Models\User;
use App\Models\Receipient;
use App\Models\UserWallet;
use App\Models\Transaction;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Constants\GlobalConst;
use App\Http\Helpers\Response;
use App\Models\Admin\Currency;
use App\Models\RemitanceCashPickup;
use App\Http\Controllers\Controller;
use App\Models\Admin\ReceiverCounty;
use App\Models\RemitanceBankDeposit;
use Illuminate\Support\Facades\Auth;
use App\Constants\PaymentGatewayConst;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use App\Models\Admin\PaymentGatewayCurrency;
use Illuminate\Validation\ValidationException;

class ReceipientController extends Controller
{
    public function index()
    {
        $page_title =__( "All Recipient");
        $token = (object)session()->get('remittance_token');
        $user = auth()->user();
        if(@$token ->transacion_type != null && @$token ->receiver_country == null){
            $receipients = Receipient::auth()->where('type',$token->transacion_type)->orderByDesc("id")->paginate(12);
        }elseif(@$token ->transacion_type != null && @$token ->receiver_country != null){
            $receipients = Receipient::auth()->where('type',$token->transacion_type)->where('country',@$token->receiver_country)->orderByDesc("id")->paginate(12);
        }else{
            $receipients = Receipient::auth()->orderByDesc("id")->paginate(12);
        }
        

        return view('user.sections.receipient.index',compact('page_title','receipients','token'));
    }
    public function addReceipient(){
        $page_title = __("Add Recipient");
        $receiverCountries = ReceiverCounty::active()->get();
        $banks = getFlutterwaveBanks("NG");
        $cashPickups = RemitanceCashPickup::active()->latest()->get();
        return view('user.sections.receipient.add',compact('page_title','receiverCountries','banks','cashPickups'));
    }
    public function checkUser(Request $request){
        $fullMobile = $request->mobile;
        $exist['data'] = User::where('full_mobile',$fullMobile)->orWhere('mobile',$fullMobile)->first();
        $user = auth()->user();
        if(@$exist['data'] && $user->full_mobile == @$exist['data']->full_mobile || $user->mobile == @$exist['data']->mobile){
            return response()->json(['own'=>__("Can't send remittance to your own")]);
        }
        return response($exist);
    }
    public function sendRemittance($id){
        $recipient = Receipient::auth()->where("id",$id)->first();
        $token = session()->get('remittance_token');
        $in['receiver_country'] = $recipient->country;
        $in['transacion_type'] = $recipient->type;
        $in['recipient'] = $recipient->id;
        $in['sender_amount'] = $token['sender_amount']??0;
        $in['receive_amount'] = $token['receive_amount']??0;
        Session::put('remittance_token',$in);
        return redirect()->route('user.remittance.index');

    }

    public function store(Request $request){
        
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required',
            'account_number' => 'required|unique:receipients,account_number',
        ]);

        if($validator->fails()){
            return back()->withErrors($validator)->withInput($request->all());
        }
        
        $validated = $validator->validated();
        
        $bank_info = explode('|', $validated['bank_name']);

        try{
            $exist['data'] = checkBankAccount($validated['account_number'],$validated['bank_name']);
           
            if($exist['data']['status'] == 'success'){
                $validated['user_id']        = Auth::id();
                $validated['bank_name']      = $bank_info[1];
                $validated['bank_code']      = $bank_info[0];
                $validated['account_name']   = $exist['data']['data']['account_name'];
                $validated['account_number'] = $exist['data']['data']['account_number'];
                Receipient::create($validated);
            }else{
                $message    = $exist['data']['message'];
                return back()->with(['error' => [$message]]);
            }
        }catch(Exception $e) {
            return back()->with(['error' => [$e->getMessage()]]);
        }
        return redirect()->route('user.receipient.index')->with(['success' => ['Recipient Created Successful!']]);
    }
    public function editReceipient($id){
        $page_title     = __("Edit Recipient");
        $countries      = ReceiverCounty::active()->get();
        $banks          = getFlutterwaveBanks("NG");
        $pickup_points  = RemitanceCashPickup::active()->latest()->get();
        $data           = Receipient::auth()->where('id',$id)->first();

        if( !$data){
            return back()->with(['error' => [__('Invalid request')]]);
        }
        return view('user.sections.receipient.edit',compact('page_title','countries','banks','pickup_points','data'));
    }
    public function updateReceipient(Request $request,$id){
        $data           = Receipient::auth()->where('id',$id)->first();
        $validator = Validator::make($request->all(), [
            'bank_name' => 'required',
            'account_number' => 'required|unique:receipients,account_number',
        ]);

        if($validator->fails()){
            return back()->withErrors($validator)->withInput($request->all());
        }
        
        $validated = $validator->validated();
        
        $bank_info = explode('|', $validated['bank_name']);

        try{
            $exist['data'] = checkBankAccount($validated['account_number'],$validated['bank_name']);
           
            if($exist['data']['status'] == 'success'){
                $validated['user_id']        = Auth::id();
                $validated['bank_name']      = $bank_info[1];
                $validated['bank_code']      = $bank_info[0];
                $validated['account_name']   = $exist['data']['data']['account_name'];
                $validated['account_number'] = $exist['data']['data']['account_number'];
                $data->update($validated);
            }else{
                $message    = $exist['data']['message'];
                return back()->with(['error' => [$message]]);
            }
        }catch(Exception $e) {
            return back()->with(['error' => [$e->getMessage()]]);
        }
        return redirect()->route('user.receipient.index')->with(['success' => ['Recipient Created Successful!']]);

    }
    public function deleteReceipient(Request $request) {
        $validator = Validator::make($request->all(),[
            'target'        => 'required|string|exists:receipients,id',
        ]);
        $validated = $validator->validate();
        $receipient = Receipient::where("id",$validated['target'])->first();
        try{
            $receipient->delete();
        }catch(Exception $e) {
            return back()->with(['error' => [__("Something went wrong! Please try again.")]]);
        }

        return back()->with(['success' => [__('Receipient deleted successfully!')]]);
    }
    public function getTrxTypeInputs(Request $request) {
        $validator = Validator::make($request->all(),[
            'data'          => "required|string"
        ]);
        if($validator->fails()) {
            return Response::error($validator->errors());
        }
        $validated = $validator->validate();


        switch($validated['data']){
            case Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER):
                $countries = ReceiverCounty::active()->get();
                return view('user.components.recipient.trx-type-fields.wallet-to-wallet',compact('countries'));
                break;
            case Str::slug(GlobalConst::TRX_CASH_PICKUP);
                $countries = ReceiverCounty::active()->get();
                $pickup_points =  RemitanceCashPickup::active()->latest()->get();
                return view('user.components.recipient.trx-type-fields.cash-pickup',compact('countries','pickup_points'));
                break;
            case Str::slug(GlobalConst::TRX_BANK_TRANSFER);
                $countries = ReceiverCounty::active()->get();
                $banks =  RemitanceBankDeposit::active()->latest()->get();
                return view('user.components.recipient.trx-type-fields.bank-deposit',compact('countries','banks'));

            default:
                return Response::error([__('Oops! Data not found or section is under maintenance')]);
        }
        return Response::error(['error' => [__("Something went wrong! Please try again.")]]);
    }
    public function getTrxTypeInputsEdit(Request $request) {
        $validator = Validator::make($request->all(),[
            'data'          => "required|string"
        ]);
        if($validator->fails()) {
            return Response::error($validator->errors());
        }
        $validated = $validator->validate();

        switch($validated['data']){
            case Str::slug(GlobalConst::TRX_WALLET_TO_WALLET_TRANSFER):
                $countries = ReceiverCounty::active()->get();
                return view('user.components.recipient.trx-type-fields.edit.wallet-to-wallet',compact('countries'));
                break;
            case Str::slug(GlobalConst::TRX_CASH_PICKUP);
                $countries = ReceiverCounty::active()->get();
                $pickup_points =  RemitanceCashPickup::active()->latest()->get();
                return view('user.components.recipient.trx-type-fields.edit.cash-pickup',compact('countries','pickup_points'));
                break;
            case Str::slug(GlobalConst::TRX_BANK_TRANSFER);
                $countries = ReceiverCounty::active()->get();
                $banks =  RemitanceBankDeposit::active()->latest()->get();
                return view('user.components.recipient.trx-type-fields.edit.bank-deposit',compact('countries','banks'));

            default:
                return Response::error([__('Oops! Data not found or section is under maintenance')]);
        }
        return Response::error(['error' => [__("Something went wrong! Please try again.")]]);
    }
}
