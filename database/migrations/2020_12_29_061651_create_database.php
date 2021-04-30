<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDatabase extends Migration
{
    /**
     * Run the migrations.
     * @return void
     */
    public function up()
    {
        Schema::create('urlmarker_users', function (Blueprint $table) {
            $table->id();
            $table->text('name')->unique();
            $table->text('password');
            $table->integer('role')->default(User::NORMAL);
            $table->integer('status')->default(User::ACTIVE);
            $table->rememberToken();
            $table->string('reset_token', 100)->nullable();
            $table->timestampsTz();
        });

        Schema::create('urlmarker_invites', function(Blueprint $table) {
            $table->id();
            $table->text('code')->unique();
            $table->text('notes')->nullable();
            $table->timestampsTz();
            $table->timestampTz('expired_at');
        });

        Schema::create('urlmarker_resets', function(Blueprint $table) {
            $table->id();
            $table->text('email')->unique();
            $table->text('token');
            $table->timestampTz('created_at');
        });

        Schema::create('urlmarker_markers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user');
            $table->text('url');
            $table->text('description')->nullable();
            $table->text('handler')->nullable();
            $table->timestampsTz();
            $table->softDeletesTz('deleted_at', 0);
            $table->foreign('user')->references('id')->on('urlmarker_users');
            $table->index([ 'user', 'url' ]);
        });
    }

    /**
     * Reverse the migrations.
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('urlmarker_users');
        Schema::dropIfExists('urlmarker_invites');
        Schema::dropIfExists('urlmarker_resets');
        Schema::dropIfExists('urlmarker_markers');
    }
}
