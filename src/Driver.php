<?php

namespace Hsvisus\Equipment;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class Driver
{
    private $drive_path = 'Hsvisus\Equipment\Drives\\';
    /**
     * 更新在线状态
     * @param string $table
     * @param $sn
     * @return int
     */
    public function updateOnline(string $table, $sn)
    {
        return DB::table($table)->where('device_sn', $sn)
            ->update([
                'last_heartbeat_at' => date('Y-m-d H:i:s')
            ]);
    }
    /**
     * 获取设备驱动对象
     * @param string $device_sn
     * @return EquipmentContract
     */
    public function getEquipmentObject(string $device_sn):EquipmentContract
    {
        $table = config('equipment.table_name', 'equipments');
        $producer = DB::table($table)
            ->where('device_sn', $device_sn)
            ->value('manufacturer');
        $obj = $this->drive_path . ucfirst($producer);
        return  new $obj();
    }
    /**
     * base64转图片并存储
     * @param string $base64_str
     * @param string $suffix
     * @return string
     */
    public function storeBase64(string $base64_str, string $suffix='.jpg'):string
    {
        $regex = "/^(data:\s*image\/(\w+);base64,)/";
        if (preg_match($regex, $base64_str) > 0)
        {
            $base64_str = substr($base64_str, strpos($base64_str, ',')+1);
        }
        $name = md5(uniqid()). $suffix;
        $path = config('equipment.face_path', 'records') .DIRECTORY_SEPARATOR. date('Ymd'). DIRECTORY_SEPARATOR. $name;

        $disk = Storage::disk('public');
        if ($disk->put($path, base64_decode($base64_str))){
            return 'storage' .DIRECTORY_SEPARATOR. $path;
        }else{
            return '';
        }
    }
}
