<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenant_verification_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id');
            $table->unsignedBigInteger('uploaded_by')->nullable();
            $table->enum('doc_type', [
                'owner_ktp',
                'owner_selfie_ktp',
                'business_address_proof',
                'bank_account_proof',
            ]);
            $table->string('file_path', 255);
            $table->string('original_name', 255)->nullable();
            $table->string('mime_type', 100)->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->enum('status', ['uploaded', 'approved', 'rejected'])->default('uploaded');
            $table->timestamp('uploaded_at')->nullable();
            $table->timestamps();

            $table->foreign('tenant_id')
                ->references('idTenant')
                ->on('tenants')
                ->cascadeOnDelete();

            $table->foreign('uploaded_by')
                ->references('id')
                ->on('users')
                ->nullOnDelete();

            $table->unique(['tenant_id', 'doc_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tenant_verification_documents');
    }
};

