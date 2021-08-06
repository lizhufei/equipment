<?php
use Illuminate\Support\Facades\Route;
use Hs\Equipment\Controllers\MachineController;


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
    //通用设备接口 有：巨龙，海清
    Route::post('heartbeat/{device_sn}', [MachineController::class, 'heartbeat']); //设备心跳同步
    Route::post('task/{device_sn}', [MachineController::class, 'issued']); //主动获取任务
    Route::post('feedback/{device_sn}', [MachineController::class, 'feedback']); //任务反馈
    Route::post('face/{device_sn}', [MachineController::class, 'face']); //上传人脸
});



