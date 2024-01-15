<?php

namespace App\Http\Controllers\Admin;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserWallet;
use Exception;
use Illuminate\Support\Facades\Validator;
use App\Notifications\User\AddMoney\ApprovedByAdminMail;
use App\Notifications\User\AddMoney\RejectedByAdminMail;
use App\Providers\Admin\BasicSettingsProvider;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AddMoneyTransactionExport;

class AddMoneyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    protected $basic_settings;

    public function __construct()
    {
        $this->basic_settings = BasicSettingsProvider::get();
    }
    public function index()
    {
        $page_title = "All Logs";
        $transactions = Transaction::userTrx()->with(
          'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name',
        )->where('type', 'ADD-MONEY')->latest()->paginate(20);


        return view('admin.sections.add-money.index', compact(
            'page_title',
            'transactions'
        ));
    }


    /**
     * Pending Add Money Logs View.
     * @return view $pending-add-money-logs
     */
    public function pending()
    {
        $page_title = "Pending Logs";
        $transactions = Transaction::userTrx()->with(
         'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name',
        )->where('type', 'add-money')->where('status', 2)->latest()->paginate(20);
        return view('admin.sections.add-money.index', compact(
            'page_title',
            'transactions'
        ));
    }


    /**
     * Complete Add Money Logs View.
     * @return view $complete-add-money-logs
     */
    public function complete()
    {
        $page_title = "Complete Logs";
        $transactions = Transaction::userTrx()->with(
          'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name',
        )->where('type', 'add-money')->where('status', 1)->latest()->paginate(20);
        return view('admin.sections.add-money.index', compact(
            'page_title',
            'transactions'
        ));
    }

    /**
     * Canceled Add Money Logs View.
     * @return view $canceled-add-money-logs
     */
    public function canceled()
    {
        $page_title = "Canceled Logs";
        $transactions = Transaction::userTrx()->with(
          'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name',
        )->where('type', 'add-money')->where('status',4)->latest()->paginate(20);
        return view('admin.sections.add-money.index', compact(
            'page_title',
            'transactions'
        ));
    }
    public function addMoneyDetails($id){

        $data = Transaction::where('id',$id)->with(
          'user:id,firstname,lastname,email,username,full_mobile',
            'currency:id,name,alias,payment_gateway_id,currency_code,rate',
        )->where('type', 'add-money')->first();
        $page_title = "Add money details for".'  '.$data->trx_id;
        return view('admin.sections.add-money.details', compact(
            'page_title',
            'data'
        ));
    }

    public function approved(Request $request){

        $validator = Validator::make($request->all(),[
            'id' => 'required|integer',
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $data = Transaction::where('id',$request->id)->where('status',2)->where('type', 'add-money')->first();
        try{
            //update wallet
            $userWallet = UserWallet::where('user_id',$data->user_id)->first();
            $userWallet->balance +=  $data->request_amount;
            $userWallet->save();
            //update transaction
            $data->status = 1;
            $data->available_balance =  $userWallet->balance;
            $data->save();
            $user = User::where('id',$data->user_id)->first();
            if( $this->basic_settings->email_notification == true){
            $user->notify(new ApprovedByAdminMail($user,$data));
            }

            return redirect()->back()->with(['success' => ['Add Money request approved successfully']]);
        }catch(Exception $e){
            return back()->with(['error' => [$e->getMessage()]]);
        }
    }
    public function rejected(Request $request){

        $validator = Validator::make($request->all(),[
            'id' => 'required|integer',
            'reject_reason' => 'required|string|max:200',
        ]);
        if($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $data = Transaction::where('id',$request->id)->where('status',2)->where('type', 'add-money')->first();
        $up['status'] = 4;
        $up['reject_reason'] = $request->reject_reason;
        try{
            $data->fill($up)->save();
            $user = User::where('id',$data->user_id)->first();
            if( $this->basic_settings->email_notification == true){
            $user->notify(new RejectedByAdminMail($user,$data));
            }
            return redirect()->back()->with(['success' => ['Add Money request rejected successfully']]);
        }catch(Exception $e){
            return back()->with(['error' => [$e->getMessage()]]);
        }
    }
    public function exportData(){
        $file_name = now()->format('Y-M-d_H:i:s') . "_Add_Money_Logs".'.xlsx';
        return Excel::download(new AddMoneyTransactionExport, $file_name);
    }

}
