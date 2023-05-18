<?php namespace Thoughtco\Printer\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddStatus extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('thoughtco_printer', 'is_enabled'))
        {
            Schema::table('thoughtco_printer', function (Blueprint $table) {
                $table->boolean('is_enabled')->default(true);
            });
        }  
    }
}