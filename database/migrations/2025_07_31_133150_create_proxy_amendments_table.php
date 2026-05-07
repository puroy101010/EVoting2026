<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProxyAmendmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('proxy_amendments', function (Blueprint $table) {

            $table->id('proxyAmendmentId')->unsignedBigInteger();
            $table->string('proxyAmendmentFormNo', 10)->unique();
            $table->unsignedBigInteger('accountId')->unique();
            $table->unsignedBigInteger('assignorId');
            $table->unsignedBigInteger('assigneeId');
            $table->unsignedBigInteger('auditedBy')->nullable();
            $table->timestamp('auditedAt')->nullable();
            // $table->boolean('isActive')->default(true);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->nullable();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('restoredAt')->nullable();
            $table->unsignedBigInteger('createdBy');
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->unsignedBigInteger('deletedBy')->nullable();
            $table->unsignedBigInteger('restoredBy')->nullable();

            $table->foreign('accountId')->references('accountId')->on('stockholder_accounts')->onDelete('restrict');
            $table->foreign('assignorId')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('assigneeId')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('auditedBy')->references('id')->on('users')->onDelete('restrict');
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
        Schema::dropIfExists('proxy_amendments');
    }
}
