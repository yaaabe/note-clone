<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;
use App\Models\User;

class ProfileController extends Controller
{
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
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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

    public function updateHeaderImage(Request $request, User $user)
{
    abort_unless(auth()->id() === $user->id, 403);

    $data = $request->validate([
        'header_image' => ['required','image','mimes:jpg,jpeg,png,webp','max:4096'],
    ]);

    // 旧ヘッダー削除
    if ($user->header_image_path && Storage::disk('public')->exists($user->header_image_path)) {
        Storage::disk('public')->delete($user->header_image_path);
    }

    // 新規保存（/storage/app/public/headers）
    $path = $request->file('header_image')->store('headers', 'public');

    $user->header_image_path = $path;
    $user->save();

    return back()->with('status', 'ヘッダー画像を更新しました。');
}

}
