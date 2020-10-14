<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class CreateSettingsTable extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('key');
            $table->text('value');
            $table->string('type');
            $table->timestamps();

            $table->index('key');
            $table->index('type');
        });

        Cache::forget('settings_table_exists');
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
}
