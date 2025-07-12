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
        Schema::create('housing_units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_number')->unique();
            $table->unsignedBigInteger('housing_type_id');
            $table->integer('bedrooms')->default(0);
            $table->integer('bathrooms')->default(0);
            $table->decimal('square_footage', 8, 2)->nullable();
            $table->integer('parking_spaces')->default(0);
            $table->decimal('rental_rate', 8, 2)->default(0);
            $table->boolean('is_occupied')->default(false);
            $table->enum('condition_grade', ['A', 'B', 'C', 'D', 'F'])->default('B');
            $table->text('property_address');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('housing_type_id')->references('id')->on('housing_types')->onDelete('cascade');
            
            $table->index('unit_number');
            $table->index('housing_type_id');
            $table->index('is_occupied');
            $table->index('is_active');
            $table->index('condition_grade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('housing_units');
    }
};