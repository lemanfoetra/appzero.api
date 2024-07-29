<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateApiModulesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('api_modules', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('method')->nullable();
            $table->string('url')->nullable();
            $table->text('description')->nullable();
            $table->text('header')->nullable();
            $table->text('body')->nullable();
            $table->text('response')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('api_modules');
    }
}
