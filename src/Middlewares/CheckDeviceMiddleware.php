<?php

namespace Hs\Equipment\Middlewares;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CheckDeviceMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $table     = config('equipment.table_name');
        $device_sn = $request->route('device_sn');
        if(
            DB::table($table)
                ->where('device_sn', $device_sn)
                ->doesntExist()
        ){
            return response()->json([
                'data' => [],
                'mete' => [
                    'code' => 404,
                    'msg' => '此设备未添加到平台！！'
                ]
            ]);
            //abort(404, '');
        }
        $request->attributes->add(['table' => $table]);
        return $next($request);
    }
}
