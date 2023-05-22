<?php

namespace Diligentsquad\Gst\Models;

class GstSettings extends \Igniter\Flame\Database\Model
{
    public $implement = [\System\Actions\SettingsModel::class];

    // A unique code
    public $settingsCode = 'diligentsquad_gst_settings';

    // Reference to field configuration
    public $settingsFieldsConfig = 'gstsettings';

}
