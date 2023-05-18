<?php

namespace Thoughtco\Printer\Facades;

use Illuminate\Support\Facades\Facade;

class PrintHelper extends Facade
{
    /**
     * Get the registered name of the component.
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'printhelper';
    }
}
