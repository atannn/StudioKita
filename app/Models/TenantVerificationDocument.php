<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantVerificationDocument extends Model
{
    public const DOC_OWNER_KTP = 'owner_ktp';
    public const DOC_OWNER_SELFIE_KTP = 'owner_selfie_ktp';
    public const DOC_BUSINESS_ADDRESS_PROOF = 'business_address_proof';
    public const DOC_BANK_ACCOUNT_PROOF = 'bank_account_proof';

    public const REQUIRED_DOC_TYPES = [
        self::DOC_OWNER_KTP,
        self::DOC_OWNER_SELFIE_KTP,
        self::DOC_BUSINESS_ADDRESS_PROOF,
        self::DOC_BANK_ACCOUNT_PROOF,
    ];

    protected $connection = 'mysql';

    protected $fillable = [
        'tenant_id',
        'uploaded_by',
        'doc_type',
        'file_path',
        'original_name',
        'mime_type',
        'file_size',
        'status',
        'uploaded_at',
    ];

    protected $casts = [
        'uploaded_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'idTenant');
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by', 'id');
    }

    public static function labelForType(string $docType): string
    {
        return match ($docType) {
            self::DOC_OWNER_KTP => 'KTP Pemilik',
            self::DOC_OWNER_SELFIE_KTP => 'Selfie dengan KTP',
            self::DOC_BUSINESS_ADDRESS_PROOF => 'Bukti Alamat Usaha',
            self::DOC_BANK_ACCOUNT_PROOF => 'Rekening Bank Penerima',
            default => $docType,
        };
    }
}

