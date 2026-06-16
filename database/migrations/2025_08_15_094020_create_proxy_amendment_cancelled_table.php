<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProxyAmendmentCancelledTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('proxy_amendment_cancelled', function (Blueprint $table) {
            $table->id('proxyAmendmentCancelledId')->unsignedBigInteger();
            $table->string('proxyAmendmentFormNo', 10);
            $table->enum('reason', ['quorum', 'encoding_error']);
            $table->string('remarks')->nullable()->default(null);

            $table->string('assignorName')->nullable();
            $table->string('assignorEmail')->nullable();
            $table->string('assigneeName')->nullable();
            $table->string('assigneeEmail')->nullable();

            $table->unsignedBigInteger('proxyAmendmentId')->unique();
            $table->unsignedBigInteger('accountId');
            $table->unsignedBigInteger('assignorId');
            $table->unsignedBigInteger('assigneeId');
            $table->unsignedBigInteger('auditedBy')->nullable();
            $table->unsignedBigInteger('cancelledBy');

            $table->timestamp('auditedAt')->nullable();
            // $table->boolean('isActive')->default(true);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->nullable();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('restoredAt')->nullable();
            $table->timestamp('cancelledAt')->useCurrent();
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
        Schema::dropIfExists('proxy_amendment_cancelled');
    }
}
