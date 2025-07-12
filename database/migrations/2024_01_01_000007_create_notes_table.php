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
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->enum('category', ['general', 'maintenance', 'complaint', 'inquiry', 'lease', 'payment', 'inspection', 'other'])->default('general');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('housing_unit_id')->nullable();
            $table->unsignedBigInteger('occupier_id')->nullable();
            $table->boolean('is_private')->default(false);
            $table->string('attachment_path')->nullable();
            $table->string('attachment_name')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('housing_unit_id')->references('id')->on('housing_units')->onDelete('cascade');
            $table->foreign('occupier_id')->references('id')->on('occupiers')->onDelete('cascade');
            
            $table->index('user_id');
            $table->index('housing_unit_id');
            $table->index('occupier_id');
            $table->index('category');
            $table->index('priority');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};