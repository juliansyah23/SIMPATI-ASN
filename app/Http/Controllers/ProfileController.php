<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        return view('profile.edit', [
            'u'          => auth()->user(),
            'pusatRiset' => config('options.pusat_riset'),
            'posisiList' => config('options.posisi'),
        ]);
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'nip'         => ['nullable', 'digits_between:15,18', Rule::unique('users', 'nip')->ignore($user->id)],
            'name'        => ['required', 'string', 'max:255'],
            'email'       => ['required', 'email', 'ends_with:brin.go.id', Rule::unique('users', 'email')->ignore($user->id)],
            'institusi'   => ['required', 'string', 'max:100'],
            'pusat_riset' => ['required', 'string', 'in:' . implode(',', config('options.pusat_riset'))],
            'posisi'      => ['required', 'string', 'in:' . implode(',', config('options.posisi'))],
            'current_password' => ['required_with:password', 'current_password'],
            'password'    => ['nullable', 'confirmed', Password::min(6)],
        ], [
            'nip.digits_between'        => 'NIP harus terdiri dari 15–18 digit angka.',
            'nip.unique'                => 'NIP ini sudah dipakai user lain.',
            'name.required'             => 'Nama lengkap wajib diisi.',
            'email.required'            => 'Email wajib diisi.',
            'email.email'               => 'Format email tidak valid.',
            'email.ends_with'           => 'Email harus menggunakan domain @brin.go.id.',
            'email.unique'              => 'Email ini sudah dipakai user lain.',
            'institusi.required'        => 'Institusi wajib diisi.',
            'pusat_riset.required'      => 'Pusat Riset wajib dipilih.',
            'pusat_riset.in'            => 'Pusat Riset yang dipilih tidak valid.',
            'posisi.required'           => 'Posisi wajib dipilih.',
            'posisi.in'                 => 'Posisi yang dipilih tidak valid.',
            'current_password.required_with' => 'Masukkan password Anda saat ini untuk mengubah password.',
            'current_password.current_password' => 'Password Anda saat ini salah.',
            'password.confirmed'        => 'Konfirmasi password baru tidak cocok.',
        ]);

        $user->fill([
            'nip'         => $validated['nip'] ?: null,
            'name'        => $validated['name'],
            'email'       => $validated['email'],
            'institusi'   => $validated['institusi'],
            'pusat_riset' => $validated['pusat_riset'],
            'posisi'      => $validated['posisi'],
        ]);

        if (! empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return back()->with('success', 'Profil berhasil diperbarui.');
    }
}
