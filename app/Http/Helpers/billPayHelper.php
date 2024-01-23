<?php

use App\Models\VirtualCardApi;



function getBillPayCategories($type){
    $credentials = VirtualCardApi::first();
    
    $sk         =  $credentials->config->flutterwave_secret_key;
    $base_url   =  $credentials->config->flutterwave_url;
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL =>   $base_url."/bill-categories?".$type."=1",
    //   CURLOPT_URL =>   $base_url."/bill-categories",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => "",
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 30,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => "GET",
      CURLOPT_HTTPHEADER => array(
        "Authorization: Bearer ".$sk
      ),
    ));

    $response = curl_exec($curl);
    $result = json_decode($response,true);

    if(isset(($result['status']))){
        if($result['status'] == 'success'){
            $data =[
                'status' => true,
                'data' => $result['data']
            ];
            return $data;
        }
    }else{
        $data =[
            'status' => false,
            'data' => []
        ];
        return $data;
    }

}
function payBill($type,$customer,$amount){
    $credentials = VirtualCardApi::first();

    $sk         =  $credentials->config->flutterwave_secret_key;
    $base_url   =  $credentials->config->flutterwave_url;
    $data = [
        "amount"      => $amount,
        "biller_name" => $type,
        "country"     => "NG",
        "customer"    => $customer,
        "reference"   => getTrxNum(12),
        "type"        => $type
    ];
    
    $headers = [
        "Authorization: Bearer ". $sk,
        'Content-Type: application/json'
    ];
    $curl = curl_init();
    curl_setopt_array($curl, array(
      CURLOPT_URL =>  $base_url.'/bills',
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_ENCODING => '',
      CURLOPT_MAXREDIRS => 10,
      CURLOPT_TIMEOUT => 0,
      CURLOPT_FOLLOWLOCATION => true,
      CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
      CURLOPT_CUSTOMREQUEST => 'POST',
      CURLOPT_POSTFIELDS =>  json_encode($data),
        CURLOPT_HTTPHEADER=> $headers
    ));
    $response = curl_exec($curl);
    $result = json_decode($response,true);
    
    curl_close($curl);
    if(isset($result['status'])){
        if($result['status'] == 'success' || $result['status'] == 'pending'){
            $val= [
                'status' => 'success',
                'message' => $result['message'],
                'data' => $result['data'],
            ];
            return $val;
        }else{
            $val= [
                'status' => 'error',
                'message' => $result['message']??"Something Is Wrong, Try Again Later",
                'data' => $result['data'],
            ];
            return $val;
        }
    }else{
        $val= [
            'status' => 'error',
            'message' => "Something Is Wrong, Try Again Later",
            'data' =>'',
        ];
        return $val;
    }

}
