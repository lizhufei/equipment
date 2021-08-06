<?php


namespace Hsvisus\Equipment;

use Illuminate\Support\Facades\Facade;

class FacadeService extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'hardware';
    }
}
