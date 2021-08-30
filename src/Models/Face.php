<?php

namespace Hsvisus\Equipment\Models;

use Endroid\QrCode\Builder\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Face extends Model
{
    use HasFactory;

    protected $table = 'records';
    protected $guarded = [];

    /**
     * 模型的事件映射
     * @var array
     */
    protected $dispatchesEvents = [
        //'created' => UserSaved::class,
        //'deleted' => UserDeleted::class,
    ];

    protected function store(array $fields)
    {
        return $this->create($fields);
    }

    /**
     * 获取设备方向
     * @param string $device_sn
     * @return mixed|null
     */
    protected function orientation(string $device_sn)
    {
        return DB::table(config('equipment.table_name', 'equipments'))
            ->where('device_sn', $device_sn)
            ->value('orientation');
    }
}
