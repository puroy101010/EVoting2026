<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBallotAgendasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('ballot_agendas', function (Blueprint $table) {
            $table->id('ballotAgendaId')->unsignedBigInteger();
            $table->unsignedSmallInteger('favor')->comment('Number of favor votes, 1-5000');
            $table->unsignedSmallInteger('notFavor')->comment('Number of not favor votes, 1-5000');
            $table->unsignedSmallInteger('abstain')->comment('Number of abstain votes, 1-5000');
            $table->unsignedBigInteger('agendaId');
            $table->unsignedBigInteger('ballotId');
            $table->boolean('isActive')->default(true);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->nullable();
            $table->timestamp('deletedAt')->nullable();
            $table->timestamp('restoredAt')->nullable();
            $table->unsignedBigInteger('createdBy');
            $table->unsignedBigInteger('updatedBy')->nullable();
            $table->unsignedBigInteger('deletedBy')->nullable();
            $table->unsignedBigInteger('restoredBy')->nullable();

            $table->foreign('agendaId')->references('agendaId')->on('agendas')->onDelete('restrict');
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
        Schema::dropIfExists('ballot_agendas');
    }
}
