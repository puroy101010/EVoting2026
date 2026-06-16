<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivityLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id('logId');
            $table->string('remarks', 5000)->nullable();
            $table->json('data')->nullable();
            $table->string('email', 50)->nullable();
            $table->string('accountNo', 10)->nullable();
            $table->string('ip', 50);
            $table->string('activityCode', 5);
            $table->unsignedBigInteger('userId')->nullable();
            $table->unsignedBigInteger('ballotId')->nullable();
            $table->unsignedBigInteger('confirmationId')->nullable();
            $table->unsignedBigInteger('amendmentId')->nullable();
            $table->unsignedBigInteger('agendaId')->nullable();
            $table->unsignedBigInteger('nonMemberId')->nullable();


            $table->unsignedBigInteger('accountId')->nullable();
            $table->unsignedBigInteger('ballotDetailsId')->nullable();
            $table->unsignedBigInteger('documentId')->nullable();
            $table->unsignedBigInteger('candidateId')->nullable();
            $table->unsignedBigInteger('roleId')->nullable();
            $table->unsignedBigInteger('proxyAssignee')->nullable();
            $table->unsignedBigInteger('proxyAssigner')->nullable();

            $table->unsignedBigInteger('proxyAuditedId')->nullable();
            $table->unsignedBigInteger('proxyBodId')->nullable();
            $table->unsignedBigInteger('proxyAmendmentId')->nullable();
            $table->unsignedBigInteger('proxyBodHistoryId')->nullable();
            $table->unsignedBigInteger('proxyAmendmentHistoryId')->nullable();
            $table->boolean('isActive')->default(true);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->nullable();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('restoredAt')->nullable();
            $table->unsignedBigInteger('createdBy')->nullable();
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->unsignedBigInteger('deletedBy')->nullable();
            $table->unsignedBigInteger('restoredBy')->nullable();

            $table->foreign('activityCode')->references('activityCode')->on('activity_codes')->onDelete('restrict');
            $table->foreign('userId')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('ballotId')->references('ballotId')->on('ballots')->onDelete('restrict');
            $table->foreign('confirmationId')->references('confirmationId')->on('ballot_confirmations')->onDelete('restrict');
            $table->foreign('amendmentId')->references('amendmentId')->on('amendments')->onDelete('restrict');
            $table->foreign('agendaId')->references('agendaId')->on('agendas')->onDelete('restrict');
            $table->foreign('nonMemberId')->references('nonMemberId')->on('nonmember_accounts')->onDelete('restrict');


            $table->foreign('accountId')->references('accountId')->on('stockholder_accounts')->onDelete('restrict');
            $table->foreign('ballotDetailsId')->references('ballotDetailsId')->on('ballot_details')->onDelete('restrict');
            $table->foreign('documentId')->references('documentId')->on('documents')->onDelete('restrict');
            $table->foreign('roleId')->references('id')->on('roles')->onDelete('restrict');
            $table->foreign('candidateId')->references('candidateId')->on('candidates')->onDelete('restrict');
            $table->foreign('proxyAssignee')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('proxyAssigner')->references('id')->on('users')->onDelete('restrict');

            $table->foreign('proxyAuditedId')->references('id')->on('users')->onDelete('restrict');
            // $table->foreign('proxyBodId')->references('proxyBodId')->on('proxy_board_of_directors')->onDelete('set null');
            // $table->foreign('proxyAmendmentId')->references('proxyAmendmentId')->on('proxy_amendments')->onDelete('set null');
            $table->foreign('proxyBodHistoryId')->references('proxyBodHistoryId')->on('proxy_board_of_director_histories')->onDelete('restrict');
            $table->foreign('proxyAmendmentHistoryId')->references('proxyAmendmentHistoryId')->on('proxy_amendment_histories')->onDelete('restrict');
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
        Schema::dropIfExists('activity_logs');
    }
}
