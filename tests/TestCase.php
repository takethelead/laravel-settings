<?php

namespace TakeTheLead\Settings\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use TakeTheLead\Settings\EventServiceProvider;
use TakeTheLead\Settings\SettingsServiceProvider;

class TestCase extends OrchestraTestCase
{
    use RefreshDatabase;
    
    protected function getPackageProviders($app)
    {
        return [
            SettingsServiceProvider::class,
            EventServiceProvider::class,
        ];
    }
}
