<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {


        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('email', 100)->nullable()->default(null);
            $table->string('password', 500)->nullable()->default(null);
            $table->string('otp', 255)->nullable()->default(null);
            $table->boolean('otpValid')->default(false);
            $table->timestamp('otpCreatedAt')->nullable()->default(null);
            $table->enum('role', ['superadmin', 'admin', 'stockholder', 'corp-rep', 'non-member']);
            $table->timestamp('createdAt')->useCurrent();
            $table->timestamp('updatedAt')->nullable()->default(null);
            $table->timestamp('deletedAt')->nullable()->default(null);
            $table->timestamp('restoredAt')->nullable()->default(null);
            $table->unsignedBigInteger('createdBy');
            $table->unsignedBigInteger('updatedBy')->nullable()->default(null);
            $table->unsignedBigInteger('deletedBy')->nullable()->default(null);
            $table->unsignedBigInteger('restoredBy')->nullable()->default(null);

            $table->foreign('createdBy')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updatedBy')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('deletedBy')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('restoredBy')->references('id')->on('users')->onDelete('restrict');
        });

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
}
