<?php

namespace Diligentsquad\Touchfreewaitercustomization\Models;

use Admin\Models\Orders_model;
use Igniter\Flame\Database\Model;

/**
 * Customization Model
 */
class Customization extends Orders_model
{
    /**
     * @var string The database table used by the model.
     */
    public $table = 'diligentsquad_customizationext_customizations';

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
        'belongsTo' => [
            'table_values'=> ['Admin\Models\Tables_model', 'foreignKey'=>'table_number'],
        ],
        'belongsToMany' => [],
        'morphTo' => [],
        'morphOne' => [],
        'morphMany' => [],
    ];
}
