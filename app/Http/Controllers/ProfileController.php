<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Support\TenantProfileSynchronizer;
use App\Support\TenantVerificationService;
use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function __construct(
        private readonly TenantProfileSynchronizer $tenantProfileSynchronizer,
        private readonly TenantVerificationService $tenantVerificationService
    ) {
    }

    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $user = $request->user();
        $previousEmail = (string) $user->email;

        $user->fill($request->validated());

        if ($user->isDirty('email') && \Illuminate\Support\Facades\Schema::hasColumn('users', 'email_verified_at')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if (
            $user->role === 'owner'
            && $user->tenants_idTenant
            && strcasecmp($previousEmail, (string) $user->email) !== 0
        ) {
            $tenant = Tenant::query()->find($user->tenants_idTenant);

            if ($tenant && strcasecmp((string) $tenant->email, (string) $user->email) !== 0) {
                $tenant->email = $user->email;
                $tenant->save();

                $tenant = $this->tenantVerificationService->resetForEmailChange($tenant);
                $tenant = $this->tenantVerificationService->refreshBasicVerification($tenant);
                $this->tenantProfileSynchronizer->sync($tenant);
            }
        }

        $redirect = match ($user->role) {
            'customer' => 'customer.profile',
            'owner' => 'owner.dashboard',
            'developer' => 'developer.dashboard',
            default => 'profile.edit',
        };

        return Redirect::route($redirect)->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
