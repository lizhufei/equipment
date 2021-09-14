<?php


namespace Hsvisus\Equipment\Drives;

use Carbon\Carbon;
use Hsvisus\Equipment\Driver;
use Hsvisus\Equipment\EquipmentContract;
use Hsvisus\Equipment\Models\Equipment_persons;
use Hsvisus\Equipment\Models\Face;
use Hsvisus\Equipment\Models\History;

class Shenyun_v1 implements EquipmentContract
{
    /**
     * 创建下发任务
     * @param string $device_sn
     * @param array $data
     * @param string $operate
     * @return array|null
     */
    public function generate(string $device_sn, $data, string $operate)
    {
        $time = Carbon::now();
        $fields['device_sn'] = $device_sn;
        switch ($operate){
            case 'add_card':
                $info = $this->add_card($device_sn, $data, $time, $operate);
                break;
            default:
                $info = $this->setPerson($device_sn, $data, $time, $operate);
                break;
        }
        $fields['count'] = count($data);
        $fields['created_at'] =$fields['updated_at'] = $time;
        $fields['info'] = json_encode($info, JSON_UNESCAPED_UNICODE);
        return $fields;
    }

    /**
     * 心跳响应
     * @param string $device_sn
     * @param null $request
     * @return array
     */
    public function heartRespond(string $device_sn, $request=null):array
    {}

    /**
     * 更新人脸信息
     * @param string $device_sn
     * @param array $other
     * @return mixed
     */
    public function uploadRespond(string $device_sn, array $other)
    {}

    /**
     * 下发人脸数据
     * @param string $device_sn
     * @param array $data
     * @return mixed
     */
    public function issuedRespond(string $device_sn, array $data)
    {}

    /**
     * 下发反馈
     * @param string $device_sn
     * @param  array $other
     * @return mixed
     */
    public function feedbackRespond(string $device_sn, array $params)
    {}

    /**
     * 下发人脸
     * @param string $device_sn
     * @param array $data
     * @param Carbon $Carbon
     * @param $operate
     * @return array
     */
    private function setPerson(string $device_sn, array $data, Carbon $Carbon, $operate)
    {
        if ('save' == $operate){
            $operate = 'add';
        }
        $persons = [];
        foreach ($data as $item){
            $persons[] = [
                "userId" => $item->id,
                "userName" => $item->name,
                "userType" => "staff",
                "url" => asset($item->face),
                "operation" => $operate
            ];
        }
        return [
            'timestamp' => $Carbon->timestamp,
            'data' => $persons
        ];
    }

    /**
     * 门卡下发
     * @param string $device_sn
     * @param array $data
     * @param Carbon $Carbon
     * @param $operate
     * @return array
     */
    private function add_card(string $device_sn, array $data, Carbon $Carbon, $operate)
    {
        if ('del_card' == $operate){
            $operate = 'delete';
        }else{
            $operate = 'add';
        }
        $cards = [];
        foreach ($data as $item){
            $persons[] = [
                "userId" => uniqid(),
                "type" => "RCARD",
                "card" => $item->no,
                "expired" => "4124000711",
                "operation" => $operate
            ];
        }
        return [
            'timestamp' => $Carbon->timestamp,
            'cards' => $cards
        ];
    }

}
