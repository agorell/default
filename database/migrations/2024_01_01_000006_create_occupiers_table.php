<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('occupiers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('housing_unit_id')->constrained()->onDelete('restrict');
            $table->string('name', 255);
            $table->string('email', 255)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('emergency_contact_name', 255)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->date('move_in_date')->nullable();
            $table->date('move_out_date')->nullable();
            $table->date('lease_start_date')->nullable();
            $table->date('lease_end_date')->nullable();
            $table->decimal('rental_amount', 8, 2)->nullable();
            $table->decimal('deposit_amount', 8, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['housing_unit_id', 'is_active']);
            $table->index(['move_in_date', 'move_out_date']);
            $table->index(['lease_start_date', 'lease_end_date']);
            $table->index(['email', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('occupiers');
    }
};