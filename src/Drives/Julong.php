<?php


namespace Hsvisus\Equipment\Drives;

use GuzzleHttp\Exception\ServerException;
use Hsvisus\Equipment\Driver;
use Hsvisus\Equipment\EquipmentContract;
use Hsvisus\Equipment\Models\Equipment_persons;
use Hsvisus\Equipment\Models\Face;
use Hsvisus\Equipment\Models\History;
use Hsvisus\Equipment\Models\Task;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Julong implements EquipmentContract
{
    /**
     * 分配任务
     * @param string $device_sn
     * @param array $data
     * @param string $operate
     * @return array|void
     */
    public function generate(string $device_sn, array $data, string $operate='save')
    {
        $count = 0;
        $info = [];
        foreach ($data as $item){
            $count++;
            switch ($operate){
                case 'save':
                    $info[] = [
                        "Action" => "addPerson",
                        "SN" => $item->id,//任务SN号（任务的唯一标识，和结果上报SN匹配）
                        "PersonType" => 2,//名单类型： 1：黑名单 2：白名单 3：VIP名单
                        "PhotoType" => 0, //人员图片下发类型 （默认0） 0：URL的方式; PersonPhotoUrl字段 1：Base64的方式; PersonPhoto字段
                        "PersonPhotoUrl" => asset($item->face), //人脸图片URL地址
                        "PersonId" => "{$item->id}", //跟开发文档不一样,这个得放到PersonInfo外面
                        "PersonInfo" => [
                            "PersonName" => $item->name,
                            "Sex" => $item->sex,  //性别 1：男  2：女  0：未知
                            "IDCard" => $item->identity, //身份证号
                            "Phone" => $item->phone,
                            "LimitTime" => 0,//人员有效时间限制0 : 永久有效1 : 周期有效
                            "StartTime" => "",//开始时间
                            "EndTime" => "",//结束时间
                            "PersonIdentity" => 1,
                            "IdentityAttribute" => 1,
                            "Label" => "职员", //人员标签
                            "ICCardNo" => isset($item->card)? $item->card->no: '', //IC卡号
                        ]
                        // "PersonPhoto" => "...(base64)...", //人脸BASE64
                    ];
                    break;
                case 'update':
                    $info[] = [
                        "Action" => "upgrade",
                        "SN" => $item->id,
                        "Version" => 2
                    ];
                    break;
                case 'delete':

                    $info[] = [
                        "Action" => "deletePerson",
                        "SN" => $item->id,
                        "PersonType" => 2,
                        "PersonId" => "{$item->id}"
                    ];
                    break;
            }
        }
        $fields['device_sn'] = $device_sn;
        $fields['count'] = $count;
        $fields['created_at'] = $fields['updated_at'] = date('Y-m-d H:i:s');
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
    {
        //判断是否有任务下发
        $isTask = Task::isTask($device_sn)? 1 : 0;
        return [
                "Name" => 'heartbeatResponse',
                "TimeStamp" => time(),
                "Session" => "",
                "EventCount" => $isTask,
                "Code" => 1,
                "Message" => ""
            ];
    }

    /**
     * 通行上传人脸
     * @param string $device_sn
     * @param $data
     * @return string
     */
    public function uploadRespond(string $device_sn, $data)
    {
//        $deviceInfo = $data['Data']['DeviceInfo'];
//        $faceInfo = $data['Data']['FaceInfo'];
        $captureInfo = $data['Data']['CaptureInfo'];
        $compareInfo = $data['Data']['CompareInfo'];
        $fields = [
            'device_sn' => $device_sn,
            'person_id' => $compareInfo['PersonInfo']['PersonId']??'0',
            'name' => $compareInfo['PersonInfo']['PersonName']??'陌生人',
            'face' => (new Driver())->storeBase64($captureInfo['FacePicture']),
            'temperature' => 0,
            'mask' => $compareInfo['Attribute']['Mask'],
            'screen_time' => $captureInfo['CaptureTime'],
            'orientation' => Face::orientation($device_sn),
        ];

        $fields['other'] = json_encode($compareInfo, 320);
        Face::store($fields);
        return [
            "Name" => "captureInfoResponse",
            "TimeStamp" => time(),
            "Session" => "",
            "EventCount" => 0,
            "Code" => 1,
            "Message" => "上传成功"
        ];
    }

    /**
     * 下发任务
     * @param string $device_sn
     * @param array $data
     * @return mixed|void
     */
    public function issuedRespond(string $device_sn, array $data)
    {
        $persons = json_decode($data['info'], true);
        $params = [
            "Name" => "eventResponse",
            "UUID" => $device_sn,
            "TimeStamp" => time(),
            "Code" => 1,
            "Message" => "成功",
            "Data" => [
                "NextEvent" => 0,//下次获取任务状态 0：处理当前任务列表完成(并上报)，等心跳通知再去获取新任务列表 1：处理当前任务列表完成(并上报)后再次调用主动获取任务接口获取新任务列表（不用等心跳通知）
                "ListCount" => $data['count'], //列表数量
                "List" => $persons
//                "Action"  => "addPersons",
//                "AddType" => 0,//添加方式 0：图片添加（默认） 1：特征值添加（同步） 2：特征值添加（异步） （待实现） 3：IC卡添加（只识别IC卡不识别人脸）
//                "PersonType" => 2,//名单类型：1：黑名单2：白名单 3：VIP名单
//                "PersonCover" => 1,//是否覆盖添加 0：不覆盖 （已存在该人员ID则不添加该人员，返回错误信息）1：覆盖（若存在该人员ID则覆盖该已存在人员ID信息；若不存人员ID则添加该人员。）
//                "PersonList"  => $data
            ]
        ];
        return $params;
    }

    /**
     * 反馈
     * @param string $device_sn
     * @param array $params
     * @return array|void
     */
    public function feedbackRespond(string $device_sn, array $params):array
    {
        foreach ($params['Data']['List'] as $item){
            $data = [
                'person_id' => $item['SN'],
                'device_sn' => $device_sn,
                'contents' => "任务总人数: {$params['Data']['ListCount']}",
                'code' => $item['ResultCode'],
                'msg' => "{$item['ResultMessage']}",
                'operate' => $item['Action'],
            ];
            History::store($data);
            //如果是删除就删掉中间表
            if (1 == $item['ResultCode']){
                if ('deletePerson' == $item['Action']){
                    Equipment_persons::clear($device_sn, $item['SN']);
                }
            }elseif('addPerson' == $item['Action']){
                Equipment_persons::clear($device_sn, $item['SN']);
            }

        }
        return [
                "Name"  => "resultResponse",
                "TimeStamp" => time(),
                "Session" => "",
                "Code" => 1,
                "Message"  => "收到反馈并已处理"
        ];

    }

    /**
     * 请求设备中间件
     * @param string $device_sn
     * @param $data
     */
    private function request(string $device_sn, $data)
    {
        try {
            $headers = [
                'content-type' => 'application/json;charset=utf-8',
                'UUID' => $device_sn
            ];
            $url = config("equipment.julong_server"); //巨龙中间件服务器
            if (empty($middle_url)){
                throw new ServerException('巨龙中间件服务器未配置');
            }
            $response = Http::withHeaders($headers)->post($url, $data);;
            $params = [
                'response_body' => $response->body(),
                'response_status' => $response->status(),
                'response_ok' => $response->ok(),
                'person_id' => $data['Data']['PersonInfo']['PersonId'],
            ];
            return $this->feedbackRespond($device_sn, $params);
        } catch (RequestException $e) {
            Log::error("巨龙设备:{$device_sn}->下发失败，原因：1.请求参数".$e->getRequest().'响应：'. $e->getResponse()??'没有响应');
        }

    }
}
