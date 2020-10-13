<?php

namespace TakeTheLead\Settings\Tests\Unit\Listeners;

use TakeTheLead\Settings\Events\SettingWasSaved;
use TakeTheLead\Settings\Listeners\ClearSettingCache;
use TakeTheLead\Settings\Models\Setting;
use TakeTheLead\Settings\Tests\TestCase;

class ClearSettingCacheTest extends TestCase
{
    /** @test */
    public function it_can_clear_the_cache()
    {
        $setting = Setting::factory()->create();

        cache()->put(Setting::getCacheKey($setting->key), $setting);

        $this->assertTrue(cache()->has(Setting::getCacheKey($setting->key)));

        (new ClearSettingCache())
            ->handle(new SettingWasSaved($setting));

        $this->assertFalse(cache()->has(Setting::getCacheKey($setting->key)));
    }
}
