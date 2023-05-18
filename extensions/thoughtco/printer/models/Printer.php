<?php

namespace Thoughtco\Printer\Models;

use Admin\Models\Categories_model;
use Admin\Models\Locations_model;
use Admin\Models\Statuses_model;
use ApplicationException;
use Exception;
use Igniter\Flame\Database\Traits\Purgeable;
use Igniter\Flame\Database\Traits\Validation;
use Illuminate\Support\Facades\Log;
use Model;
use Thoughtco\Printer\Classes\Printerfunctions;
use Thoughtco\Printer\Models\Printer_dockets;

class Printer extends Model
{
    use Validation;
    use Purgeable;

    /**
     * @var string The database table name
     */
    protected $table = 'thoughtco_printer';

    public $timestamps = TRUE;

    public $casts = [
        'location_id' => 'integer',
        'printer_settings' => 'serialize',
    ];

    public $relation = [
        'belongsTo' => [
            'location' => 'Admin\Models\Locations_model',
        ],
        'hasMany' => [
            'dockets' => ['Thoughtco\Printer\Models\Printer_dockets', 'delete' => TRUE],
        ],
    ];

    protected $purgeable = ['dockets'];

    public $rules = [
        ['label', 'lang:thoughtco.printer::default.label_label', 'required|string'],
        ['location_id', 'lang:thoughtco.printer::default.label_location', 'required|integer'],
        ['printer_settings.copies', 'lang:thoughtco.printer::default.label_copies', 'required|integer|min:1'],
        ['printer_settings.characters_per_line', 'lang:thoughtco.printer::default.label_characters_per_line', 'required|integer|min:32'],
        ['printer_settings.codepage', 'lang:thoughtco.printer::default.label_codepage', 'required|integer'],
        ['printer_settings.autoprint_interval', 'lang:thoughtco.printer::default.label_autoprint_interval', 'required|integer|min:10'],
        ['printer_settings.font.default_vertical', 'lang:thoughtco.printer::default.label_font_default', 'required|integer|min:1|max:7'],
        ['printer_settings.font.default_horizontal', 'lang:thoughtco.printer::default.label_font_default', 'required|integer|min:1|max:7'],
        ['printer_settings.font.default_line', 'lang:thoughtco.printer::default.label_font_default', 'required|integer|min:1|max:255'],
        ['printer_settings.font.heading1_vertical', 'lang:thoughtco.printer::default.label_font_heading1', 'required|integer|min:1|max:7'],
        ['printer_settings.font.heading1_horizontal', 'lang:thoughtco.printer::default.label_font_heading1', 'required|integer|min:1|max:7'],
        ['printer_settings.font.heading1_line', 'lang:thoughtco.printer::default.label_font_heading1', 'required|integer|min:1|max:255'],
        ['printer_settings.font.heading2_vertical', 'lang:thoughtco.printer::default.label_font_heading2', 'required|integer|min:1|max:7'],
        ['printer_settings.font.heading2_horizontal', 'lang:thoughtco.printer::default.label_font_heading2', 'required|integer|min:1|max:7'],
        ['printer_settings.font.heading2_line', 'lang:thoughtco.printer::default.label_font_heading2', 'required|integer|min:1|max:255'],
        ['printer_settings.font.heading3_vertical', 'lang:thoughtco.printer::default.label_font_heading3', 'required|integer|min:1|max:7'],
        ['printer_settings.font.heading3_horizontal', 'lang:thoughtco.printer::default.label_font_heading3', 'required|integer|min:1|max:7'],
        ['printer_settings.font.heading3_line', 'lang:thoughtco.printer::default.label_font_heading3', 'required|integer|min:1|max:255'],
        ['printer_settings.font.heading4_vertical', 'lang:thoughtco.printer::default.label_font_heading4', 'required|integer|min:1|max:7'],
        ['printer_settings.font.heading4_horizontal', 'lang:thoughtco.printer::default.label_font_heading4', 'required|integer|min:1|max:7'],
        ['printer_settings.font.heading4_line', 'lang:thoughtco.printer::default.label_font_heading4', 'required|integer|min:1|max:255'],
        ['printer_settings.font.heading5_vertical', 'lang:thoughtco.printer::default.label_font_heading5', 'required|integer|min:1|max:7'],
        ['printer_settings.font.heading5_horizontal', 'lang:thoughtco.printer::default.label_font_heading5', 'required|integer|min:1|max:7'],
        ['printer_settings.font.heading5_line', 'lang:thoughtco.printer::default.label_font_heading5', 'required|integer|min:1|max:255'],
        ['printer_settings.font.heading6_vertical', 'lang:thoughtco.printer::default.label_font_heading6', 'required|integer|min:1|max:7'],
        ['printer_settings.font.heading6_horizontal', 'lang:thoughtco.printer::default.label_font_heading6', 'required|integer|min:1|max:7'],
        ['printer_settings.font.heading6_line', 'lang:thoughtco.printer::default.label_font_heading6', 'required|integer|min:1|max:255'],
    ];

