<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProxyAmendmentHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('proxy_amendment_histories', function (Blueprint $table) {


            $table->id('proxyAmendmentHistoryId')->unsignedBigInteger();
            $table->unsignedBigInteger('proxyAmendmentId');
            $table->string('proxyAmendmentFormNo', 10);
            $table->unsignedBigInteger('accountId');
            $table->unsignedBigInteger('assignorId');
            $table->unsignedBigInteger('assigneeId');
            $table->unsignedBigInteger('auditedBy')->nullable();
            $table->unsignedBigInteger('cancelledBy')->nullable();
            $table->timestamp('cancelledAt')->nullable();
            $table->timestamp('auditedAt')->nullable();
            $table->boolean('isActive')->default(true);
            $table->enum('status', ['assigned', 'cancelled', 'for quorum', 'verified', 'unverified'])->default('assigned'); // Assuming a status column to track history state
            $table->string('remarks')->nullable();
            $table->string('reason')->nullable(); // Added reason for cancellation
            $table->string('assignorName');
            $table->string('assignorEmail');
            $table->string('assigneeName');
            $table->string('assigneeEmail');
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->nullable();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('restoredAt')->nullable();
            $table->unsignedBigInteger('createdBy');
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->unsignedBigInteger('deletedBy')->nullable();
            $table->unsignedBigInteger('restoredBy')->nullable();

            // $table->foreign('proxyBodId')->references('proxyBodId')->on('proxy_board_of_directors')->onDelete('restrict');
            $table->foreign('accountId')->references('accountId')->on('stockholder_accounts')->onDelete('restrict');
            $table->foreign('assignorId')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('assigneeId')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('auditedBy')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('cancelledBy')->references('id')->on('users')->onDelete('restrict');
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
        Schema::dropIfExists('proxy_amendment_histories');
    }
}
