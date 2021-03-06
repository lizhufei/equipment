<?php
use Illuminate\Support\Facades\Route;
use Hsvisus\Equipment\Controllers\MachineController;

Route::group([
    'prefix' => 'api',
    'middleware' => 'admittance'
], function(){
    //深云设备接口
    Route::post('syheartbeat/{device_sn}', [MachineController::class, 'heartbeat']);
    Route::get('syrecord/{device_sn}', [MachineController::class, 'issued']); //下发人脸库
    Route::post('syface/{device_sn}', [MachineController::class, 'face']); //人脸比对成功上传
    Route::post('syattribute/{device_sn}', [MachineController::class, 'face']); //人脸比对失败上传
    Route::post('syresult/{device_sn}', [MachineController::class, 'feedback']); //上传反馈记录
    //深云v1设备接口
    Route::get('device/dcit/api/eq/v1/face/sync', [MachineController::class, 'issued']);
    Route::get('device/dcit/api/eq/v1/card/sync', [MachineController::class, 'card']); //下发门卡
    Route::post('device/dcit/api/eq/v1/face/result', [MachineController::class, 'face']);
    Route::post('device/dcit/api/eq/v1/card/result', [MachineController::class, 'cardFeedback']); //门卡上报

    //通用设备接口 有：巨龙，海清
    //Route::post('card/{device_sn}',  [MachineController::class, 'card']); //刷卡上传
    Route::post('heartbeat/{device_sn}', [MachineController::class, 'heartbeat']); //设备心跳同步
    Route::post('task/{device_sn}', [MachineController::class, 'issued']); //主动获取任务
    Route::post('feedback/{device_sn}', [MachineController::class, 'feedback']); //任务反馈
    Route::post('face/{device_sn}', [MachineController::class, 'face']); //上传人脸
    Route::post('qr/{device_sn}', [MachineController::class, 'qr']); //扫开门二维码
});




