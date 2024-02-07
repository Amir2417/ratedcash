<?php

namespace Database\Seeders\Admin;

use App\Models\Admin\AppOnboardScreens;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OnboardScreenSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $app_onboard_screens = array(
            array('id' => '1','title' => 'Welcome to RatedCash','sub_title' => 'Managing your money is about to get a lot better.
            ','image' => 'seeder/onboard1.png','status' => '1','last_edit_by' => '1','created_at' => '2023-05-01 16:33:41','updated_at' => '2023-06-11 12:36:42'),
            array('id' => '2','title' => 'Budgeting','sub_title' => 'Spend smarter every day, all from one app.','image' => 'seeder/onboard2.png','status' => '1','last_edit_by' => '1','created_at' => '2023-05-01 16:34:33','updated_at' => '2023-06-11 12:36:58'),
          );
        AppOnboardScreens::insert($app_onboard_screens);
    }
}
