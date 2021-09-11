<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeviceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $date = date('Y-m-d H:i:s');
        //清空数据库
        DB::statement('TRUNCATE TABLE `manufactures`');
        DB::table('manufactures')->insert([
            [
                'name' => 'SY',
                'mark' => 'shengyun',
                'created_at' => $date,
                'updated_at' => $date,
            ],
            [
                'name' => 'SY_V1',
                'mark' => 'shengyun_v1',
                'created_at' => $date,
                'updated_at' => $date,
            ],
            [
                'name' => 'JL',
                'mark' => 'julong',
                'created_at' => $date,
                'updated_at' => $date,
            ],
            [
                'name' => 'HQ',
                'mark' => 'haiqing',
                'created_at' => $date,
                'updated_at' => $date,
            ]
        ]);
    }
}
