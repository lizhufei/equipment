<?php
/**
 * 设备驱动接口.
 * 所有接入设备都必须继承此接口
 */
namespace Hsvisus\Equipment;

interface EquipmentContract
{
    /**
     * 创建下发任务
     * @param string $device_sn
     * @param array $data
     * @param string $operate
     * @return array|null
     */
    public function generate(string $device_sn, array $data, string $operate);

    /**
     * 心跳响应
     * @param string $device_sn
     * @param null $request
     * @return array
     */
    public function heartRespond(string $device_sn, $request=null):array;

    /**
     * 更新人脸信息
     * @param string $device_sn
     * @param array $other
     * @return mixed
     */
    public function uploadRespond(string $device_sn, array $other);

    /**
     * 下发人脸数据
     * @param string $device_sn
     * @param array $data
     * @return mixed
     */
    public function issuedRespond(string $device_sn, array $data);

    /**
     * 下发反馈
     * @param string $device_sn
     * @param  array $other
     * @return mixed
     */
    public function feedbackRespond(string $device_sn, array $params);


}
