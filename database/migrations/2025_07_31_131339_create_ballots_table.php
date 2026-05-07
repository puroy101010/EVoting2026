<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBallotsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('ballots', function (Blueprint $table) {
            $table->id('ballotId')->unsignedBigInteger();
            $table->string('ballotNo', 30);
            $table->string('ballotKey', 50)->unique();
            $table->string('email', 100);
            $table->string('ip', 50);
            $table->text('availableBodAccounts')->nullable()->comment('List of stockholder account IDs available for voting for Board of Directors');
            $table->text('availableAmendmentAccounts')->nullable()->comment('List of stockholder account IDs available for voting for Amendment');
            $table->text('availableAccounts')->nullable()->comment("List of stockholder account IDs available for voting with different revocation types ['bod', 'amendment', 'both', 'none']");
            $table->enum('ballotType', ['person', 'proxy']);
            $table->enum('authorizedVoter', ['stockholder', 'corp-rep'])->nullable();
            $table->enum('role', ['stockholder', 'corp-rep', 'non-member']);
            $table->integer('availableVotesBod')->comment('Total number of votes available for Board of Directors. This is the sum of shares multiplied by votes per share.');
            $table->integer('availableVotesAmendment')->comment('Total number of votes available for Amendment. This is the sum of shares multiplied by votes per share.');
            $table->integer('castedVotes')->nullable();
            $table->integer('unusedVotesBod')->nullable();
            $table->integer('unusedVotesAmendment')->nullable();
            $table->enum('revoked', ['bod', 'amendment', 'both', 'none'])->default('none');
            $table->boolean('isSubmitted')->default(false);
            $table->boolean('isViewed')->default(false);
            $table->timestamp('submittedAt')->nullable();
            $table->unsignedBigInteger('confirmationId')->nullable();
            $table->boolean('isActive')->default(true);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->nullable();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('restoredAt')->nullable();
            $table->unsignedBigInteger('createdBy');
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->unsignedBigInteger('deletedBy')->nullable();
            $table->unsignedBigInteger('restoredBy')->nullable();


            $table->foreign('createdBy')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updatedBy')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('deletedBy')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('restoredBy')->references('id')->on('users')->onDelete('restrict');

            // Foreign key constraints can be added here if necessary
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ballots');
    }
}
