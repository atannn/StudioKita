<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->enum('verification_level', ['none', 'basic_verified', 'verified'])
                ->default('none')
                ->after('status');
            $table->enum('verification_status', ['draft', 'pending', 'approved', 'rejected'])
                ->default('draft')
                ->after('verification_level');
            $table->timestamp('email_otp_verified_at')->nullable()->after('verification_status');
            $table->timestamp('basic_verified_at')->nullable()->after('email_otp_verified_at');
            $table->timestamp('manual_verified_at')->nullable()->after('basic_verified_at');
            $table->timestamp('verification_submitted_at')->nullable()->after('manual_verified_at');
            $table->timestamp('verification_reviewed_at')->nullable()->after('verification_submitted_at');
            $table->unsignedBigInteger('verification_reviewer_id')->nullable()->after('verification_reviewed_at');
            $table->text('verification_notes')->nullable()->after('verification_reviewer_id');

            $table->foreign('verification_reviewer_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('tenants', function (Blueprint $table) {
            $table->dropForeign(['verification_reviewer_id']);
            $table->dropColumn([
                'verification_level',
                'verification_status',
                'email_otp_verified_at',
                'basic_verified_at',
                'manual_verified_at',
                'verification_submitted_at',
                'verification_reviewed_at',
                'verification_reviewer_id',
                'verification_notes',
            ]);
        });
    }
};

