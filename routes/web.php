<?php

use App\Http\Controllers\Api\User\AddMoneyController as UserAddMoneyController;
use App\Http\Controllers\DeveloperController;
use App\Http\Controllers\SiteController;
use App\Http\Controllers\User\AddMoneyController;
use App\Http\Controllers\User\PaymentLinkController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


//landing pages
Route::controller(SiteController::class)->group(function(){
    Route::get('/','home')->name('index');
    Route::get('about','about')->name('about');
    Route::get('service','service')->name('service');
    Route::get('faq','faq')->name('faq');
    Route::get('web/journal','blog')->name('blog');
    Route::get('web/journal/details/{id}/{slug}','blogDetails')->name('blog.details');
    Route::get('web/journal/by/category/{id}/{slug}','blogByCategory')->name('blog.by.category');
    Route::get('merchant-info','merchant')->name('merchant');
    Route::get('contact','contact')->name('contact');
    Route::post('contact/store','contactStore')->name('contact.store');
    Route::get('change/{lang?}','changeLanguage')->name('lang');
    Route::get('page/{slug}','usefulPage')->name('useful.link');
    Route::post('newsletter','newsletterSubmit')->name('newsletter.submit');
    Route::get('/user/success','pagaditoSuccess')->name('success');

});

Route::controller(DeveloperController::class)->prefix('developer')->name('developer.')->group(function(){
    Route::get('/','index')->name('index');
    Route::get('prerequisites','prerequisites')->name('prerequisites');
    Route::get('authentication','authentication')->name('authentication');
    Route::get('base-url','baseUrl')->name('base.url');
    Route::get('access.token','accessToken')->name('access.token');
    Route::get('initiate-payment','initiatePayment')->name('initiate.payment');
    Route::get('check-status-payment','checkStatusPayment')->name('check.status.payment');
    Route::get('response-code','responseCode')->name('response.code');
    Route::get('error-handling','errorHandling')->name('error.handling');
    Route::get('best-practices','bestPractices')->name('best.practices');
    Route::get('examples','examples')->name('examples');
    Route::get('faq','faq')->name('faq');
    Route::get('support','support')->name('support');

});

//for sslcommerz callback urls(web)
Route::controller(AddMoneyController::class)->prefix("add-money")->name("add.money.")->group(function(){
    //sslcommerz
    Route::post('sslcommerz/success','sllCommerzSuccess')->name('ssl.success');
    Route::post('sslcommerz/fail','sllCommerzFails')->name('ssl.fail');
    Route::post('sslcommerz/cancel','sllCommerzCancel')->name('ssl.cancel');
    Route::post("/callback/response/{gateway}",'callback')->name('payment.callback')->withoutMiddleware(['web','auth','verification.guard','user.google.two.factor']);
});
//for sslcommerz callback urls(api)
Route::controller(UserAddMoneyController::class)->prefix("api/add-money")->name("api.add.money.")->group(function(){
    //sslcommerz
    Route::post('sslcommerz/success','sllCommerzSuccess')->name('ssl.success');
    Route::post('sslcommerz/fail','sllCommerzFails')->name('ssl.fail');
    Route::post('sslcommerz/cancel','sllCommerzCancel')->name('ssl.cancel');
    Route::get('razor-payment/api-link/{trx_id}','razorPaymentLink')->name('razorPaymentLink');
});

//both merchants/users
Route::controller(PaymentLinkController::class)->prefix('/payment-link')->name('payment-link.')->group(function(){
    Route::get('/share/{token}','paymentLinkShare')->name('share');
    Route::post('/submit','paymentLinkSubmit')->name('submit')->middleware('app.mode');
    Route::get('/transaction/success/{token}','transactionSuccess')->name('transaction.success');
});
Route::get('create/card',function(){
    // Replace these with your actual values
    $flutterwaveSecretKey = 'FLWSECK_TEST-SANDBOXDEMOKEY-X';
    $currency = 'USD';
    $amount = 5;
    $userFirstName = 'Example';
    $userLastName = 'User';
    $userDateOfBirth = '1996/12/30';
    $userEmail = 'user@example.com';
    $userPhone = '07030000000'; // Provide the actual phone number
    $userTitle = 'MR'; // Provide the actual title
    $userGender = 'M'; // Provide the actual gender
    $callbackUrl = 'https://your-callback-url.com';
    $userBVN = '100735067'; // Provide the actual BVN

    // Construct the data array
    $data = [
        "currency" => $currency,
        "amount" => $amount,
        "billing_name" => "$userFirstName $userLastName",
        "first_name" => $userFirstName,
        "last_name" => $userLastName,
        "date_of_birth" => $userDateOfBirth,
        "email" => $userEmail,
        "phone" => $userPhone,
        "title" => $userTitle,
        "gender" => $userGender,
        "bvn" => $userBVN, // Include the BVN parameter
        "callback_url" => $callbackUrl,
    ];

// Initialize cURL session
$curl = curl_init();

// Set cURL options
curl_setopt_array($curl, [
    CURLOPT_URL => 'https://api.flutterwave.com/v3/virtual-cards',
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_ENCODING => '',
    CURLOPT_MAXREDIRS => 10,
    CURLOPT_TIMEOUT => 0,
    CURLOPT_FOLLOWLOCATION => true,
    CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    CURLOPT_CUSTOMREQUEST => 'POST',
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'Authorization: Bearer ' . $flutterwaveSecretKey
    ],
]);

// Execute cURL and get the response
$response = curl_exec($curl);
dd($response);

// Close cURL session
curl_close($curl);

});

