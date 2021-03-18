<?php

namespace TakeTheLead\Settings\Tests\Feature\Console;

use TakeTheLead\Settings\Console\ListSettingsCommand;
use TakeTheLead\Settings\Models\Setting;
use TakeTheLead\Settings\Tests\TestCase;

class ListSettingsCommandTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        Setting::factory()->createMany([
            [
                'key' => 'setting_1',
                'value' => 'value_1',
                'type' => 'string',
            ],
            [
                'key' => 'setting_2',
                'value' => 'value_2',
                'type' => 'string',
            ],
            [
                'key' => 'setting_3',
                'value' => true,
                'type' => 'boolean',
            ],
            [
                'key' => 'setting_4',
                'value' => false,
                'type' => 'boolean',
            ],
            [
                'key' => 'setting_5',
                'value' => '{"ttl": "Is Awesome!, "LARAVEL": true}',
                'type' => 'json',
            ],
        ]);
    }

    /** @test */
    public function it_can_list_settings()
    {
        $this->artisan(ListSettingsCommand::class)
            ->expectsOutput('Listing 5 settings')
            ->assertExitCode(0);
    }
}
