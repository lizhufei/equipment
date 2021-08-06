<?php

namespace Hs\Equipment;


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


}
