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
        Schema::create('clients', function (Blueprint $table) {

            $table->id();
            $table->string('name');
            $table->string('personal_number')->unique();
            $table->string('email')->unique();
            $table->string('phone');
            
            $table->foreignId('department_id');
            $table->foreignId('emp_type_id');
            $table->boolean('is_deleted')->default(false);
            $table->timestamps();


            //? set relations on other table
            $table->foreign('emp_type_id')->references('id')->on('employee_types')->onUpdate('cascade')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments')->onUpdate('cascade')->onDelete('cascade');

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
