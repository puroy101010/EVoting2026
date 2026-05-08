<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockholderAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('stockholder_accounts', function (Blueprint $table) {
            $table->id('accountId')->unsignedBigInteger();
            $table->string('accountKey', 10)->unique();
            $table->tinyInteger('suffix')->unsigned();
            $table->string('corpRep', 100)->nullable()->default(null);
            $table->string('authSignatory', 255)->nullable()->default(null);
            $table->boolean('isDelinquent')->default(false);
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('stockholderId');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->nullable();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('restoredAt')->nullable();
            $table->unsignedBigInteger('createdBy');
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->unsignedBigInteger('deletedBy')->nullable();
            $table->unsignedBigInteger('restoredBy')->nullable();

            $table->foreign('userId')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('stockholderId')->references('stockholderId')->on('stockholders')->onDelete('restrict');
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
        Schema::dropIfExists('stockholder_accounts');
    }
}
