<?php

namespace Hsvisus\Equipment\Controllers;

use Hsvisus\Equipment\Driver;
use Hsvisus\Equipment\Models\Task;
use Illuminate\Http\Request;

class MachineController extends Controller
{
    private $driver;
    public function __construct()
    {
        $this->driver = new Driver();
    }
    /**
     *  心跳 - 通用模式
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function heartbeat(Request $request, $device_sn)
    {
        file_put_contents('device.txt', date('Y-m-d H:i:s') . "{$device_sn} 已连接...".PHP_EOL, FILE_APPEND);
        //更新设备在线状态$request->get('table')
        $this->driver->updateOnline('equipments', $device_sn);
        //获取设备对象
        $equipment = $this->driver->getEquipmentObject($device_sn);
        $response = $equipment->heartRespond($device_sn, $request->input());
        //返回json响应
        return $this->formatResponse($response);
    }

    /**
     * 上传人脸 - 通用模式
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function face(Request $request, $device_sn)
    {
        $equipment = $this->driver->getEquipmentObject($device_sn);
        $data = $request->input();
        $response = $equipment->uploadRespond($device_sn, $data);
        return $this->formatResponse($response);
    }

    /**
     * 下发反馈 - 深云+巨龙
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function feedback(Request $request, $device_sn)
    {
        $equipment = $this->driver->getEquipmentObject($device_sn);
        $feedbackResult = $request->input();
        $response = $equipment->feedbackRespond($device_sn, $feedbackResult);
        return $this->formatResponse($response);
    }

    /**
     * 下发人脸 - 深云+巨龙
     * @return \Illuminate\Http\JsonResponse
     */
    public function issued(Request $request, $device_sn)
    {
        $equipment = $this->driver->getEquipmentObject($device_sn);
        //查询设备要下发的人员数据
        $pushData = Task::pending($device_sn);
        $response = $equipment->issuedRespond($device_sn, $pushData);
        return $this->formatResponse($response);
    }

    /**
     * 扫开门二维码
     * @param Request $request
     * @param $device_sn
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function qr(Request $request, $device_sn)
    {
        $equipment = $this->driver->getEquipmentObject($device_sn);
        $params = $request->input();
        $response = $equipment->qr($device_sn, $params);
        return $this->formatResponse($response);

    }

    /**
     * 格式化响应数据
     * @param array $content
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    private function formatResponse(array $content=[])
    {
        //json_encode同时不转义汉字和斜线 JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES); 256|64
        $content = json_encode($content, 320);
        $headers = [
            'Content-Length' => strlen($content),
            'Content-Type' => 'application/json;charset=UTF-8'
        ];

        return response($content, 200, $headers);
    }
}
