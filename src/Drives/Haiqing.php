<?php


namespace Hsvisus\Equipment\Drives;

use Hsvisus\Equipment\Driver;
use Hsvisus\Equipment\EquipmentContract;
use Hsvisus\Equipment\Models\Equipment_persons;
use Hsvisus\Equipment\Models\Face;
use Hsvisus\Equipment\Models\History;
use Hsvisus\Equipment\Models\Task;
use Hsvisus\Wechat\Models\Qr;

class Haiqing implements EquipmentContract
{
    /**
     * 创建下发务
     * @param string $device_sn
     * @param array $data
     * @param string $operate
     */
    public function generate(string $device_sn, array $data, string $operate='save')
    {
        $time = date('Y-m-d H:i:s');
        $fields['device_sn'] = $device_sn;
        switch ($operate){
            case 'save':
                $info = $this->add($device_sn, $data);
                break;
            case 'update':
                $info = $this->edit($device_sn, $data);
                break;
            case 'delete':
                $info = $this->del($device_sn, $data);
                break;
            case 'remote':
                $info = $this->remote($device_sn, $data);
                $fields['priority'] = 1; //优先级 默认为0
                break;
            case 'add_card':
                $info = $this->add_card($device_sn, $data);
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
     * @param array $request
     * @return array
     */
    public function heartRespond(string $device_sn, $request=[]):array
    {
        $stateCode = $request['data']['StateCode']??1;
        $operatorInfo = $request['data']['OperatorInfo'];
        // StateCode=1时，不管平台无有任务，由于没处理完成，不能下发Taskid，即值为0
        if (1 == $stateCode){
            return $this->responseData();
        }
        //任务完成反馈保存
        if (isset($request['data']['code']) && 200 == $request['data']['code']){
            $this->feedbackRespond($device_sn, $operatorInfo);
        }
        $task = Task::pending($device_sn, 1); //先下发优先级高的任务(比如远程开门)
        if (empty($task)){
            $task =  Task::pending($device_sn);
        }
        if (empty($task)){
            return $this->responseData();
        }
        return $this->issuedRespond($device_sn, $task);
    }

    /**
     * 下发任务响应
     * @param string $device_sn
     * @param $data
     * @return array
     */
    public function issuedRespond(string $device_sn, $data)
    {
        return $this->responseData($data['info'], $data['id']);
    }

    /**
     * 上传人脸
     * @param string $device_sn
     * @param $data
     * @return null
     */
    public function uploadRespond(string $device_sn, $data)
    {
        $info = $data['info'];
        $response = [
            'code' => 200, 'desc' => 'OK', 'openDoor' => 0, 'showInfo' => '禁止通行'
        ];
        $orientation = Face::orientation($device_sn);
        $fields = [
            'device_sn' => $device_sn,
            'person_id' => is_integer($info['PersonUUID'])?:0,
            'name' => $info['Name']??'陌生人',
            'temperature' => $info['Temperature']??0,
            'mask' => $info['isNoMask']??0 ,
            'screen_time' => $info['CreateTime'],
            'orientation' => $orientation
        ];
        if (1==$info['Sendintime']){
            $hook_class = config('equipment.device_hook');
            $hook = new $hook_class;
            if (isset($data['SanpPic'])){
                $fields['face'] = (new Driver())->storeBase64($data['SanpPic']);
                if (
                    method_exists($hook, 'faceOpenDoor') &&
                    $hook->faceOpenDoor($info['CreateTime'], $info['PersonUUID'], $orientation)
                ){
                    $response['openDoor'] = 1;
                    $response['showInfo'] = '请通行';
                }
            }else if(!empty($info['RFIDCard'])){
                if (
                    method_exists($hook, 'cardOpenDoor') &&
                    $hook->cardOpenDoor($device_sn, $info['RFIDCard'])
                ){
                    $response['openDoor'] = 1;
                    $response['showInfo'] = '请通行';
                }
            }
        }
        Face::store($fields);
        return $response;
    }

    /**
     * 下发结果反馈
     * @param string $device_sn
     * @param array $operatorInfo
     * @return mixed
     */
    public function feedbackRespond($device_sn, $operatorInfo)
    {
        $time = date('Y-m-d H:i:s');
        foreach ($operatorInfo['errorInfo'] as $item){
            $logs[] = [
                'person_id' => $item['PersonUUID'],
                'device_sn' => $device_sn,
                'contents' => "进度:{$operatorInfo['ProgressText']};成功总数: {$operatorInfo['SuccessCount']}; 错误代码: {$item['errcode']} ",
                'code' => -1,
                'msg' => $item['errdesc'],
                'operate' => $operatorInfo['operator'],
                'created_at' => $time,
                'updated_at' => $time
            ];
            if ('AddPersons' == $operatorInfo['operator']){
                Equipment_persons::clear($device_sn, $item['PersonUUID']);
            }
        }
        foreach ($operatorInfo['SuccessInfo'] as $item){
            $logs[] = [
                'person_id' => $item['PersonUUID'],
                'device_sn' => $device_sn,
                'contents' => "进度:{$operatorInfo['ProgressText']}; 成功总数: {$operatorInfo['SuccessCount']} ",
                'code' => 1,
                'msg' => '下发成功',
                'operate' => $operatorInfo['operator'],
                'created_at' => $time,
                'updated_at' => $time
            ];
            if ('DeletePerson' == $operatorInfo['operator']){
                //如果下发是删除反馈成功,就需要删除中间表记录
                Equipment_persons::clear($device_sn, $item['PersonUUID']);
            }
        }
        return History::store($logs, true);
    }

    /**
     * 设备扫开门二维码远程开门
     * @param $device_sn
     * @param $params
     */
    public function qr(string $device_sn, array $params)
    {
        $QRCodeInfo = $params['info']['QRcodeInfo'];
        $state = Qr::state($QRCodeInfo);
        if (1 == $state){
            return Task::create($this->generate($device_sn, [], 'remote'));
        }
        return '';
    }
    /**
     * 海清设备响应格式
     * @param string $info
     * @return array
     */
    private function responseData(string $info='', $taskId=0):array
    {
        $loading = [
            'TimeStamp' => time(),
            'TaskId' => $taskId,
            'OperatorInfo' => ['Operator' => 'none']
        ];
        if (!empty($info)){
            $loading['OperatorInfo'] = json_decode($info, true); //指指令信息
        }
        return $loading;
    }

    /**
     * 新增人名单
     * @param string $device_sn
     * @param $data
     * @return array
     */
    private function add(string $device_sn, $data)
    {
        $info = [
            "Total" => count($data),
            "DeviceID" => $device_sn,
            'operator' => 'AddPersons'
        ];
        foreach ($data as $index=>$item){
            if (empty($item->face)){
                continue;
            }
            $info["Personinfo_{$index}"] = [
                "Name" => $item->name,
                "IdCard" => $item->identity??'', //身份证卡号
                "IdCardId" => $item->identity??'',//身份证卡号
                "Telnum" => $item->phone??'',
                "Gender" => --$item->sex,
                "MjCardNo" => isset($item->card)?$item->card->no:'', //韦根卡号
                "RFIDCard" => isset($item->card)?$item->card->no:'',//Id卡卡号，最大长度为18个字符长度,针对内置刷卡机型
                "AccessId" => isset($item->card)?$item->card->no:'', //门禁卡号
                "IdType" => 2, //0:CustomizeID, 1:LibID,(修改使用) 2:PersonUUID
                "picURI" => asset($item->face), //图片网络地址
                "ValidEnd" => '', //过期时间
                "ValidBegin" => '', //开始时间
                "Tempvalid" => 0, //0永久名单 1 临时名单（按过期时间）2临时名单（每天时间 段）3临时名单（有效次数）
                "PersonType" => 0, //0: 白名单 1: 黑名单
                "PersonUUID" => $item->id,
            ];
        }
        return $info;
    }

    /**
     * 更新名单
     * @param string $device_sn
     * @param $data
     * @return array
     */
    private function edit(string $device_sn, $data)
    {
        return [];
    }

    /**
     * 删除名单
     * @param string $device_sn
     * @param $data
     * @return array
     */
    private function del(string $device_sn, $data)
    {
        return[
            "operator" => "DeletePerson",
            "info" => [
                "IdType" => 2,
                "DeviceID" => $device_sn,
                "TotalNum" => count($data),
                "PersonUUID" => array_map(function ($item) {
                    return $item->id;
                }, $data)
            ]
        ];
    }

    /**
     * 远程开门指令
     * @param string $device_sn
     * @param $data
     * @return array
     */
    private function remote(string $device_sn, $data=[])
    {
        return[
            "operator" => "OpenDoor",
            "info" => [
                "DeviceID" => $device_sn,
                "Chn" => 0,
                "status" => empty($data)? 1: $data['status'],
                "msg" => empty($data)?"请通行":$data['msg']
            ]
        ];
    }

    /**
     * 单独添加IC卡
     * @param string $device_sn
     * @param array $data IC卡号数据
     */
    private function add_card(string $device_sn, $data=[])
    {

        $info = [
            "Total" => count($data),
            "DeviceID" => $device_sn,
            'operator' => 'AddPersons'
        ];
        foreach ($data as $index=>$item){
            if (empty($item->face)){
                continue;
            }
            $info["Personinfo_{$index}"] = [
                "Name" => 'IC卡',
                "IdCard" => $item->no, //身份证卡号
                "IdCardId" => $item->no,//身份证卡号
                "Telnum" => $item->no,
                "Gender" => 1,
                "MjCardFrom" => 2,   //手动下发卡号
                "RFCardMode" => 0, //下发内置刷卡卡号使用十进制下发
                "WiegandType" => 1, //使用韦根34位
                "MjCardNo" => $item->no, //韦根卡号
                "RFIDCard" => $item->no,//Id卡卡号，最大长度为18个字符长度,针对内置刷卡机型
                "AccessId" => $item->no, //门禁卡号
                "IdType" => 2, //0:CustomizeID, 1:LibID,(修改使用) 2:PersonUUID
                "picURI" => asset($item->face), //图片网络地址
                "ValidEnd" => '', //过期时间
                "ValidBegin" => '', //开始时间
                "Tempvalid" => 0, //0永久名单 1 临时名单（按过期时间）2临时名单（每天时间 段）3临时名单（有效次数）
                "PersonType" => 0, //0: 白名单 1: 黑名单
                "PersonUUID" => 'c_'.$item->no,
            ];
        }
        return $info;
    }

}

