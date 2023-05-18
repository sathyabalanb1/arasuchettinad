<?php

namespace Thoughtco\Dinein\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTableNumberToOrdersTable extends Migration
{
    public function up()
    {
        if (!Schema::hasColumn('orders', 'table_number'))
        {
            Schema::table('orders', function (Blueprint $table) {
                $table->text('table_number')->nullable();
                $table->dateTime('table_closed_at')->nullable();
                $table->text('table_count')->nullable();
            });
        }
    }
}
