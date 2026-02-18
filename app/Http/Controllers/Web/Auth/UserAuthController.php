<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\Admin;
use App\Models\User;
use App\Models\PostReaction;
use App\Support\ClientContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserAuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register', [ 'title' => 'Register' ]);
    }

    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:6', 'max:255', 'confirmed'],
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);

        // Link existing fingerprint-based reactions to the new user
        $this->linkFingerprintReactionsToUser($request, $user);

        Auth::login($user, true); // remember = true
        $request->session()->regenerate();

        return redirect()->route('user.profile');
    }

    public function showLogin()
    {
        return view('auth.login', [ 'title' => 'Login' ]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 1) Пытаемся авторизовать как админа
        $admin = Admin::where('email', $credentials['email'])->first();
        if ($admin && Hash::check($credentials['password'], $admin->password)) {
            // Выходим из user-аккаунта, если был
            if (Auth::check()) {
                Auth::logout();
            }
            // Фиксируем сессию админа
            $request->session()->put('admin_id', $admin->id);
            return redirect()->route('admin.dashboard');
        }

        // 2) Иначе обычный пользователь
        if (Auth::attempt($credentials, true)) {
            $request->session()->regenerate();
            // На всякий случай очищаем admin-флаг
            $request->session()->forget('admin_id');
            
            // Link existing fingerprint-based reactions to the logged in user
            $this->linkFingerprintReactionsToUser($request, Auth::user());
            
            return redirect()->route('feed');
        }

        return back()->withErrors(['email' => __('auth.failed')])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        // Выходим из user guard
        if (Auth::check()) {
            Auth::logout();
        }
        // Сбрасываем admin-флаг
        $request->session()->forget('admin_id');
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('feed');
    }

    /**
     * Link existing fingerprint-based reactions to a user account
     */
    private function linkFingerprintReactionsToUser(Request $request, User $user): void
    {
        $deviceFingerprint = ClientContext::fingerprint($request);
        
        if (!$deviceFingerprint) {
            return;
        }

        // Find all reactions made with this device fingerprint that don't have a user_id
        $fingerprintReactions = PostReaction::where('device_fingerprint', $deviceFingerprint)
            ->whereNull('user_id')
            ->get();

        // Link them to the user
        foreach ($fingerprintReactions as $reaction) {
            // Check if user already has a reaction for this post
            $existingUserReaction = PostReaction::where('post_id', $reaction->post_id)
                ->where('user_id', $user->id)
                ->first();

            if ($existingUserReaction) {
                // User already has a reaction for this post, delete the fingerprint one
                $reaction->delete();
            } else {
                // Link the fingerprint reaction to the user
                $reaction->update([
                    'user_id' => $user->id,
                    'device_fingerprint' => null, // Clear fingerprint since it's now linked to user
                ]);
            }
        }
    }
} 