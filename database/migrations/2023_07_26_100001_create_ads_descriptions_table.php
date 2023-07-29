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
        Schema::create('ads_descriptions', function (Blueprint $table) {
            $table->id();
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->text('html_code')->nullable();
            $table->unsignedBigInteger('ads_id')->nullable();
            $table->foreign('ads_id')->references('id')->on('ads')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ads_descriptions');
    }
};
