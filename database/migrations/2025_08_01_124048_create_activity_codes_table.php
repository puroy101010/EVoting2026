<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityCodesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('activity_codes', function (Blueprint $table) {
            $table->id();
            $table->string('activityCode', 5)->unique();
            $table->string('activity', 100);
            $table->string('category', 50);
            $table->string('severity', 50);
            $table->string('action', 10);
            $table->unsignedSmallInteger('adminLevel');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activity_codes');
    }
}
