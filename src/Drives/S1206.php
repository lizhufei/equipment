<?php


namespace Hsvisus\Equipment\Drives;

use Hsvisus\Equipment\EquipmentContract;

class S1206 implements EquipmentContract
{
    /**
     * 创建下发任务
     * @param string $device_sn
     * @param array $data
     * @param string $operate
     * @return array|null
     */
    public function generate(string $device_sn, array $data, string $operate)
    {

    }

    /**
     * 心跳响应
     * @param string $device_sn
     * @param null $request
     * @return array
     */
    public function heartRespond(string $device_sn, $request=null):array
    {
        return [
            "info" => "接收成功",
            "resultCode" => 100,
            "data" => ""
        ];

    }

    /**
     * 上传车牌信息
     * @param string $device_sn
     * @param array $other
     * @return mixed
     */
    public function uploadRespond(string $device_sn, array $other)
    {

    }

    /**
     * 下发人脸数据
     * @param string $device_sn
     * @param array $data
     * @return mixed
     */
    public function issuedRespond(string $device_sn, array $data)
    {

    }

    /**
     * 下发反馈
     * @param string $device_sn
     * @param  array $other
     * @return mixed
     */
    public function feedbackRespond(string $device_sn, array $params)
    {

    }
}
