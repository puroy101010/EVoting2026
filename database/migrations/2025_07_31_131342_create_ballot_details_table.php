<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBallotDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('ballot_details', function (Blueprint $table) {
            $table->id('ballotDetailsId')->unsignedBigInteger();
            $table->unsignedInteger('vote');
            $table->unsignedTinyInteger('voidedVote')->nullable();
            $table->string('voidRemarks', 255)->nullable();
            $table->string('ip', 50);
            $table->string('ipVoidBy', 50)->nullable();
            $table->unsignedBigInteger('ballotId');
            $table->unsignedBigInteger('candidateId');
            $table->unsignedBigInteger('voidedBy')->nullable();
            $table->timestamp('voidedAt')->nullable();
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
            $table->foreign('candidateId')->references('candidateId')->on('candidates')->onDelete('restrict');
            $table->foreign('voidedBy')->references('id')->on('users')->onDelete('restrict');
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
        Schema::dropIfExists('ballot_details');
    }
}
