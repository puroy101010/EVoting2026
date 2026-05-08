<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockholdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {



        Schema::create('stockholders', function (Blueprint $table) {
            $table->id('stockholderId')->unsignedBigInteger();
            $table->string('accountNo', 10)->unique();
            $table->string('stockholder', 100);
            $table->enum('accountType', ['indv', 'corp']);
            $table->enum('voteInPerson', ['stockholder', 'corp-rep'])->default('stockholder');
            $table->string('authorizedSignatory')->nullable()->comment('Name of the authorized signatory for corporate accounts');
            $table->unsignedBigInteger('userId')->comment('User ID of the stockholder');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->nullable();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('restoredAt')->nullable();
            $table->unsignedBigInteger('createdBy');
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->unsignedBigInteger('deletedBy')->nullable();
            $table->unsignedBigInteger('restoredBy')->nullable();

            $table->foreign('userId')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('createdBy')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updatedBy')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('deletedBy')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('restoredBy')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stockholders');
    }
}