    // on save
    public function save(?array $options = NULL, $sessionKey = NULL)
    {
	    Printerfunctions::clearTemplates();
	    return parent::save($options, $sessionKey);
    }

    protected function afterSave()
    {
        $this->restorePurgedValues();

        if (array_key_exists('dockets', $this->attributes)) {
            foreach ($this->dockets as $docket) {
                $docketModel = Printer_dockets::find($docket['printer_docket_id']);
                if ($docketModel) {
                    $docketModel->priority = $docket['priority'];
                    $docketModel->save();
                }
            }
        }

    }

    // location options
    public static function getLocationIdOptions()
    {
	    $locations = [];
	    foreach (Locations_model::all() as $location){
	    	$locations[$location->location_id] = $location->location_name;
	    };
	    return $locations;
    }

    // status options
    public static function getStatusOptions($allowUnchanged = true)
    {
	    $statuses = $allowUnchanged ? [
		    '-1' => lang('thoughtco.printer::default.leave_unchanged'),
	    ] : [];

	    Statuses_model::listStatuses()->each(function($status) use (&$statuses) {
	    	if ($status->status_for == 'order'){
		    	$statuses[$status->status_id] = $status->status_name;
		    }
	    });

	    return $statuses;
    }

    // cut options
    public static function getCutOptions()
    {
        return [
            "0" => lang('thoughtco.printer::default.option_cut_none'),
            "1" => lang('thoughtco.printer::default.option_cut_last'),
            "2" => lang('thoughtco.printer::default.option_cut_every'),
        ];
    }

    // encoding options
    public static function getEncodingOptions()
    {
        return [
            "windows-1250" => "windows-1250",
            "windows-1251" => "windows-1251",
            "windows-1252" => "windows-1252",
            "windows-1253" => "windows-1253",
            "windows-1254" => "windows-1254",
            "windows-1255" => "windows-1255",
            "windows-1256" => "windows-1256",
            "windows-1257" => "windows-1257",
            "windows-1258" => "windows-1258",
            "iso-8859-2" => "iso-8859-2",
            "iso-8859-3" => "iso-8859-3",
            "iso-8859-4" => "iso-8859-4",
            "iso-8859-5" => "iso-8859-5",
            "iso-8859-6" => "iso-8859-6",
            "iso-8859-7" => "iso-8859-7",
            "iso-8859-8" => "iso-8859-8",
            "iso-8859-10" => "iso-8859-10",
            "iso-8859-13" => "iso-8859-13",
            "iso-8859-14" => "iso-8859-14",
            "iso-8859-15" => "iso-8859-15",
            "iso-8859-16" => "iso-8859-16",
        ];
    }

    // epson language options
    public static function getLanguageOptions()
    {
        return [
            "en" => "English",
            "de" => "German",
            "fr" => "French",
            "it" => "Italian",
            "es" => "Spanish",
            "ja" => "Japanese",
            "ko" => "Korean",
            "zh-hans" => "Simplified Chinese",
            "zh-hant" => "Traditional Chinese",
            "th" => "Thai",
            "mul" => "Multiple (UTF-8)",
        ];
    }
}
