<?php

namespace TakeTheLead\Settings\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use TakeTheLead\Settings\Models\Setting;

class SettingWasSaved
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public Setting $setting;

    public function __construct(Setting $setting)
    {
        $this->setting = $setting;
    }
}
