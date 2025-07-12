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
        Schema::create('occupiers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('housing_unit_id');
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_phone')->nullable();
            $table->date('occupancy_start_date');
            $table->date('occupancy_end_date')->nullable();
            $table->text('lease_terms')->nullable();
            $table->boolean('is_current')->default(true);
            $table->decimal('monthly_rent', 8, 2)->default(0);
            $table->decimal('security_deposit', 8, 2)->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('housing_unit_id')->references('id')->on('housing_units')->onDelete('cascade');
            
            $table->index('housing_unit_id');
            $table->index('is_current');
            $table->index('occupancy_start_date');
            $table->index('occupancy_end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('occupiers');
    }
};