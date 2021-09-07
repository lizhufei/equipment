<?php
return [
    'table_name' => env('EQUIPMENT_TABLE_NAME', 'equipments'),
    'face_path' => env('FACE_PATH', 'records'),
    'julong_server' => env('JULONG_SERVER', ''),
    'img_base_url' => env('IMG_BASE_URL', ''),
    'device_hook' => env('DEVICE_HOOK', '\App\Models\Device'),
];
