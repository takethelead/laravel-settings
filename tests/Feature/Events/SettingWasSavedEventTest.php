<?php

namespace TakeTheLead\Settings\Tests\Feature\Events;

use Illuminate\Support\Facades\Event;
use TakeTheLead\Settings\Events\SettingWasSaved;
use TakeTheLead\Settings\Models\Setting;
use TakeTheLead\Settings\Tests\TestCase;

class SettingWasSavedEventTest extends TestCase
{
    /** @test */
    public function it_fires_the_setting_was_saved_event_when_updating_a_setting()
    {
        Event::fake();

        $setting = Setting::factory()->create(['key' => 'my_setting']);

        Event::assertDispatched(SettingWasSaved::class, function (SettingWasSaved $event) use ($setting) {
            return $event->setting->id === $setting->id;
        });
    }

    /** @test */
    public function it_clears_the_setting_cache_when_updating_a_setting()
    {
        Setting::factory()->create(['key' => 'my_setting']);

        $setting = Setting::byKey('my_setting');

        // assert that the setting is cached
        $this->assertSame($setting, cache(Setting::getCacheKey($setting->key)));

        $setting->update(['value' => 'updated value']);

        // assert that the setting is no longer cached
        $this->assertNull(cache(Setting::getCacheKey($setting->key)));
    }
}
