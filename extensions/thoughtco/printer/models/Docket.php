<?php

namespace Thoughtco\Printer\Models;

use Igniter\Flame\Database\Traits\Validation;
use Model;
use Thoughtco\Printer\Classes\Printerfunctions;
use Thoughtco\Printer\Models\Printer;

class Docket extends Model
{
    use Validation;

    /**
     * @var string The database table name
     */
    protected $table = 'thoughtco_printer_dockets';

    public $timestamps = TRUE;

    public $casts = [
        'docket_settings' => 'serialize',
    ];
    
    protected $fillable = ['label', 'docket_settings', 'is_enabled'];
    
    public $rules = [
        ['label', 'lang:thoughtco.printer::default.label_label', 'required|string'],
        ['docket_settings.lines_before', 'lang:thoughtco.printer::default.lines_before', 'required|integer|min:0'],
        ['docket_settings.lines_after', 'lang:thoughtco.printer::default.lines_after', 'required|integer|min:0'],
    ];
    
    public function afterDelete()
    {
        $myId = $this->id;
        Printer::all()
        ->each(function($printer) use($myId){
           $printer->dockets->each(function($docket) use($myId) {
               if ($docket->docket_id == $myId)
                   $docket->delete();
           });
        });
    }

    // on save
    public function save(?array $options = NULL, $sessionKey = NULL)
    {
	    Printerfunctions::clearTemplates();
	    parent::save($options, $sessionKey);
    }
    
    public static function getRecordEditorOptions()
    {
        return self::selectRaw('id, label AS display_name')
            ->dropdown('display_name');
    }
}
