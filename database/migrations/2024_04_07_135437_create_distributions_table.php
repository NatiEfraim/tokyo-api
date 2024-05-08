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
            $table->longText('comment')->nullable();
            //? 0--> pendig. 1--> approved. 2 -->canceld
            $table->integer('status')->default(0);
            $table->integer('quantity');
            $table->foreignId('inventory_id');
            $table->foreignId('department_id');
            $table->foreignId('created_by');
            $table->foreignId('created_for');

            $table->boolean('is_deleted')->default('0');
            $table->timestamps();


            //? set relations on others table.
            $table->foreign('inventory_id')->references('id')->on('inventories')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('created_for')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');

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
