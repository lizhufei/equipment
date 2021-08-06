<?php

namespace Hsvisus\Equipment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Equipment_persons extends Model
{
    use HasFactory;

    protected $table = 'equipment_persons';
    protected $guarded = [];

    //反向一对一
    //
    /**
     * 清除人员跟设备关联
     * @param string $device_sn
     * @param int $person_id
     * @return mixed
     */
    protected function clear(string $device_sn, int $person_id)
    {
        return $this->where([
            'device_sn' => $device_sn,
            'person_id' => $person_id
        ])->delete();
    }
}
