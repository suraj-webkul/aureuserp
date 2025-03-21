<?php

namespace Webkul\Account\Facades;

use Illuminate\Support\Facades\Facade;

class Account extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'account';
    }
}
