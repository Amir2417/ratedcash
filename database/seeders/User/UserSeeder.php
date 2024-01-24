<?php

namespace Database\Seeders\User;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        
        $data = [
            [
                'firstname'         => "Pastor",
                'lastname'          => "Bright",
                'email'             => "user@appdevs.net",
                'username'          => "appdevs",
                'mobile_code'       => "880",
                'mobile'            => "1333333333",
                'full_mobile'       => "8801333333333",
                'status'            => true,
                'password'          => Hash::make("appdevs"),
                'address'           => '{"country":"Bangladesh","city":"Dhaka","zip":"1230","state":"Dhaka","address":"Dhaka, Bangladesh"}',
                'email_verified'    => true,
                'sms_verified'      => true,
                'kyc_verified'      => true,
                'pin_status'      => true,
                'pin_code'      => Hash::make(1234),
                'created_at'        => now(),
                'updated_at'        => now(),
            ],
        ];

        User::insert($data);
    }
}
