<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\BasicSettings;
use Illuminate\Database\Seeder;

class BasicSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            'site_name'         => "QRPAY",
            'site_title'        => "Money Transfer with QR Code",
            'base_color'        => "#0C56DB",
            'web_version'       => "3.2.0",
            'secondary_color'   => "#000400",
            'otp_exp_seconds'   => "600",
            'timezone'          => "Asia/Dhaka",
            'broadcast_config'  => [
                "method" => "pusher",
                "app_id" => "1539602",
                "primary_key" => "39079c30de823f783dbe",
                "secret_key" => "78b81e5e7e0357aee3df",
                "cluster" => "ap2"
            ],
            'push_notification_config'  => [
                "method" => "pusher",
                "instance_id" => "809313fc-1f5c-4d0b-90bc-1c6751b83bbd",
                "primary_key" => "58C901DC107584D2F1B78E6077889F1C591E2BC39E9F5C00B4362EC9C642F03F"
            ],
            'kyc_verification'  => true,
            'mail_config'       => [
                "method" => "smtp",
                "host" => "appdevs.net",
                "port" => "465",
                "encryption" => "ssl",
                "username" => "system@appdevs.net",
                "password" => "QP2fsLk?80Ac",
                "from" => "system@appdevs.net",
                "app_name" => "QRPAY",
            ],
            'email_verification'    => false,
            'user_registration'     => true,
            'agree_policy'          => true,
            'email_notification'    => true,
            'sms_verification'      => true,
            'sms_notification'      => true,
            'site_logo_dark'        => "seeder/logo-white.png",
            'site_logo'             => "seeder/logo-dark.png",
            'site_fav_dark'         => "seeder/favicon-dark.png",
            'site_fav'              => "seeder/favicon-white.png",
            'sms_config'       => [
                "name" => "twilio",
                "account_sid" => "ACe719845f849a3b117fee55e9069aedd4",
                "auth_token" => "3cc661cb2cb04241fb91b1c493b57eca",
                "from" => "+16592254223",
            ],
            'sms_api'       => "hi {{name}}, {{message}}"
        ];
        BasicSettings::truncate();
        BasicSettings::firstOrCreate($data);
    }
}
