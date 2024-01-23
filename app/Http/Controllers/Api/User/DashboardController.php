<?php

namespace App\Http\Controllers\Api\User;

use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Helpers\Api\Helpers;

class DashboardController extends Controller
{
    /**
     * Method for dashboard data
     */
    public function index(){
        $current_date = date('Y-m-d');
        $current_year = date('Y');
        
        $this_week_start = date('Y-m-d', strtotime('this week monday', strtotime($current_date)));
        $this_week_end = date('Y-m-d', strtotime('this week sunday', strtotime($current_date)));

        $this_month_start   = date('Y-m-01');
        $this_month_end     = date('Y-m-d');
        
        $this_year_start = date('Y-01-01', strtotime($current_year));
        $this_year_end = date('Y-12-31', strtotime($current_year));

        $this_month_bill_pay       = Transaction::auth()->billPay()
                                    ->toBase()
                                    ->whereDate('created_at',">=" , $this_month_start)
                                    ->whereDate('created_at',"<=" , $this_month_end)
                                    ->where('status',1)->sum('request_amount');
        $this_week_bill_pay       = Transaction::auth()->billPay()
                                    ->toBase()
                                    ->whereDate('created_at',">=" , $this_week_start)
                                    ->whereDate('created_at',"<=" , $this_week_end)
                                    ->where('status',1)->sum('request_amount');

        $this_year_bill_pay       = Transaction::auth()->billPay()
                                    ->toBase()
                                    ->whereDate('created_at',">=" , $this_year_start)
                                    ->whereDate('created_at',"<=" , $this_year_end)
                                    ->where('status',1)->sum('request_amount');

        //mobile top up
        $this_month_topup       = Transaction::auth()->mobileTopUp()
                                    ->toBase()
                                    ->whereDate('created_at',">=" , $this_month_start)
                                    ->whereDate('created_at',"<=" , $this_month_end)
                                    ->where('status',1)->sum('request_amount');
        $this_week_topup       = Transaction::auth()->mobileTopUp()
                                    ->toBase()
                                    ->whereDate('created_at',">=" , $this_week_start)
                                    ->whereDate('created_at',"<=" , $this_week_end)
                                    ->where('status',1)->sum('request_amount');
        $this_year_topup       = Transaction::auth()->mobileTopUp()
                                    ->toBase()
                                    ->whereDate('created_at',">=" , $this_year_start)
                                    ->whereDate('created_at',"<=" , $this_year_end)
                                    ->where('status',1)->sum('request_amount');

        //send money
        $this_month_send_money       = Transaction::auth()->senMoney()
                                        ->toBase()
                                        ->whereDate('created_at',">=" , $this_month_start)
                                        ->whereDate('created_at',"<=" , $this_month_end)
                                        ->where('status',1)->sum('request_amount');
        $this_week_send_money       = Transaction::auth()->senMoney()
                                        ->toBase()
                                        ->whereDate('created_at',">=" , $this_week_start)
                                        ->whereDate('created_at',"<=" , $this_week_end)
                                        ->where('status',1)->sum('request_amount');
        $this_year_send_money       = Transaction::auth()->senMoney()
                                        ->toBase()
                                        ->whereDate('created_at',">=" , $this_year_start)
                                        ->whereDate('created_at',"<=" , $this_year_end)
                                        ->where('status',1)->sum('request_amount');
        
        $data =[
            'base_curr'                 => get_default_currency_code(),
            'this_month_bill_pay'       => $this_month_bill_pay,
            'this_week_bill_pay'        => $this_week_bill_pay,
            'this_year_bill_pay'        => $this_year_bill_pay,
            'this_month_topup'          =>  $this_month_topup,
            'this_week_topup'           =>  $this_week_topup,
            'this_year_topup'           => $this_year_topup,
            'this_month_send_money'     => $this_month_send_money,
            'this_week_send_money'      => $this_week_send_money,
            'this_year_send_money'      => $this_year_send_money,
        ];
        $message =  ['success'=>[__('Chart Data Fetch Successfully.')]];
        return Helpers::success($data,$message);
        
        
       
    }
}
