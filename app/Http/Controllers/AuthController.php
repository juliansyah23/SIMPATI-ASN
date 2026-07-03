<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class AuthController extends Controller
{
    /**
     * List resmi Pusat Riset & Posisi — sumber tunggalnya ada di config/options.php,
     * supaya selalu sinkron dengan dropdown di register.blade.php dan filter di
     * DataController. JANGAN hardcode ulang di sini, baca via config() saja.
     */

    /**
     * Tampilkan halaman login.
     */
    public function loginForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    /**
     * Proses login menggunakan guard session bawaan Laravel.
     */
    public function loginSubmit(Request $request)
    {
        $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'min:6'],
        ], [
            'email.required'    => 'Email wajib diisi.',
            'email.email'       => 'Format email tidak valid.',
            'password.required' => 'Password wajib diisi.',
            'password.min'      => 'Password minimal 6 karakter.',
        ]);

        $remember = $request->boolean('remember');

        if (Auth::attempt($request->only('email', 'password'), $remember)) {
            $request->session()->regenerate();

            return redirect()->intended(route('dashboard'))
                ->with('success', 'Selamat datang, ' . Auth::user()->name . '!');
        }

        return back()
            ->withInput($request->only('email'))
            ->with('error', 'Email atau password salah. Silakan coba lagi.');
    }

    /**
     * Tampilkan halaman registrasi.
     */
    public function registerForm()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }

        return view('auth.register');
    }

    /**
     * Proses registrasi: simpan user baru dengan password di-hash, lalu auto-login.
     */
    public function registerSubmit(Request $request)
    {
        $validated = $request->validate([
            'nip'         => ['required', 'digits_between:15,18', 'unique:users,nip'],
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'ends_with:brin.go.id', 'unique:users,email'],
            'institusi'   => ['required', 'string', 'max:100'],
            'pusat_riset' => ['required', 'string', 'in:' . implode(',', config('options.pusat_riset'))],
            'posisi'      => ['required', 'string', 'in:' . implode(',', config('options.posisi'))],
            'password'    => ['required', 'confirmed', Password::min(6)],
        ], [
            'nip.required'          => 'NIP wajib diisi.',
            'nip.digits_between'    => 'NIP harus terdiri dari 15–18 digit angka.',
            'nip.unique'            => 'NIP ini sudah terdaftar.',
            'name.required'         => 'Nama lengkap wajib diisi.',
            'email.required'        => 'Email wajib diisi.',
            'email.email'           => 'Format email tidak valid.',
            'email.ends_with'       => 'Email harus menggunakan domain @brin.go.id.',
            'email.unique'          => 'Email ini sudah terdaftar.',
            'institusi.required'    => 'Institusi wajib diisi.',
            'pusat_riset.required'  => 'Pusat Riset wajib dipilih.',
            'pusat_riset.in'        => 'Pusat Riset yang dipilih tidak valid.',
            'posisi.required'       => 'Posisi wajib dipilih.',
            'posisi.in'             => 'Posisi yang dipilih tidak valid.',
            'password.required'     => 'Password wajib diisi.',
            'password.confirmed'    => 'Konfirmasi password tidak cocok.',
        ]);

        $user = User::create([
            'nip'         => $validated['nip'],
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'institusi'   => $validated['institusi'],
            'pusat_riset' => $validated['pusat_riset'],
            'posisi'      => $validated['posisi'],
            'role'        => 'pegawai',
            'password'    => Hash::make($validated['password']),
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')
            ->with('registered', 'Akun berhasil dibuat. Selamat datang, ' . $user->name . '!');
    }

    /**
     * Logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah keluar.');
    }
}
