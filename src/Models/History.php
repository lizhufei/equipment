<?php

namespace Hs\Equipment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class History extends Model
{
    use HasFactory;

    protected $table = 'task_histories';
    protected $guarded = [];

    /**
     * 记录人员下发历史
     * @param int $person_id
     * @param string $device_sn
     * @param $contents
     * @param string $contents
     * @return mixed
     */
    protected function mark(int $person_id, string $device_sn, $contents, string $operator='save')
    {
        $msg = $contents['msg'];
        $code = $contents['code'];
        $contents = is_string($contents) ?: json_encode($contents, 320);
        return $this->create([
            'person_id' => $person_id,
            'device_sn' => $device_sn,
            'operate' => $operator,
            'contents' => $contents,
            'code' => $msg,
            'msg' => $code
        ]);
    }

    /**
     * 保存反馈
     * @param array $fields
     * @param false $batch 是否是批量
     * @return mixed
     */
    protected function store(array $fields, $batch=false)
    {
        if ($batch){
            return $this->insert($fields);
        }
        return $this->create($fields);
    }


}
