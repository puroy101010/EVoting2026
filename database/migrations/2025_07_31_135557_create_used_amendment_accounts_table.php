<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsedAmendmentAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('used_amendment_accounts', function (Blueprint $table) {
            $table->id('usedAmendmentAccountId')->unsignedBigInteger();
            $table->unsignedBigInteger('ballotId');
            $table->unsignedBigInteger('accountId')->unique();
            $table->boolean('isActive')->default(true);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->nullable();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('restoredAt')->nullable();
            $table->unsignedBigInteger('createdBy');
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->unsignedBigInteger('deletedBy')->nullable();
            $table->unsignedBigInteger('restoredBy')->nullable();

            $table->foreign('ballotId')->references('ballotId')->on('ballots')->onDelete('restrict');
            $table->foreign('accountId')->references('accountId')->on('stockholder_accounts')->onDelete('restrict');
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
        Schema::dropIfExists('used_amendment_accounts');
    }
}
