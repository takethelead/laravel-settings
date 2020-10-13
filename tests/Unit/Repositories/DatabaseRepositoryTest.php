<?php

namespace TakeTheLead\Settings\Tests\Unit\Repositories;

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Log;
use TakeTheLead\Settings\Exceptions\ConfigKeyNotFoundException;
use TakeTheLead\Settings\Exceptions\SettingNotFoundException;
use TakeTheLead\Settings\Models\Setting;
use TakeTheLead\Settings\Repositories\DatabaseRepository;
use TakeTheLead\Settings\Tests\TestCase;

class DatabaseRepositoryTest extends TestCase
{
    /** @test */
    public function it_can_overwrite_settings_from_default_files()
    {
        Setting::factory()->createMany([
            ['key' => 'APP_NAME', 'value' => 'Laravel settings', 'type' => 'string'],
            ['key' => 'MAILGUN_SECRET', 'value' => 'MysecretForMailgun', 'type' => 'string'],
            ['key' => 'APP_DEBUG', 'value' => true, 'type' => 'boolean'],
        ]);

        $this->assertEquals('Laravel', config('app.name'));
        $this->assertFalse(config('app.debug'));
        $this->assertNull(config('services.mailgun.secret'));

        $databaseRepository = resolve(DatabaseRepository::class);
        $databaseRepository->overwriteDefaults([
            'app.name' => 'APP_NAME',
            'app.debug' => 'APP_DEBUG',
            'services.mailgun.secret' => 'MAILGUN_SECRET',
        ]);

        $this->assertEquals('Laravel settings', config('app.name'));
        $this->assertTrue(config('app.debug'));
        $this->assertEquals('MysecretForMailgun', config('services.mailgun.secret'));
    }

    /** @test */
    public function it_can_overwrite_settings_from_default_files_with_custom_config_keys()
    {
        Setting::factory()->createMany([
            ['key' => 'MY_SERVICE_BE_HOST', 'value' => 'https://be.myservice.com', 'type' => 'string'],
        ]);

        config()->set('services.my_service.belgium.host', 'https://be.default.myservice.com');

        $this->assertEquals('https://be.default.myservice.com', config('services.my_service.belgium.host'));

        $databaseRepository = resolve(DatabaseRepository::class);
        $databaseRepository->overwriteDefaults([
            'services.my_service.belgium.host' => 'MY_SERVICE_BE_HOST',
        ]);

        $this->assertEquals('https://be.myservice.com', config('services.my_service.belgium.host'));
    }

    /** @test */
    public function it_can_overwrite_settings_from_custom_config_files()
    {
        Setting::factory()->createMany([
            ['key' => 'FEATURE1_IS_ENABLED', 'value' => true, 'type' => 'boolean'],
        ]);

        config()->set('custom-file.feature_1.is_enabled', false);

        $this->assertFalse(config('custom-file.feature_1.is_enabled'));

        $databaseRepository = resolve(DatabaseRepository::class);
        $databaseRepository->overwriteDefaults([
            'custom-file.feature_1.is_enabled' => 'FEATURE1_IS_ENABLED',
        ]);

        $this->assertTrue(config('custom-file.feature_1.is_enabled'));
    }

    /** @test */
    public function it_can_not_overwrite_the_value_of_a_non_existing_config_key()
    {
        Setting::factory()->createMany([
            ['key' => 'FEATURE1_IS_ENABLED', 'value' => true, 'type' => 'boolean'],
        ]);

        $this->assertFalse(config()->has('custom-file.feature_1.is_enabled'));

        $this->expectException(ConfigKeyNotFoundException::class);

        $databaseRepository = resolve(DatabaseRepository::class);
        $databaseRepository->overwriteDefaults([
            'custom-file.feature_1.is_enabled' => 'FEATURE1_IS_ENABLED',
        ]);
    }

    /** @test */
    public function it_can_not_overwrite_a_config_value_if_the_setting_does_not_exist()
    {
        config()->set('custom-file.feature_1.key', 'RandomString');

        $this->assertEquals('RandomString', config('custom-file.feature_1.key'));

        $databaseRepository = resolve(DatabaseRepository::class);
        $databaseRepository->overwriteDefaults([
            'custom-file.feature_1.key' => 'FEATURE1_KEY',
        ]);

        $this->assertEquals('RandomString', config('custom-file.feature_1.key'));
    }

    /** @test */
    public function it_can_use_special_characters_in_the_new_config_value()
    {
        Setting::factory()->createMany([
            ['key' => 'MAILGUN_SECRET', 'value' => '&é"\'èç!à)($%?:€.=+', 'type' => 'string'],
        ]);

        config()->set('custom-file.feature_1.key', 'RandomString');

        $this->assertEquals('RandomString', config('custom-file.feature_1.key'));

        $databaseRepository = resolve(DatabaseRepository::class);
        $databaseRepository->overwriteDefaults([
            'custom-file.feature_1.key' => 'MAILGUN_SECRET',
        ]);

        $this->assertEquals('&é"\'èç!à)($%?:€.=+', config('custom-file.feature_1.key'));
    }
}
