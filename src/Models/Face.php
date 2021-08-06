<?php

namespace Hs\Equipment\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
