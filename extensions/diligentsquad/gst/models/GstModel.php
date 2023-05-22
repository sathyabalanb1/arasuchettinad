<?php

namespace Diligentsquad\Gst\Models;

use Igniter\Flame\Database\Model;
use Illuminate\Support\Facades\DB;


/**
 * gst_model Model
 */
class GstModel extends Model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'settings';

    /**
     * @var array fillable fields
     */
    protected $fillable = [];

    public $timestamps = TRUE;

    /**
     * @var array Relations
     */
    public $relation = [
        'hasOne' => [],
        'hasMany' => [],
        'belongsTo' => [],
        'belongsToMany' => [],
        'morphTo' => [],
        'morphOne' => [],
        'morphMany' => [],
    ];

    public function getTaxOptions()
    {
        return DB::table('extension_settings')
                ->select('data')
                ->where('item', 'diligentsquad_gst_settings')
                ->get();
    }
}
