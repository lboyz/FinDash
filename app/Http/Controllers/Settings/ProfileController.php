<?php

namespace App\Http\Controllers\Settings;

use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\ProfileUpdateRequest;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Show the user's profile settings page.
     */
    public function edit(Request $request): Response
    {
        return Inertia::render('settings/Profile', [
            'mustVerifyEmail' => $request->user() instanceof MustVerifyEmail,
            'status' => $request->session()->get('status'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        // Handle profile image removal
        if ($request->boolean('remove_profile_image')) {
            if ($request->user()->profile_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($request->user()->profile_image);
                $request->user()->profile_image = null;
            }
        }

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            if ($request->user()->profile_image) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($request->user()->profile_image);
            }
            $path = $request->file('profile_image')->store('profile-images', 'public');
            $request->user()->profile_image = $path;
        }

        $request->user()->save();

        return to_route('settings.profile.edit');
    }

    /**
     * Delete the user's profile.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
