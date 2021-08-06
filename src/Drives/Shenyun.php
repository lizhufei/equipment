<?php

namespace Hs\Equipment\Drives;

use Hs\Equipment\Driver;
use Hs\Equipment\EquipmentContract;
use Hs\Equipment\Models\Equipment_persons;
use Hs\Equipment\Models\Face;
use Hs\Equipment\Models\History;

class Shenyun implements EquipmentContract
{
    /**
     * 创建下发任务
     * @param string $device_sn
     * @param array $data
     * @param array $operate
     */
    public function generate(string $device_sn, array $data, string $operate='save'):array
    {
        $time = date('Y-m-d H:i:s');
        $fields['device_sn'] = $device_sn;
        $count = 0;
        foreach ($data as $item){
            $count++;
            $info[] = [
                "operation" => $operate,
                "userType" => 'staff',
                "userId" => "{$item->id}",
                "userName" => $item->name,
                "sex" => $item->sex,
                // "userRemark" => "学生",
                "userImageUrl" => asset($item->face),
                "operateTime" => $time,
                "created_at" => $time,
                "updated_at" => $time
            ];
        }

        $fields['count'] = $count;
        $fields['created_at'] =$fields['updated_at'] = $time;
        $fields['info'] = json_encode($info, JSON_UNESCAPED_UNICODE);
        return $fields;
    }

    /**
     * 心跳
     * @param string $device_sn
     * @param null $request
     * @return array
     */
    public function heartRespond(string $device_sn, $request=null):array
    {
        return [
            "ret" => "0",
            'timestamp' => time(),
            "desc" => "Success"
        ];

    }

    /**
     * 下发响应数据
     * @param string $device_sn
     * @param $data
     * @return false|string
     */
    public function issuedRespond(string $device_sn, $data)
    {
        $response['serviceResponse'] = [
            'list' => json_decode($data['info'], true),
            'lastQueryTime' => time()
        ];
        return $response;

    }

    /**
     * 反馈响应
     * @param $device_sn
     * @param $params
     * @return string
     */
    public function feedbackRespond($device_sn, $params)
    {
        $data = $params['resultList'];
        $data = [
            'person_id' => $data['userID'],
            'device_sn' => $device_sn,
            'contents' => $data['msg'],
            'code' => $data['status'],
            'msg' => $data['msg'],
            'operate' => $data['operationType'],
        ];
        History::store($data);
        if ('delete' == $data['operationType'] && 100 == $data['status']){
            Equipment_persons::clear($device_sn, $data['userID']);
        }
        return [
            "ret" => 0,
            "desc" => "Success"
        ];
    }

    /**
     * 人员通行上传
     * @param string $device_sn
     * @param $other
     * @return string
     */
    public function uploadRespond(string $device_sn, $other)
    {
        if (isset($other['Info'])){
            $info = $other['Info'];
            //带身份证识别
            if (isset($other['idCardInfo'])){
                $info['idCardInfo'] = $other['idCardInfo'];
                $info['IDCardImg'] = $other['IDCardImg'];
            }
        }else{
            $info = $other;
        }
        if (isset($info['IDCardImg'])){
            $info['IDCardImg'] =(new Driver())->storeBase64($info['IDCardImg']);
        }

        $fields = [
            'device_sn' => $device_sn,
            'person_id' => $info['userId']??0,
            'name' => $info['userName']??'陌生人',
            'face' => empty($info['img']) ?'' :(new Driver())->storeBase64($other['img']),
            'temperature' => $info['temperature'],
            'mask' => $info['mask'],
            'screen_time' => date('Y-m-d H:i:s',$info['screenTime']),
        ];
        unset($info['img'],$info['userId'],$info['userName'],$info['temperature'],$info['screenTime']);
        $fields['other'] = json_encode($info, 320);
        Face::store($fields);
        return [
            "ret" => 0,
            "desc" => "Success",
            "openDoor" => 1,
            "unlockDelay" => 20
        ];
    }


}