<?php

namespace Thoughtco\Printer\Models;

use Admin\Models\Categories_model;
use Igniter\Flame\Database\Traits\Validation;
use Model;

/**
 * Printer_dockets Model Class
 */
class Printer_dockets extends Model
{
    use Validation;

    /**
     * @var string The database table name
     */
    protected $table = 'thoughtco_printer_has_dockets';

    /**
     * @var string The database table primary key
     */
    protected $primaryKey = 'printer_docket_id';

    protected $fillable = ['printer_id', 'docket_id', 'settings'];

    public $casts = [
        'printer_id' => 'integer',
        'docket_id' => 'integer',
        'settings' => 'serialize',
    ];

    public $relation = [
        'belongsTo' => [
            'printer' => ['Thoughtco\Printer\Models\Printer'],
            'docket' => ['Thoughtco\Printer\Models\Docket'],
        ],
    ];

    public $appends = [];

    public $rules = [];

    public $with = ['docket'];

    public function getOptionNameAttribute()
    {
        return optional($this->docket)->label;
    }

    public function getOptionContextAttribute()
    {
        $contexts = self::getContextOptions();
        foreach ($contexts as $c=>$v) {
            if ($this->settings['contexts'] == $c)
                return lang($v);
        }
        return '';
    }

    public function getOptionCategoriesAttribute()
    {
        $cats = self::getCategoryOptions();
        $selected = [];
        if (isset($this->settings['categories'])) {
            foreach ($cats as $c=>$v) {
                foreach ($this->settings['categories'] as $sel) {
                    if ($sel == $c)
                        $selected[] = $v;
                }
            }
        }
        if (!count($selected))
            $selected[] = 'All';

        return $selected;
    }

    // category options
    public static function getCategoryOptions()
    {
	    $categories = [];
	    foreach (Categories_model::all() as $category){
	    	$categories[$category->category_id] = $category->name;
	    };
	    return $categories;
    }

    // context options
    public static function getContextOptions()
    {
	    return [
          '0' => 'lang:thoughtco.printer::default.option_all',
          '1' => 'lang:thoughtco.printer::default.option_print',
          '2' => 'lang:thoughtco.printer::default.option_autoprint',
        ];
    }
}
