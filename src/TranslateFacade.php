<?php
namespace iscms\Translate;
use Illuminate\Support\Facades\Facade;

class TranslateFacade extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'translate';
    }
}