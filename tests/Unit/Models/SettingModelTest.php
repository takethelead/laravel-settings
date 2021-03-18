<?php

namespace TakeTheLead\Settings\Tests\Unit\Models;

use Carbon\Carbon;
use TakeTheLead\Settings\Exceptions\InvalidTypeException;
use TakeTheLead\Settings\Exceptions\TypeNotQueriedException;
use TakeTheLead\Settings\Models\Setting;
use TakeTheLead\Settings\Tests\TestCase;

class SettingModelTest extends TestCase
{
    /** @test */
    public function it_decrypts_string_values_when_the_property_is_accessed()
    {
        Setting::factory()->create([
            'key' => 'some_setting',
            'value' => 'some_value_that_should_be_encrypted',
            'type' => 'string',
        ]);

        $this->assertDatabaseHas('settings', ['key' => 'some_setting']);
        $this->assertDatabaseMissing('settings', ['value' => 'some_value_that_should_be_encrypted']);
        $this->assertNotEquals('some_value_that_should_be_encrypted', Setting::first()->getAttributes()['value']);
        $this->assertEquals('some_value_that_should_be_encrypted', Setting::first()->value);
    }

    /** @test */
    public function it_can_query_a_setting_by_key()
    {
        Setting::factory()->createMany([
            ['key' => 'setting_1'],
            ['key' => 'setting_2'],
        ]);

        $this->assertInstanceOf(Setting::class, Setting::byKey('setting_1'));
        $this->assertInstanceOf(Setting::class, Setting::byKey('setting_2'));
        $this->assertNull(Setting::byKey('invalid_key'));
    }

    /** @test */
    public function it_caches_a_setting_forever_when_queried()
    {
        $setting = Setting::factory()->create(['key' => 'setting_1']);
        $cacheKey = Setting::getCacheKey($setting->key);

        $this->assertNull(cache($cacheKey));

        $setting = Setting::byKey('setting_1');

        $this->assertTrue(cache()->has($cacheKey));
        $this->assertEquals($setting, cache($cacheKey));
        $this->assertInstanceOf(Setting::class, cache($cacheKey));

        // We fast forward 10 years so that we can make sure
        // that the value is still cached.
        Carbon::setTestNow(now()->addYears(10));

        $this->assertTrue(cache()->has($cacheKey));
        $this->assertEquals($setting, cache($cacheKey));
        $this->assertInstanceOf(Setting::class, cache($cacheKey));
    }

    /** @test */
    public function it_can_get_the_cache_key()
    {
        $settings = Setting::factory()->createMany([
            ['key' => 'setting_1'],
            ['key' => 'setting_2'],
            ['key' => 'setting_3'],
        ]);

        collect($settings)->each(function (Setting $setting) {
            $this->assertEquals("setting_$setting->key", Setting::getCacheKey($setting->key));
        });
    }

    /** @test */
    public function it_can_check_if_a_setting_is_enabled()
    {
        $settings = Setting::factory()->createMany([
            ['key' => 'boolean_setting_1', 'value' => true, 'type' => 'boolean'],
            ['key' => 'boolean_setting_2', 'value' => false, 'type' => 'boolean'],
            ['key' => 'sting_setting_1', 'type' => 'string'],
        ]);


        $this->assertTrue(Setting::isEnabled('boolean_setting_1'));
        $this->assertFalse(Setting::isEnabled('boolean_setting_2'));

        $this->expectException(InvalidTypeException::class);
        Setting::isEnabled('sting_setting_1');
    }

    /** @test */
    public function it_can_check_if_a_setting_is_not_enabled()
    {
        $settings = Setting::factory()->createMany([
            ['key' => 'boolean_setting_1', 'value' => true, 'type' => 'boolean'],
            ['key' => 'boolean_setting_2', 'value' => false, 'type' => 'boolean'],
            ['key' => 'string_setting_1', 'type' => 'string'],
        ]);


        $this->assertFalse(Setting::isNotEnabled('boolean_setting_1'));
        $this->assertTrue(Setting::isNotEnabled('boolean_setting_2'));

        $this->expectException(InvalidTypeException::class);
        Setting::isNotEnabled('string_setting_1');
    }

    /** @test */
    public function it_requires_the_type_column_in_each_query()
    {
        Setting::factory()->create();

        $this->assertIsArray(Setting::all('key', 'value', 'type')->toArray());

        $this->expectException(TypeNotQueriedException::class);

        Setting::all(['key', 'value'])->toArray();
    }

    /** @test */
    public function it_convert_json_settings_to_objects()
    {
        $setting = Setting::factory()->create([
            'key' => 'json_setting',
            'value' => '{"ttl": "Is Awesome!", "LARAVEL": true}',
            'type' => 'json',
        ]);

        $this->assertIsObject($setting->value);
        $this->assertEquals('Is Awesome!', $setting->value->ttl);
        $this->assertTrue($setting->value->LARAVEL);
    }

    /** @test */
    public function it_does_not_fail_on_json_settings_with_invalid_json_data()
    {
        $setting = Setting::factory()->create([
            'key' => 'json_setting',
            'value' => '{"ttl": "Is Awesome!, "LARAVEL": true}',
            'type' => 'json',
        ]);

        $this->assertNull($setting->value);
    }
}
