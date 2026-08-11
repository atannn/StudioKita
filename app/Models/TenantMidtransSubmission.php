<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantMidtransSubmission extends Model
{
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_REVISION_NEEDED = 'revision_needed';
    public const STATUS_APPROVED = 'approved';

    protected $connection = 'mysql';

    protected $fillable = [
        'tenant_id',
        'status',
        'business_entity_type',
        'legal_business_name',
        'brand_name',
        'business_category',
        'business_description_short',
        'business_email',
        'business_phone',
        'public_business_url',
        'pic_name',
        'pic_phone',
        'pic_email',
        'bank_name',
        'bank_account_number',
        'bank_account_holder_name',
        'submission_notes',
        'submitted_at',
        'reviewed_at',
        'reviewed_by',
        'review_notes',
    ];

    protected $casts = [
        'bank_account_number' => 'encrypted',
        'submitted_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class, 'tenant_id', 'idTenant');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by', 'id');
    }

    public static function labels(): array
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Dikirim',
            self::STATUS_REVISION_NEEDED => 'Perlu Revisi',
            self::STATUS_APPROVED => 'Disetujui',
        ];
    }
}
