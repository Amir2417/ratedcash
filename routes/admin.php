<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Providers\Admin\BasicSettingsProvider;
use Pusher\PushNotifications\PushNotifications;
use App\Http\Controllers\Admin\CookieController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\AddMoneyController;
use App\Http\Controllers\Admin\CurrencyController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\MoneyOutController;
use App\Http\Controllers\Admin\SetupKycController;
use App\Http\Controllers\Admin\SetupSmsController;
use App\Http\Controllers\Admin\UserCareController;
use App\Http\Controllers\Admin\AdminCareController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\RemitanceController;
use App\Http\Controllers\Admin\ExtensionsController;
use App\Http\Controllers\Admin\GatewayApiController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\ProfitLogsController;
use App\Http\Controllers\Admin\ServerInfoController;
use App\Http\Controllers\Admin\SetupEmailController;
use App\Http\Controllers\Admin\SetupPagesController;
use App\Http\Controllers\Admin\UsefulLInkController;
use App\Http\Controllers\Admin\AppSettingsController;
use App\Http\Controllers\Admin\CryptoAssetController;
use App\Http\Controllers\Admin\PaymentLinkController;
use App\Http\Controllers\Admin\TrxSettingsController;
use App\Http\Controllers\Admin\VirtualCardController;
use App\Http\Controllers\Admin\WebSettingsController;
use App\Http\Controllers\Admin\BroadcastingController;
use App\Http\Controllers\Admin\MerchantCareController;
use App\Http\Controllers\Admin\SetupBillPayController;
use App\Http\Controllers\Admin\ModuleSettingController;
use App\Http\Controllers\Admin\SetupSectionsController;
use App\Http\Controllers\Admin\SupportTicketController;
use App\Http\Controllers\Admin\ContactMessageController;
use App\Http\Controllers\Admin\PaymentGatewaysController;
use App\Http\Controllers\Admin\PushNotificationController;
use App\Http\Controllers\Admin\SetupMobileTopupController;
use App\Http\Controllers\Admin\AppOnboardScreensController;
use App\Http\Controllers\Admin\PaymentGatewayCurrencyController;

