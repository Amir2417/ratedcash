<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\VirtualAccountService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class VirtualAccountServiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $virtual_account_service = array(
            array('admin_id' => '1','image' => 'seeder/account-service.webp','card_details' => 'This card is property of QRPay, Wonderland. Misuse is criminal offence. If found, please return to QRPay or to the nearest bank.','config' => '{"username":"ratedartisan","password":"r*!hRf+H+AIwWyJ9XKbbJa&8x)XQm##e**$$cpheq2fzbr2^qvcZUwkV","clientId":"waas","clientSecret":"cRAwnWElcNMUZpALdnlve6PubUkCPOQR","publickey":"31B24088687A4401924E50133FDA7FF8","privatekey":"R4lXf-oB-d429kIV_3b_O8Fxaa1_H1M95Oq3d4MiEgfAYfUq3f1PescDHI5AzTnH","apiKey":"RARTSN_TEST_cENQSM0CPY5LUyB","secretKey":"pmuBPBPK22hoXxlQrHk9BUmJBXWhAGSO","service_url":"https://baastest.9psb.com.ng/iva-api/v1/merchant/virtualaccount/","name":"9psb"}','created_at' => now(),'updated_at' => now())
          );
        VirtualAccountService::insert($virtual_account_service);
    }
}
