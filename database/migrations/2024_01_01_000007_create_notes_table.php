<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('restrict');
            $table->foreignId('housing_unit_id')->nullable()->constrained()->onDelete('cascade');
            $table->foreignId('occupier_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('title', 255);
            $table->text('content');
            $table->enum('category', ['general', 'maintenance', 'complaint', 'inspection', 'lease', 'payment', 'communication', 'other'])->default('general');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->boolean('is_private')->default(false);
            $table->json('attachments')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes
            $table->index(['user_id', 'created_at']);
            $table->index(['housing_unit_id', 'created_at']);
            $table->index(['occupier_id', 'created_at']);
            $table->index(['category', 'priority']);
            $table->index(['is_private', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notes');
    }
};