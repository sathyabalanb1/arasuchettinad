<?php

namespace Diligentsquad\Gst\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGstModelsTable extends Migration
{
    public function up()
    {
       Schema::table('order_menus', function (Blueprint $table) {
            $table->decimal('cgst')->default(0.00)->nullable();
            $table->decimal('sgst')->default(0.00)->nullable();
            $table->decimal('vat')->default(0.00)->nullable();
            $table->decimal('total_amt')->default(0.00)->nullable();
            $table->boolean('is_item_price_include_tax')->default(0);
       });
    }

    public function down()
    {
       /* if (Schema::hasColumn('order_menus', 'cgst'))
        {
            Schema::table('order_menus', function (Blueprint $table) {
                $table->dropColumn('cgst');
            });
        }
        if (Schema::hasColumn('order_menus', 'sgst'))
        {
            Schema::table('order_menus', function (Blueprint $table) {
                $table->dropColumn('sgst');
            });
        }
        if (Schema::hasColumn('order_menus', 'vat'))
        {
            Schema::table('order_menus', function (Blueprint $table) {
                $table->dropColumn('vat');
            });
        }

        if (Schema::hasColumn('order_menus', 'total_amt'))
        {
            Schema::table('order_menus', function (Blueprint $table) {
                $table->dropColumn('total_amt');
            });
        }
        if (Schema::hasColumn('order_menus', 'is_item_price_include_tax'))
        {
            Schema::table('order_menus', function (Blueprint $table) {
                $table->dropColumn('is_item_price_include_tax');
            });
        }*/
    }
}
