<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Database\Seeders\User\UserSeeder;
use Database\Seeders\Admin\BlogSeeder;
use Database\Seeders\Admin\CashPickup;
use Database\Seeders\Admin\RoleSeeder;
use Database\Seeders\Admin\AdminSeeder;
use Database\Seeders\Admin\TopupSeeder;
use Database\Seeders\Admin\BankTransfer;
use Database\Seeders\Admin\CurrencySeeder;
use Database\Seeders\Admin\LanguageSeeder;
use Database\Seeders\Admin\SetupKycSeeder;
use Database\Seeders\Admin\SetupSeoSeeder;
use Database\Seeders\User\RecipientSeeder;
use Database\Seeders\Admin\ExtensionSeeder;
use Database\Seeders\Admin\ReceiverCountry;
use Database\Seeders\Admin\SetupPageSeeder;
use Database\Seeders\User\UserWalletSeeder;
use Database\Seeders\Admin\GatewayApiSeeder;
use Database\Seeders\Admin\SetupEmailSeeder;
use Database\Seeders\Admin\VirtualApiSeeder;
use Database\Seeders\Admin\AppSettingsSeeder;
use Database\Seeders\Admin\SmsTemplateSeeder;
use Database\Seeders\Merchant\MerchantSeeder;
use Database\Seeders\Admin\AdminHasRoleSeeder;
use Database\Seeders\Admin\SiteSectionsSeeder;
use Database\Seeders\Admin\BasicSettingsSeeder;
use Database\Seeders\Admin\ModuleSettingSeeder;
use Database\Seeders\Admin\OnboardScreenSeeder;
use Database\Seeders\Admin\PaymentGatewaySeeder;
use Database\Seeders\Admin\BillPayCategorySeeder;
use Database\Seeders\Merchant\ApiCredentialsSeeder;
use Database\Seeders\Merchant\MerchantWalletSeeder;
use Database\Seeders\Admin\TransactionSettingSeeder;
use Database\Seeders\Admin\MerchantConfigurationSeeder;
use Database\Seeders\Admin\VirtualAccountServiceSeeder;
use Database\Seeders\Fresh\ExtensionSeeder as FreshExtensionSeeder;
use Database\Seeders\Fresh\BasicSettingsSeeder as FreshBasicSettingsSeeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        //demo
        $this->call([
            AdminSeeder::class,
            RoleSeeder::class,
            TransactionSettingSeeder::class,
            CurrencySeeder::class,
            BasicSettingsSeeder::class,
            BillPayCategorySeeder::class,
            TopupSeeder::class,
            LanguageSeeder::class,
            PaymentGatewaySeeder::class,
            SetupSeoSeeder::class,
            AppSettingsSeeder::class,
            OnboardScreenSeeder::class,
            SiteSectionsSeeder::class,
            SetupKycSeeder::class,
            ExtensionSeeder::class,
            BlogSeeder::class,
            BankTransfer::class,
            CashPickup::class,
            ReceiverCountry::class,
            AdminHasRoleSeeder::class,
            SetupPageSeeder::class,
            VirtualApiSeeder::class,
            SetupEmailSeeder::class,
            MerchantConfigurationSeeder::class,
            ModuleSettingSeeder::class,
            GatewayApiSeeder::class,
            SmsTemplateSeeder::class,
            //user
            UserSeeder::class,
            UserWalletSeeder::class,
            RecipientSeeder::class,
            //merchant
            MerchantSeeder::class,
            MerchantWalletSeeder::class,
            ApiCredentialsSeeder::class,
        ]);


        //fresh
        // $this->call([
        //     AdminSeeder::class,
        //     RoleSeeder::class,
        //     TransactionSettingSeeder::class,
        //     CurrencySeeder::class,
        //     FreshBasicSettingsSeeder::class,
        //     BillPayCategorySeeder::class,
        //     TopupSeeder::class,
        //     LanguageSeeder::class,
        //     PaymentGatewaySeeder::class,
        //     SetupSeoSeeder::class,
        //     AppSettingsSeeder::class,
        //     OnboardScreenSeeder::class,
        //     SiteSectionsSeeder::class,
        //     SetupKycSeeder::class,
        //     FreshExtensionSeeder::class,
        //     BlogSeeder::class,
        //     BankTransfer::class,
        //     CashPickup::class,
        //     ReceiverCountry::class,
        //     AdminHasRoleSeeder::class,
        //     SetupPageSeeder::class,
        //     VirtualApiSeeder::class,
        //     MerchantConfigurationSeeder::class,
        //     ModuleSettingSeeder::class,
        //     GatewayApiSeeder::class,
        //     //merchant
        //     MerchantSeeder::class,
        //     MerchantWalletSeeder::class,
        //     ApiCredentialsSeeder::class,
        // ]);
    }
}
