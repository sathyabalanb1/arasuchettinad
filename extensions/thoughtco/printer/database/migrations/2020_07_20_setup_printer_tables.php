<?php namespace Thoughtco\Printer\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class PrinterTables extends Migration
{
    public function up()
    {
        Schema::create('thoughtco_printer', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('location_id');
            $table->mediumText('printer_settings');
            $table->timestamps();
        });
        
        Schema::table('menus', function (Blueprint $table) {
            $table->text('print_docket');
        });  
        
        Schema::table('menu_option_values', function (Blueprint $table) {
            $table->text('print_docket');
        });
                
    }

    public function down()
    {
        Schema::dropIfExists('thoughtco_printer');
        
        if (Schema::hasColumn('menus', 'print_docket'))
        {
	        Schema::table('menus', function (Blueprint $table) {
	            $table->dropColumn('print_docket');
	        });  
        }
        
        if (Schema::hasColumn('menu_option_values', 'print_docket'))
        {
	        Schema::table('menu_option_values', function (Blueprint $table) {
	            $table->dropColumn('print_docket');
	        });        
        }
    }
}