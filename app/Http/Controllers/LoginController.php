<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class LoginController extends Controller
{
    public function view()
    {
        return view('formlogin');
    }

    public function username()
    {
        return view('name');
    }

        public function login(Request $request)
        {
            $request->validate([
                'name' => 'required',
                'password' => 'required',
            ]);

            $user = User::where('name', $request->name)->first();

            if ($user && Hash::check($request->password, $user->password)) {
                Auth::login($user);
                return redirect()->intended('dashboard');
            }

            return back()->withErrors([
                'name' => 'Username atau password salah.',
            ]);
        }

    public function logout(Request $request) {
        // Proses Logout
        Auth::logout();

        // Menghapus session
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // Arahkan ke halaman login
        return redirect('/login')->with('status', 'Kamu telah berhasil logout.');
    }
}
