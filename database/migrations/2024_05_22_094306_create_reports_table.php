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
        Schema::create('reports', function (Blueprint $table) {

            $table->id();
            $table->string('sku');
            $table->integer('new_quantity');
            $table->integer('last_quantity');
            $table->foreignId('created_by');
            $table->foreignId('inventory_id');

            $table->boolean('is_deleted')->default('0');
            $table->time('hour');
            $table->timestamps();

            //? set relation
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('inventory_id')->references('id')->on('inventories')->onUpdate('cascade')->onDelete('cascade');


        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('reports');
    }
};
