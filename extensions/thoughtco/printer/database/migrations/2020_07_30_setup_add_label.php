<?php namespace Thoughtco\Printer\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLabel extends Migration
{
    public function up()
    {
        Schema::table('thoughtco_printer', function (Blueprint $table) {
            $table->text('label');
        });  
    }
}