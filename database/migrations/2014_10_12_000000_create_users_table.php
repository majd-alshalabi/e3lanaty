<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('phone_number')->nullable();
            $table->string('location')->nullable();
            $table->string('about_me')->nullable();
            $table->string('image')->nullable();
            $table->integer('rate')->nullable();
            $table->boolean('blocked')->default(false);
            $table->boolean('admin')->default(false);
            $table->boolean('deleted')->default(false);
            $table->integer('type_of_account')->default(0);
            $table->integer('point')->default(0);
            $table->string('email')->unique();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
