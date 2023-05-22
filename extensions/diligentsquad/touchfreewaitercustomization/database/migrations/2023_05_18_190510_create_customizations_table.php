<?php

namespace Diligentsquad\Touchfreewaitercustomization\Database\Migrations;

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCustomizationsTable extends Migration
{
    public function up()
    {
        Schema::create('diligentsquad_customizationext_customizations', function(Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->bigIncrements('id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('diligentsquad_customizationext_customizations');
    }
}
