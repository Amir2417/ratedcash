<?php

namespace Database\Seeders\User;

use App\Models\Receipient;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RecipientSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $receipients = array(
            array('user_id' => '1','bank_name' => 'Access Bank','bank_code' => '044','account_name' => 'Forrest Green','account_number' => '0690000031','created_at' => '2024-01-23 13:16:30','updated_at' => '2024-01-23 13:16:30'),
            array('user_id' => '1','bank_name' => 'Access Bank','bank_code' => '044','account_name' => 'Pastor Bright','account_number' => '0690000032','created_at' => '2024-01-24 09:42:32','updated_at' => '2024-01-24 09:42:32'),
            array('user_id' => '1','bank_name' => 'Access Bank','bank_code' => '044','account_name' => 'Bale Gary','account_number' => '0690000033','created_at' => '2024-01-24 09:42:50','updated_at' => '2024-01-24 09:42:50'),
            array('user_id' => '1','bank_name' => 'Access Bank','bank_code' => '044','account_name' => 'Ade Bond','account_number' => '0690000034','created_at' => '2024-01-24 09:43:09','updated_at' => '2024-01-24 09:43:09')
        );
        Receipient::insert($receipients);
    }
}
