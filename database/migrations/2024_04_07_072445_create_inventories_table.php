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
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->integer('quantity');
            // $table->string('sku');
            // $table->string('item_type');
            $table->foreignId('type_id');

            $table->longText('detailed_description');
            $table->integer('reserved')->default(0);
            $table->boolean('is_deleted')->default('0');

            $table->foreign('type_id')->references('id')->on('item_types')->onUpdate('cascade')->onDelete('cascade');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inventories');
    }
};
