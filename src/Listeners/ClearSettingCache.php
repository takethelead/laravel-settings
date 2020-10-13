<?php

namespace TakeTheLead\Settings\Listeners;

use Illuminate\Support\Facades\Cache;
use TakeTheLead\Settings\Events\SettingWasSaved;
use TakeTheLead\Settings\Models\Setting;

class ClearSettingCache
{
    public function handle(SettingWasSaved $event)
    {
        Cache::forget(Setting::getCacheKey($event->setting->key));
    }
}
