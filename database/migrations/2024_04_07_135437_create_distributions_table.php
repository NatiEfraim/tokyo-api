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
        Schema::create('distributions', function (Blueprint $table) {
            $table->id();
            $table->longText('comment');
            $table->integer('status')->default(0);
            $table->integer('quantity');
            $table->foreignId('inventory_id');
            $table->boolean('is_deleted')->default('0');
            $table->timestamps();

            $table->foreign('inventory_id')->references('id')->on('inventories')->onUpdate('cascade')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('distributions');
    }
};
