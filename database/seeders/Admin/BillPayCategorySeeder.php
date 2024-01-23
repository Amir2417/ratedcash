<?php

namespace Database\Seeders\Admin;

use App\Models\BillPayCategory;
use Illuminate\Database\Seeder;

class BillPayCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $bill_pay_categories = array(
            array('admin_id' => '1','name' => 'Power','slug' => 'power','status' => '1','created_at' => '2024-01-23 11:35:12','updated_at' => '2024-01-23 11:35:12'),
            array('admin_id' => '1','name' => 'Internet','slug' => 'internet','status' => '1','created_at' => '2024-01-23 11:35:22','updated_at' => '2024-01-23 11:35:22'),
            array('admin_id' => '1','name' => 'Cable','slug' => 'cable','status' => '1','created_at' => '2024-01-23 11:35:33','updated_at' => '2024-01-23 11:35:33'),
            array('admin_id' => '1','name' => 'Airtime','slug' => 'airtime','status' => '1','created_at' => '2024-01-23 11:35:41','updated_at' => '2024-01-23 11:35:41'),
            array('admin_id' => '1','name' => 'Toll','slug' => 'toll','status' => '1','created_at' => '2024-01-23 11:35:48','updated_at' => '2024-01-23 11:35:48')
        );
        BillPayCategory::insert($bill_pay_categories);
    }
}
