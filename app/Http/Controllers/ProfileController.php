<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    /**
     * Show profile page
     */
    public function index()
    {
        $user = auth()->user();
        return view('profile.index', compact('user'));
    }

    /**
     * Update profile (name, phone)
     */
    public function update(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|min:3|max:100',
            'phone' => 'required|string|min:10|max:15|unique:users,phone,' . $user->id,
        ], [
            'name.required' => 'Tafadhali weka jina lako.',
            'name.min' => 'Jina liwe na angalau herufi 3.',
            'phone.required' => 'Tafadhali weka namba ya simu.',
            'phone.unique' => 'Namba hii ya simu tayari inatumika.',
        ]);

        $user->update([
            'name' => $request->name,
            'phone' => $request->phone,
        ]);

        return back()->with('success', 'Maelezo yako yamebadilishwa!');
    }

    /**
     * Update avatar
     */
    public function updateAvatar(Request $request)
    {
        $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ], [
            'avatar.required' => 'Tafadhali chagua picha.',
            'avatar.image' => 'Faili lazima iwe picha.',
            'avatar.mimes' => 'Picha iwe ya aina: jpeg, png, jpg, gif, webp.',
            'avatar.max' => 'Picha isizidi 2MB.',
        ]);

        $user = auth()->user();

        // Delete old avatar if exists
        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        // Create avatars directory if not exists
        if (!Storage::disk('public')->exists('avatars')) {
            Storage::disk('public')->makeDirectory('avatars');
        }

        // Store new avatar with simple path: avatars/user_id_timestamp.ext
        $extension = $request->file('avatar')->getClientOriginalExtension();
        $filename = $user->id . '_' . time() . '.' . $extension;
        $path = $request->file('avatar')->storeAs('avatars', $filename, 'public');

        $user->update(['avatar' => $path]);

        return back()->with('success', 'Picha yako imebadilishwa!');
    }

    /**
     * Remove avatar
     */
    public function removeAvatar()
    {
        $user = auth()->user();

        if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
            Storage::disk('public')->delete($user->avatar);
        }

        $user->update(['avatar' => null]);

        return back()->with('success', 'Picha imeondolewa!');
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required|string',
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Tafadhali weka password ya sasa.',
            'password.required' => 'Tafadhali weka password mpya.',
            'password.min' => 'Password mpya iwe na angalau herufi 6.',
            'password.confirmed' => 'Password mpya hazilingani.',
        ]);

        $user = auth()->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->withErrors(['current_password' => 'Password ya sasa si sahihi.']);
        }

        $user->update(['password' => Hash::make($request->password)]);

        return back()->with('success', 'Password imebadilishwa!');
    }
}
