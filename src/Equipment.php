<?php

namespace Hsvisus\Equipment;


use Hsvisus\Equipment\Models\Equipment_persons;
use Hsvisus\Equipment\Models\Task;
use Illuminate\Http\Request;
use mysql_xdevapi\SqlStatementResult;

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
        $date = date('Y-m-d H:i:s');
        foreach ($device_sns as $dev){
            foreach ($person_ids as $per){
                if ('delete' == $operate){
                    $data[] = Equipment_persons::where([
                        ['device_sn', '=', $dev],
                        ['person_id', '=', $per],
                    ])->delete();

                }else{
                    $data[] = [
                        'device_sn' => $dev,
                        'person_id' => $per,
                        'created_at' => $date
                    ];
                }

            }
        }
        if ('save' == $operate){
            return Equipment_persons::insert($data);
        }
        return $data;
    }

    /**
     * 获取设备里的人员
     * @param Request $request
     * @return mixed
     */
    public function inDevicePersons(Request $request)
    {
        $device_sn = $request->get('device_sn', '');
        $name = $request->get('name', '');
        return Equipment_persons::getPersons($device_sn, $name);
    }

    /**
     * 删除设备里的人
     * @param string $device_sn
     * @param  $persons
     */
    public function deleteDevicePerson(string $device_sn, $persons)
    {
        $deviceObj = $this->getEquipmentObj($device_sn);
        $tasks = $deviceObj->generate($device_sn, $persons, 'delete');
        return Task::created($tasks);
    }






}
