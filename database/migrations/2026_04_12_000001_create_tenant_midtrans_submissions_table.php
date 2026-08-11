<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_midtrans_submissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->unique();
            $table->enum('status', ['draft', 'submitted', 'revision_needed', 'approved'])->default('draft');
            $table->string('business_entity_type', 30)->nullable();
            $table->string('legal_business_name', 150)->nullable();
            $table->string('brand_name', 150)->nullable();
            $table->string('business_category', 150)->nullable();
            $table->text('business_description_short')->nullable();
            $table->string('business_email', 255)->nullable();
            $table->string('business_phone', 45)->nullable();
            $table->string('public_business_url', 255)->nullable();
            $table->string('pic_name', 100)->nullable();
            $table->string('pic_phone', 45)->nullable();
            $table->string('pic_email', 255)->nullable();
            $table->string('bank_name', 100)->nullable();
            $table->text('bank_account_number')->nullable();
            $table->string('bank_account_holder_name', 150)->nullable();
            $table->boolean('dp_enabled')->default(true);
            $table->unsignedTinyInteger('dp_percent')->default(30);
            $table->boolean('cash_enabled')->default(false);
            $table->text('cash_instruction')->nullable();
            $table->text('submission_notes')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->unsignedBigInteger('reviewed_by')->nullable();
            $table->text('review_notes')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('idTenant')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('reviewed_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_midtrans_submissions');
    }
};
