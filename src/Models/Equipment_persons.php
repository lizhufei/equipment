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

    /**
     * 获取设备里的人
     * @param string $device_sn
     * @param string $name
     * @param int $limit
     * @return mixed
     */
    protected function getPersons(string $device_sn, string $name='', int $limit=10)
    {
        return $this->when($name, function ($q)use($name){
            $q->where('persons.name', 'like', "%{$name}%");
        })
            ->leftJoin('persons', 'equipment_persons.person_id', '=', 'persons.id')
            ->where('equipment_persons.device_sn', $device_sn)
            ->orderBy('equipment_persons.created_at', 'DESC')
            ->simplePaginate($limit);
    }
}
