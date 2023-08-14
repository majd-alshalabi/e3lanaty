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
            $table->text('name');
            $table->text('price')->nullable();
            $table->integer('type')->nullable();
            $table->integer('ads_type');
            $table->integer('status')->default(0);
            $table->boolean('stared')->default(false);
            $table->boolean('admin')->default(false);
            $table->boolean('service')->default(false);
            $table->integer('priorty')->default(0);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('set null');
            $table->string('link')->nullable();
            $table->timestamps();
        });
    }
    public function down(): void
    {
        Schema::dropIfExists('ads');
    }
};