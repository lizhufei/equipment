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
                'name' => '深云设备',
                'mark' => 'shengyun',
                'created_at' => $date,
                'updated_at' => $date,
            ],
            [
                'name' => '巨龙设备',
                'mark' => 'julong',
                'created_at' => $date,
                'updated_at' => $date,
            ],
            [
                'name' => '海清设备',
                'mark' => 'haiqing',
                'created_at' => $date,
                'updated_at' => $date,
            ],
            [
                'name' => '中铁停车',
                'mark' => 's1206',
                'created_at' => $date,
                'updated_at' => $date,
            ],
        ]);
    }
}
