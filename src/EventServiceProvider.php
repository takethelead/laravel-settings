<?php

namespace TakeTheLead\Settings;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use TakeTheLead\Settings\Events\SettingWasSaved;
use TakeTheLead\Settings\Listeners\ClearSettingCache;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        SettingWasSaved::class => [
            ClearSettingCache::class,
        ],
    ];
}
