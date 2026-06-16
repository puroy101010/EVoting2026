<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAmendmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('amendments', function (Blueprint $table) {
            $table->id('amendmentId')->unsignedBigInteger();
            $table->string('amendmentCode', 10)->unique();
            $table->string('amendmentDesc', 5000);
            $table->string('amendmentLink', 100)->nullable();
            $table->smallInteger('sorter')->nullable();
            $table->boolean('isActive')->default(true);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->nullable();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('restoredAt')->nullable();
            $table->unsignedBigInteger('createdBy');
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->unsignedBigInteger('deletedBy')->nullable();
            $table->unsignedBigInteger('restoredBy')->nullable();

            $table->foreign('createdBy')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('updatedBy')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('deletedBy')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('restoredBy')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('amendments');
    }
}
