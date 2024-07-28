<?php

namespace Nikba\Paynet\Facades;

use Illuminate\Support\Facades\Facade;

class Paynet extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'Nikba\Paynet\Services\PaynetService';
    }
}
