<?php

namespace Hsvisus\Equipment;


use Hsvisus\Equipment\Models\Equipment_persons;

class Equipment
{
    /**
     * 获取设备对象
     * @param string $device_sn
     * @return EquipmentContract
     */
    public function getEquipmentObj(string $device_sn)
    {
        return (new Driver())->getEquipmentObject($device_sn);
    }

    /**
     * 人员设备中间表操作
     * @param array $device_sns
     * @param array $person_ids
     * @param string $operate
     * @return false
     */
    public function intermediary(array $device_sns, array $person_ids, string $operate='save')
    {
        $data = [];
        foreach ($device_sns as $dev){
            foreach ($person_ids as $per){
                if ('delete' == $operate){
                    $data[] = Equipment_persons::where([
                        ['device_sn', '=', $dev],
                        ['person_id', '=', $per]
                    ])->delete();

                }else{
                    $data[] = [
                        'device_sn' => $dev,
                        'person_id' => $per
                    ];
                }

            }
        }
        if ('save' == $operate){
            return Equipment_persons::insert($data);
        }
        return $data;
    }




}
