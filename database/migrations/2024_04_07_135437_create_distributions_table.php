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
            // $table->longText('comment')->nullable();

            $table->integer('order_number')->nullable();
            $table->longText('type_comment')->nullable();

            //? 1--> pendig. 2--> approved. 3 -->canceld. 4 --> collected
            $table->integer('status')->default(1);
            // $table->integer('quantity');
            $table->integer('quantity_per_item'); //  column to store quantity per item
            $table->integer('total_quantity'); //  column to store total quantity per order
            // New JSON column to store the inventory items
            $table->json('inventory_items')->nullable();
            $table->longText('user_comment')->nullable();
            $table->longText('admin_comment')->nullable();
            $table->longText('quartermaster_comment')->nullable();
            $table->integer('year');
            $table->foreignId('type_id');
            $table->foreignId('department_id');
            $table->foreignId('created_by');
            $table->foreignId('quartermaster_id')->nullable();
            $table->foreignId('created_for')->nullable();
            $table->foreignId('inventory_id')->nullable();

            $table->boolean('is_deleted')->default('0');

            $table->timestamps();


            //? set relations on others table.
            $table->foreign('type_id')->references('id')->on('item_types')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('created_for')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('quartermaster_id')->references('id')->on('users')->onUpdate('cascade')->onDelete('cascade');
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