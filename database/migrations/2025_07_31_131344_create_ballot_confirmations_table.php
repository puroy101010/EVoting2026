<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBallotConfirmationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {



        Schema::create('ballot_confirmations', function (Blueprint $table) {
            $table->id('confirmationId');
            $table->enum('ballotType', ['person', 'proxy']);
            $table->string('data', 5000)->comment('Summary details of the submitted ballot in JSON format.');
            $table->string('availableVotes', 5000)->comment('The available votes at the time of submission, in JSON format, listing stockholder account IDs.');
            $table->string('remarks', 500)->nullable()->default(null);
            $table->string('email', 50);
            $table->string('ip', 50);
            $table->unsignedBigInteger('ballotId');
            $table->boolean('isValidBallot')->default(false);
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
        Schema::dropIfExists('ballot_confirmations');
    }
}
