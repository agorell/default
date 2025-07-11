<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('housing_units', function (Blueprint $table) {
            $table->id();
            $table->string('unit_number', 50);
            $table->foreignId('housing_type_id')->constrained()->onDelete('restrict');
            $table->integer('bedrooms')->default(0);
            $table->integer('bathrooms')->default(0);
            $table->decimal('square_footage', 8, 2)->nullable();
            $table->integer('parking_spaces')->default(0);
            $table->decimal('rental_rate', 8, 2)->nullable();
            $table->boolean('is_occupied')->default(false);
            $table->enum('condition_grade', ['A', 'B', 'C', 'D', 'F'])->default('B');
            $table->string('property_address', 500);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->unique(['unit_number', 'property_address']);
            $table->index(['housing_type_id', 'is_active']);
            $table->index(['is_occupied', 'is_active']);
            $table->index('condition_grade');
            $table->index('rental_rate');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('housing_units');
    }
};