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
            // $table->boolean('allowSpaDownload')->default(false);
            // $table->boolean('allowProxyDownload')->default(false);
            // $table->boolean('spaAudited')->default(false);
            // $table->boolean('proxyAudited')->default(false);
            $table->boolean('isDelinquent')->default(false);
            $table->unsignedBigInteger('userId');
            $table->unsignedBigInteger('stockholderId');
            // $table->unsignedBigInteger('spaAssignee')->nullable();
            // $table->unsignedBigInteger('spaAuditedBy')->nullable();
            // $table->unsignedBigInteger('proxyAssigner')->nullable();
            // $table->unsignedBigInteger('spaAssigner')->nullable();
            // $table->unsignedBigInteger('proxyAssignee')->nullable();
            // $table->unsignedBigInteger('proxyAuditedBy')->nullable();
            // $table->timestamp('proxyAuditedAt')->nullable();
            // $table->timestamp('spaAuditedAt')->nullable();
            // $table->boolean('isActive')->default(true);
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
            // $table->foreign('spaAssignee')->references('accountId')->on('stockholder_accounts')->onDelete('restrict');
            // $table->foreign('spaAuditedBy')->references('id')->on('users')->onDelete('restrict');
            // $table->foreign('proxyAssigner')->references('accountId')->on('stockholder_accounts')->onDelete('restrict');
            // $table->foreign('spaAssigner')->references('accountId')->on('stockholder_accounts')->onDelete('restrict');
            // $table->foreign('proxyAssignee')->references('accountId')->on('stockholder_accounts')->onDelete('restrict');
            // $table->foreign('proxyAuditedBy')->references('id')->on('users')->onDelete('restrict');
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
