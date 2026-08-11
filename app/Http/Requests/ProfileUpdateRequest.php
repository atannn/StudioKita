<?php

namespace App\Http\Requests;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProfileUpdateRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $user = $this->user();
        $isOwner = $user?->role === 'owner';
        $emailMax = $isOwner ? 45 : 255;

        $emailRules = [
            'required',
            'string',
            'lowercase',
            'email',
            "max:{$emailMax}",
            Rule::unique(User::class)->ignore($user?->id),
        ];

        if ($isOwner && $user?->tenants_idTenant) {
            $emailRules[] = Rule::unique(Tenant::class, 'email')
                ->ignore($user->tenants_idTenant, 'idTenant');
        }

        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => $emailRules,
            'no_telp' => ['nullable', 'string', 'max:25'],
        ];
    }
}
