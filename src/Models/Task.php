<?php


namespace Hs\Equipment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $table = 'issued_tasks';
    protected $guarded = [];

    /**
     * 设备有没和任务
     * @param string $device_sn
     * @return bool
     */
    protected function isTask(string $device_sn):bool
    {
        return $this->where([
            'device_sn' => $device_sn,
            'status' => 0
        ])->exists();
    }
    /**
     * 下发数据
     * @param string $device_sn
     * @return array
     */
    protected function pending(string $device_sn)
    {
        $model = $this->where([
            'device_sn' => $device_sn,
            'status' => 0,
        ])->oldest()->first();
        if ($model && !empty($model->info)){
            $model->status = 1;
            $model->save();
            return $model->toArray();
        }
        return [];
    }

}