// All Admin Route Is Here
Route::prefix('admin')->name('admin.')->group(function () {

    // Dashboard Section
    Route::controller(DashboardController::class)->group(function () {
        Route::get('dashboard', 'index')->name('dashboard');
        Route::post('logout', 'logout')->name('logout');
        Route::post('notifications/clear','notificationsClear')->name('notifications.clear');
    });

    // Admin Profile
    Route::controller(ProfileController::class)->prefix('profile')->name('profile.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::get('change-password', 'changePassword')->name('change.password');
        Route::put('change-password', 'updatePassword')->name('change.password.update');
        Route::put('update', 'update')->name('update');
    });

    // Setup Currency Section
    Route::controller(CurrencyController::class)->prefix('currency')->name('currency.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::post('store', 'store')->name('store');
        Route::put('status/update', 'statusUpdate')->name('status.update');
        Route::put('update', 'update')->name('update');
        Route::delete('delete','delete')->name('delete');
        Route::post('search','search')->name("search");
    });

    // Fees & Charges Section
    Route::controller(TrxSettingsController::class)->prefix('trx-settings')->name('trx.settings.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::put('charges/update', 'trxChargeUpdate')->name('charges.update');
    });
    // virtual card api
    Route::controller(VirtualCardController::class)->prefix('virtual-card')->name('virtual.card.')->group(function () {
        Route::get('api/settings', 'cardApi')->name('api');
        Route::put('api/update', 'cardApiUpdate')->name('api.update');
    });
    //PayLink Link Api
    Route::controller(GatewayApiController::class)->prefix('gateway-api')->name('gateway.api.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::put('update', 'update')->name('update');
    });

    // Remittance Logs
    Route::controller(RemitanceController::class)->prefix('remitance')->name('remitance.')->group(function () {
        //receiver countries
        Route::get('countries', 'allCountries')->name('countries');
        Route::post('country/store', 'storeCountry')->name('country.store');
        Route::put('country/update', 'updateCountry')->name('country.update');
        Route::delete('countries/delete','deleteCountry')->name('country.delete');
        Route::put('countries/status/update', 'statusUpdateCountry')->name('country.status.update');
        Route::post('countries/search','searchCountry')->name("country.search");
        //Bank Deposits
        Route::prefix('bank-deposit')->name('bank.deposit.')->group(function () {
            Route::get('/', 'bankDeposits')->name('index');
            Route::post('store', 'storeBankDeposit')->name('store');
            Route::put('status/update','bankDepositStatusUpdate')->name('status.update');
            Route::put('update', 'bankDepositUpdate')->name('update');
            Route::delete('delete','bankDepositDelete')->name('delete');
            Route::post('search','bankDepositSearch')->name("search");
        });
        //Cash pickup
        Route::prefix('cash-pickup')->name('cash.pickup.')->group(function () {
            Route::get('/', 'cashPickup')->name('index');
            Route::post('store', 'storeCashPickup')->name('store');
            Route::put('status/update','cashPickuptatusUpdate')->name('status.update');
            Route::put('update', 'cashPickupUpdate')->name('update');
            Route::delete('delete','cashPickuptDelete')->name('delete');
            Route::post('search','cashPickupSearch')->name("search");
        });
        //remittance logs
        Route::get('index', 'index')->name('index');
        Route::get('pending', 'pending')->name('pending');
        Route::get('complete', 'complete')->name('complete');
        Route::get('canceled', 'canceled')->name('canceled');
        Route::get('details/{id}', 'addMoneyDetails')->name('details');
        Route::put('approved', 'approved')->name('approved');
        Route::put('rejected', 'rejected')->name('rejected');
        Route::get('export-data', 'exportData')->name('export.data');

    });
    // Add Money Logs
    Route::controller(AddMoneyController::class)->prefix('add-money')->name('add.money.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::get('pending', 'pending')->name('pending');
        Route::get('complete', 'complete')->name('complete');
        Route::get('canceled', 'canceled')->name('canceled');
        Route::get('details/{id}', 'addMoneyDetails')->name('details');
        Route::put('approved', 'approved')->name('approved');
        Route::put('rejected', 'rejected')->name('rejected');
        Route::get('export-data', 'exportData')->name('export.data');
    });

    // Money Out Logs
    Route::controller(MoneyOutController::class)->prefix('money-out')->name('money.out.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::get('pending', 'pending')->name('pending');
        Route::get('complete', 'complete')->name('complete');
        Route::get('canceled', 'canceled')->name('canceled');
        Route::get('details/{id}', 'moneyOutDetails')->name('details');
        Route::put('approved', 'approved')->name('approved');
        Route::put('rejected', 'rejected')->name('rejected');
        Route::get('export-data', 'exportData')->name('export.data');
    });
    // Bill Pay Logs
    Route::controller(SetupBillPayController::class)->prefix('bill-pay')->name('bill.pay.')->group(function () {
        //manage bill category
        Route::get('category/index', 'billPayList')->name('category.index');
        Route::post('category/store', 'storeCategory')->name('category.store');
        Route::put('category/status/update','categoryStatusUpdate')->name('category.status.update');
        Route::put('category/update', 'categoryUpdate')->name('category.update');
        Route::delete('category/delete','categoryDelete')->name('category.delete');
        Route::post('category/search','categorySearch')->name("category.search");
        //logs
        Route::get('index', 'index')->name('index');
        Route::get('pending', 'pending')->name('pending');
        Route::get('complete', 'complete')->name('complete');
        Route::get('canceled', 'canceled')->name('canceled');
        Route::get('details/{id}', 'details')->name('details');
        Route::put('approved', 'approved')->name('approved');
        Route::put('rejected', 'rejected')->name('rejected');
        Route::get('export-data', 'exportData')->name('export.data');
    });
    // Mobile Topup Logs
    Route::controller(SetupMobileTopupController::class)->prefix('mobile-topup')->name('mobile.topup.')->group(function () {
        //manage bill category
        Route::get('category/index', 'topUpcategories')->name('category.index');
        Route::post('category/store', 'storeCategory')->name('category.store');
        Route::put('category/status/update','categoryStatusUpdate')->name('category.status.update');
        Route::put('category/update', 'categoryUpdate')->name('category.update');
        Route::delete('category/delete','categoryDelete')->name('category.delete');
        Route::post('category/search','categorySearch')->name("category.search");
        //logs
        Route::get('index', 'index')->name('index');
        Route::get('pending', 'pending')->name('pending');
        Route::get('complete', 'complete')->name('complete');
        Route::get('canceled', 'canceled')->name('canceled');
        Route::get('details/{id}', 'details')->name('details');
        Route::put('approved', 'approved')->name('approved');
        Route::put('rejected', 'rejected')->name('rejected');
        Route::get('export-data', 'exportData')->name('export.data');
    });
    // Payment Link Logs
    Route::controller(PaymentLinkController::class)->prefix('payment-link')->name('payment.link.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::get('all-link', 'allLink')->name('all.link');
        Route::get('active-link', 'activeLink')->name('active.link');
        Route::get('closed-link', 'closedLink')->name('closed.link');
        Route::get('details/{id}', 'details')->name('details');
        Route::get('export-data', 'exportData')->name('export.data');
    });
    // Admin Profit Logs
    Route::controller(ProfitLogsController::class)->prefix('profit-logs')->name('profit.logs.')->group(function () {
        Route::get('index', 'profitLogs')->name('index');
    });

    // User Care Section
    Route::controller(UserCareController::class)->prefix('users')->name('users.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::get('active', 'active')->name('active');
        Route::get('banned', 'banned')->name('banned');
        Route::get('email/unverified', 'emailUnverified')->name('email.unverified');
        Route::get('sms/unverified', 'SmsUnverified')->name('sms.unverified');
        Route::get('kyc/unverified', 'KycUnverified')->name('kyc.unverified');
        Route::get('kyc/details/{username}', 'kycDetails')->name('kyc.details');
        Route::get('email-user', 'emailAllUsers')->name('email.users');
        Route::post('email-users/send', 'sendMailUsers')->name('email.users.send')->middleware("mail");
        Route::get('details/{username}', 'userDetails')->name('details');
        Route::post('details/update/{username}', 'userDetailsUpdate')->name('details.update');
        Route::get('login/logs/{username}', 'loginLogs')->name('login.logs');
        Route::get('mail/logs/{username}', 'mailLogs')->name('mail.logs');
        Route::post('send/mail/{username}', 'sendMail')->name('send.mail')->middleware("mail");
        Route::post('login-as-member/{username?}','loginAsMember')->name('login.as.member');
        Route::post('kyc/approve/{username}','kycApprove')->name('kyc.approve');
        Route::post('kyc/reject/{username}','kycReject')->name('kyc.reject');
        Route::post('search','search')->name('search');
        Route::post('wallet/balance/update/{username}','walletBalanceUpdate')->name('wallet.balance.update');
    });

    // Merchant Care Section
    Route::controller(MerchantCareController::class)->prefix('merchants')->name('merchants.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::get('active', 'active')->name('active');
        Route::get('banned', 'banned')->name('banned');
        Route::get('email/unverified', 'emailUnverified')->name('email.unverified');
        Route::get('sms/unverified', 'SmsUnverified')->name('sms.unverified');
        Route::get('kyc/unverified', 'KycUnverified')->name('kyc.unverified');
        Route::get('kyc/details/{username}', 'kycDetails')->name('kyc.details');
        Route::get('email-agents', 'emailAllUsers')->name('email.merchants');
        Route::post('email-agents/send', 'sendMailUsers')->name('email.merchants.send')->middleware("mail");
        Route::get('details/{username}', 'userDetails')->name('details');
        Route::post('details/update/{username}', 'userDetailsUpdate')->name('details.update');
        Route::get('login/logs/{username}', 'loginLogs')->name('login.logs');
        Route::get('mail/logs/{username}', 'mailLogs')->name('mail.logs');
        Route::post('send/mail/{username}', 'sendMail')->name('send.mail')->middleware("mail");
        Route::post('login-as-member/{username?}','loginAsMember')->name('login.as.member');
        Route::post('kyc/approve/{username}','kycApprove')->name('kyc.approve');
        Route::post('kyc/reject/{username}','kycReject')->name('kyc.reject');
        Route::post('search','search')->name('search');
        Route::post('wallet/balance/update/{username}','walletBalanceUpdate')->name('wallet.balance.update');
    });


    // Admin Care Section
    Route::controller(AdminCareController::class)->prefix('admins')->name('admins.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::get('email-admin', 'emailAllAdmins')->name('email.admins');
        Route::delete('admin/delete','deleteAdmin')->name('admin.delete')->middleware('admin.delete.guard');
        Route::post('send/email','sendEmail')->name('send.email')->middleware("mail");
        Route::post('admin/search','adminSearch')->name('search');

        Route::post("store","store")->name("admin.store");
        Route::put("update","update")->name("admin.update");
        Route::put('status/update','statusUpdate')->name('admin.status.update');

        Route::get('role/index','roleIndex')->name('role.index');
        Route::post('role/store','roleStore')->name('role.store');
        Route::put('role/update','roleUpdate')->name('role.update');
        Route::delete('role/remove','roleRemove')->name('role.delete')->middleware('admin.role.delete.guard');

        Route::get('role/permission/index','rolePermissionIndex')->name('role.permission.index');
        Route::post('role/permission/store','rolePermissionStore')->name('role.permission.store');
        Route::put('role/permission/update','rolePermissionUpdate')->name('role.permission.update');
        Route::delete('role/permission/delete','rolePermissionDelete')->name('role.permission.dalete');
        Route::delete('role/permission/assign/delete/{slug}','rolePermissionAssignDelete')->name('role.permission.assign.delete');

        Route::get('role/permission/{slug}','viewRolePermission')->name('role.permission');
        Route::post('role/permission/assign/{slug}','rolePermissionAssign')->name('role.permission.assign');
    });


    // Web Settings Section
    Route::controller(WebSettingsController::class)->prefix('web-settings')->name('web.settings.')->group(function(){
        Route::get('basic-settings','basicSettings')->name('basic.settings');
        Route::put('basic-settings/update','basicSettingsUpdate')->name('basic.settings.update');
        Route::put('basic-settings/activation/update','basicSettingsActivationUpdate')->name('basic.settings.activation.update');
        Route::get('image-assets','imageAssets')->name('image.assets');
        Route::put('image-assets/update','imageAssetsUpdate')->name('image.assets.update');
        Route::get('setup-seo','setupSeo')->name('setup.seo');
        Route::put('setup-seo/update','setupSeoUpdate')->name('setup.seo.update');
    });


    // App Settings Section
    Route::prefix('app-settings')->name('app.settings.')->group(function () {
        Route::controller(AppSettingsController::class)->group(function () {
            Route::get('splash-screen', 'splashScreen')->name('splash.screen');
            Route::put('splash-screen/update', 'splashScreenUpdate')->name('splash.screen.update');
            Route::get('urls', 'urls')->name('urls');
            Route::put('urls/update', 'urlsUpdate')->name('urls.update');
        });

        Route::controller(AppOnboardScreensController::class)->name('onboard.')->group(function () {
            Route::get('onboard-screens', 'onboardScreens')->name('screens');
            Route::post('onboard-screens/store', 'onboardScreenStore')->name('screen.store');
            Route::put('onboard-screen/update', 'onboardScreenUpdate')->name('screen.update');
            Route::put('onboard-screen/status/update', 'onboardScreenStatusUpdate')->name('screen.status.update');
            Route::delete('onboard-screen/delete','onboardScreenDelete')->name('screen.delete');
        });
    });

    // Language Section
    Route::controller(LanguageController::class)->prefix('languages')->name('languages.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::post('store','store')->name('store');
        Route::put('update','update')->name('update');
        Route::put('status/update','statusUpdate')->name('status.update');
        Route::get('info/{code}','info')->name('info');
        Route::post('import','import')->name('import');
        Route::delete('delete','delete')->name('delete');
        Route::post('switch','switch')->name('switch');
        Route::get('download','download')->name('download');
    });


    // Setup Email Section
    Route::controller(SetupEmailController::class)->prefix('setup-email')->name('setup.email.')->group(function () {
        Route::get('config', 'configuration')->name('config');
        // Route::get('template/default', 'defaultTemplate')->name('template.default');
        Route::put('config/update', 'update')->name('config.update');
        Route::post('test-mail/send','sendTestMail')->name('test.mail.send')->middleware('mail');
    });

    // Setup SMS Section
    Route::controller(SetupSmsController::class)->prefix('setup-sms')->name('setup.sms.')->group(function () {
        Route::get('config', 'configuration')->name('config');
        Route::post('config/update', 'update')->name('update');
        Route::post('test-code/send','sendTestSMS')->name('test.code.send');
    });

    // Setup Module Setting Options
    Route::controller(ModuleSettingController::class)->prefix('module-setting')->name('module.setting.')->group(function () {
        Route::get('', 'index')->name('index');
        Route::put('status/update', 'statusUpdate')->name('status.update');
    });


    // Setup KYC Section
    Route::controller(SetupKycController::class)->prefix('setup-kyc')->name('setup.kyc.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::get('edit/{slug}', 'edit')->name('edit');
        Route::put('update/{slug}', 'update')->name('update');
        Route::put('status/update', 'statusUpdate')->name('status.update');
    });


    // Setup Section
    Route::controller(SetupSectionsController::class)->prefix('setup-sections')->name('setup.sections.')->group(function () {
        Route::get('{slug}', 'sectionView')->name('section');
        Route::post('update/{slug}','sectionUpdate')->name('section.update');
        Route::post('item/store/{slug}','sectionItemStore')->name('section.item.store');
        Route::post('item/update/{slug}','sectionItemUpdate')->name('section.item.update');
        Route::delete('item/delete/{slug}','sectionItemDelete')->name('section.item.delete');
         //Setup Blog Category Type
         Route::post('category/store', 'storeCategory')->name('category.store');
         Route::put('category/status/update','categoryStatusUpdate')->name('category.status.update');
         Route::put('category/update', 'categoryUpdate')->name('category.update');
         Route::delete('category/delete','categoryDelete')->name('category.delete');
         Route::post('category/search','categorySearch')->name("category.search");
         //Setup Blog Section
         Route::post('blog/store', 'blogItemStore')->name('blog.store');
         Route::put('blog/status/update','blogStatusUpdate')->name('blog.status.update');
         Route::get('blog/edit/{id}','blogEdit')->name('blog.edit');
         Route::put('blog/update', 'blogItemUpdate')->name('blog.update');
         Route::delete('blog/delete','blogItemDelete')->name('blog.delete');
    });

    // Setup Pages Controller
    Route::controller(UsefulLInkController::class)->prefix('useful-links')->name('useful.links.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::post('store', 'store')->name('store');
        Route::get('edit/{id}', 'edit')->name('edit');
        Route::post('update', 'update')->name('update');
        Route::put('status/update','statusUpdate')->name('status.update');
        Route::delete('delete','delete')->name('delete');
    });
    // Setup Pages Controller
    Route::controller(SetupPagesController::class)->prefix('setup-pages')->name('setup.pages.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::put('status/update','statusUpdate')->name('status.update');
    });


    // Extensions Section
    Route::controller(ExtensionsController::class)->prefix('extensions')->name('extensions.')->group(function () {
        Route::get('index', 'index')->name('index');
    });

    // Payment Method Section
    Route::prefix('payment-gateways')->name('payment.gateway.')->group(function () {

        Route::controller(PaymentGatewaysController::class)->group(function () {
            Route::get('{slug}/{type}/create', 'paymentGatewayCreate')->name('create')->whereIn('type', ['automatic', 'manual']);
            Route::post('{slug}/{type}', 'paymentGatewayStore')->name('store')->whereIn('type', ['automatic', 'manual']);
            Route::get('{slug}/{type}', 'paymentGatewayView')->name('view')->whereIn('type', ['automatic', 'manual']); // View Gateway Index Page
            Route::get('{slug}/{type}/{alias}', 'paymentGatewayEdit')->name('edit')->whereIn('type', ['automatic', 'manual']);
            Route::put('{slug}/{type}/{alias}', 'paymentGatewayUpdate')->name('update')->whereIn('type', ['automatic', 'manual']);
            Route::put('status/update', 'paymentGatewayStatusUpdate')->name('status.update');
            Route::delete('remove', 'remove')->name('remove');
        });

        Route::controller(PaymentGatewayCurrencyController::class)->group(function () {
            Route::delete('currency/remove', 'paymentGatewayCurrencyRemove')->name('currency.remove');
        });
    });


    // Push Notification Setup Section
    Route::controller(PushNotificationController::class)->prefix('push-notification')->name('push.notification.')->group(function(){
        Route::get('config','configuration')->name('config');
        Route::put('update','update')->name('update');

        Route::get('/','index')->name('index');
        Route::post('send','send')->name('send');
    });


    // Broadcasting Setup Section
    Route::controller(BroadcastingController::class)->prefix('broadcast')->name('broadcast.')->group(function(){
        Route::put("config/update","configUpdate")->name('config.update');
    });


    //  Newsletter  Section
    Route::controller(NewsletterController::class)->prefix('newsletter')->name('newsletter.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::delete('delete', 'delete')->name('delete');
        Route::post('search','search')->name("search");
        Route::post('send/email','sendMail')->name("send.email");
    });
     // Contact Message
     Route::controller(ContactMessageController::class)->prefix('contact-messages')->name('contact.messages.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::delete('delete', 'delete')->name('delete');
        Route::post('email/send', 'emailSend')->name('email.send');
    });
    //  GDPR Cookie Section
    Route::controller(CookieController::class)->prefix('cookie')->name('cookie.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::put('update', 'update')->name('update');
    });

    // Server Info Section
    Route::controller(ServerInfoController::class)->prefix('server-info')->name('server.info.')->group(function () {
        Route::get('index', 'index')->name('index');
    });

    // Support Ticked Section
    Route::controller(SupportTicketController::class)->prefix('support-ticket')->name('support.ticket.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::get('active', 'active')->name('active');
        Route::get('pending', 'pending')->name('pending');
        Route::get('solved', 'solved')->name('solved');
        Route::get('conversation/{ticket_id}', 'conversation')->name('conversation');
        Route::post('message/reply','messageReply')->name('messaage.reply');
        Route::post('solve','solve')->name('solve');
    });

    // Extension Section
    Route::controller(ExtensionsController::class)->prefix('extension')->name('extension.')->group(function () {
        Route::get('index', 'index')->name('index');
        Route::post('update/{id}', 'update')->name('update');
        Route::put('status/update', 'statusUpdate')->name('status.update');
    });

    // Cache Clear Section
    Route::get('cache/clear', function () {
        Artisan::all('cache:clear');
        Artisan::all('route:clear');
        Artisan::all('view:clear');
        Artisan::all('config:clear');
        return redirect()->back()->with(['success' => ['Cache Clear Successfully!']]);
    })->name('cache.clear');
    //crypto assets
    Route::controller(CryptoAssetController::class)->prefix('crypto/assets')->name('crypto.assets.')->group(function() {
        Route::get('gateway/{alias}','gatewayAssets')->name('gateway.index');
        Route::get('gateway/{alias}/generate/wallet','generateWallet')->name('generate.wallet');

        Route::get('wallet/balance/update/{crypto_asset_id}/{wallet_id}','walletBalanceUpdate')->name('wallet.balance.update');
        Route::post('wallet/store','walletStore')->name("wallet.store");
        Route::delete('wallet/delete','walletDelete')->name('wallet.delete');
        Route::put('wallet/status/update','walletStatusUpdate')->name('wallet.status.update');
        Route::get('wallet/transactions/{crypto_asset_id}/{wallet_id}','walletTransactions')->name('wallet.transactions');
        Route::post('wallet/transactions/search/{crypto_asset_id}/{wallet_id}','walletTransactionSearch')->name('wallet.transaction.search');
    });
});

Route::get('pusher/beams-auth', function (Request $request) {
    if(Auth::check() == false) {
        return response(['Inconsistent request'], 401);
    }
    $userID = Auth::user()->id;

    $basic_settings = BasicSettingsProvider::get();
    if(!$basic_settings) {
        return response('Basic setting not found!', 404);
    }

    $notification_config = $basic_settings->push_notification_config;

    if(!$notification_config) {
        return response('Notification configuration not found!', 404);
    }

    $instance_id    = $notification_config->instance_id ?? null;
    $primary_key    = $notification_config->primary_key ?? null;
    if($instance_id == null || $primary_key == null) {
        return response('Sorry! You have to configure first to send push notification.', 404);
    }
    $beamsClient = new PushNotifications(
        array(
            "instanceId" => $notification_config->instance_id,
            "secretKey" => $notification_config->primary_key,
        )
    );
    $publisherUserId = "admin-".$userID;
    try{
        $beamsToken = $beamsClient->generateToken($publisherUserId);
    }catch(Exception $e) {
        return response(['Server Error. Faild to generate beams token.'], 500);
    }

    return response()->json($beamsToken);
})->name('pusher.beams.auth');
