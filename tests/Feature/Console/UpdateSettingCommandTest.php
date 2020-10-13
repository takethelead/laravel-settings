<?php

namespace TakeTheLead\Settings\Tests\Feature\Console;

use Illuminate\Contracts\Console\Kernel;
use TakeTheLead\Settings\Console\ListSettingsCommand;
use TakeTheLead\Settings\Console\UpdateSettingCommand;
use TakeTheLead\Settings\Models\Setting;
use TakeTheLead\Settings\Tests\TestCase;

class UpdateSettingCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Setting::factory()->create([
            'key' => 'setting_1',
            'value' => 'value_1',
            'type' => 'string',
        ]);
    }

    /** @test */
    public function it_can_update_a_setting()
    {
        $this->artisan(UpdateSettingCommand::class, ['setting' => 'setting_1'])
            ->expectsQuestion('Are you sure that you want to continue editing this setting?', true)
            ->expectsQuestion('Do you want to set a new value?', true)
            ->expectsQuestion('Enter the value for the setting', 'NewValueè')
            ->expectsQuestion('Are the settings above correct? Note that once confirmed it will be updated in the database!', true)
            ->expectsOutput("Current settings for: 'setting_1'")
            ->expectsOutput("New settings for: 'setting_1'")
            ->assertExitCode(0);

        $this->assertEquals(
            ['key' => 'setting_1', 'value' => 'NewValueè', 'type' => 'string'],
            Setting::first()->only(['key', 'value', 'type'])
        );

        $this->resetSetting();

        $this->artisan(UpdateSettingCommand::class, ['setting' => 'setting_1'])
            ->expectsQuestion('Are you sure that you want to continue editing this setting?', true)
            ->expectsQuestion('Do you want to set a new value?', false)
            ->expectsQuestion('Are the settings above correct? Note that once confirmed it will be updated in the database!', true)
            ->expectsOutput("Current settings for: 'setting_1'")
            ->expectsOutput("New settings for: 'setting_1'")
            ->assertExitCode(0);

        $this->assertEquals(
            ['key' => 'setting_1', 'value' => 'value_1', 'type' => 'string'],
            Setting::first()->only(['key', 'value', 'type'])
        );

        $this->resetSetting(false);

        $this->artisan(UpdateSettingCommand::class, ['setting' => 'setting_1'])
            ->expectsQuestion('Are you sure that you want to continue editing this setting?', true)
            ->expectsQuestion('Do you want to set a new value?', false)
            ->expectsQuestion('Are the settings above correct? Note that once confirmed it will be updated in the database!', true)
            ->expectsOutput("Current settings for: 'setting_1'")
            ->expectsOutput("New settings for: 'setting_1'")
            ->assertExitCode(0);

        $this->assertEquals(
            ['key' => 'setting_1', 'value' => 'value_1', 'type' => 'string', ],
            Setting::first()->only(['key', 'value', 'type'])
        );
    }

    /** @test */
    public function it_can_create_a_setting_if_it_does_not_exists()
    {
        $this->artisan(UpdateSettingCommand::class, ['setting' => 'new_setting'])
            ->expectsQuestion("Setting 'new_setting' does not exist, do you want to create it?", true)
            ->expectsQuestion("Select the type for your setting", 'boolean')
            ->expectsQuestion("Select the value for the setting", 'True')
            ->expectsOutput("Setting new_setting has been created")
            ->assertExitCode(0);

        $this->assertDatabaseHas('settings', ['key' => 'new_setting']);
        $this->assertEquals([
            'key' => 'new_setting',
            'value' => true,
            'type' => 'boolean',
        ], Setting::where('key', 'new_setting')->first()->only('key', 'value', 'type'));

        $this->artisan(UpdateSettingCommand::class, ['setting' => 'new_setting2'])
            ->expectsQuestion("Setting 'new_setting2' does not exist, do you want to create it?", true)
            ->expectsQuestion("Select the type for your setting", 'boolean')
            ->expectsQuestion("Select the value for the setting", 'False')
            ->expectsOutput("Setting new_setting2 has been created")
            ->assertExitCode(0);

        $this->assertDatabaseHas('settings', ['key' => 'new_setting2']);
        $this->assertEquals([
            'key' => 'new_setting2',
            'value' => false,
            'type' => 'boolean',
        ], Setting::where('key', 'new_setting2')->first()->only('key', 'value', 'type'));

        $this->artisan(UpdateSettingCommand::class, ['setting' => 'new_setting3'])
            ->expectsQuestion("Setting 'new_setting3' does not exist, do you want to create it?", true)
            ->expectsQuestion("Select the type for your setting", 'string')
            ->expectsQuestion("Enter the value for the setting", 'MyValue1234')
            ->expectsOutput("Setting new_setting3 has been created")
            ->assertExitCode(0);

        $this->assertDatabaseHas('settings', ['key' => 'new_setting3']);
        $this->assertEquals([
            'key' => 'new_setting3',
            'value' => 'MyValue1234',
            'type' => 'string',
        ], Setting::where('key', 'new_setting3')->first()->only('key', 'value', 'type'));
    }

    /** @test */
    public function it_can_abort_a_setting_update()
    {
        $this->artisan(UpdateSettingCommand::class, ['setting' => 'setting_1'])
            ->expectsQuestion('Are you sure that you want to continue editing this setting?', false)
            ->expectsOutput("Ok, aborted!")
            ->assertExitCode(0);

        $this->assertEquals([
            'key' => 'setting_1',
            'value' => 'value_1',
            'type' => 'string',
        ], Setting::where('key', 'setting_1')->first()->only('key', 'value', 'type'));

        $this->artisan(UpdateSettingCommand::class, ['setting' => 'setting_1'])
            ->expectsQuestion('Are you sure that you want to continue editing this setting?', true)
            ->expectsQuestion('Do you want to set a new value?', true)
            ->expectsQuestion('Enter the value for the setting', 'NewValueè')
            ->expectsQuestion('Are the settings above correct? Note that once confirmed it will be updated in the database!', false)
            ->expectsOutput("Ok, aborted!")
            ->assertExitCode(0);

        $this->assertEquals([
            'key' => 'setting_1',
            'value' => 'value_1',
            'type' => 'string',
        ], Setting::where('key', 'setting_1')->first()->only('key', 'value', 'type'));
    }

    /** @test */
    public function it_can_abort_setting_creation()
    {
        $this->artisan(UpdateSettingCommand::class, ['setting' => 'new_setting'])
            ->expectsQuestion("Setting 'new_setting' does not exist, do you want to create it?", false)
            ->expectsOutput('Ok, stopped!')
            ->assertExitCode(0);
    }

    private function resetSetting($managable = true)
    {
        Setting::first()->update([
            'key' => 'setting_1',
            'value' => 'value_1',
            'type' => 'string',
        ]);
    }
}
