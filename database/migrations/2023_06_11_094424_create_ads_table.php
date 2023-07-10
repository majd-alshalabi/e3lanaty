<?php




use Illuminate\Database\Migrations\Migration;


use Illuminate\Database\Schema\Blueprint;


use Illuminate\Support\Facades\Schema;




return new class extends Migration {
    /**
     * Run the migrations.
     */

    public function up(): void
    {
        Schema::create('ads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->text('extra_description')->nullable();
            $table->double('price');
            $table->integer('type');
            $table->integer('status')->default(0);
            $table->boolean('stared')->default(false);
            $table->boolean('admin')->default(false);
            $table->integer('priorty')->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users');
            $table->string('link');
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};