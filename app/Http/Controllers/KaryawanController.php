<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class KaryawanController extends Controller
{
    /**
     * Daftar akun staf dan hak akses (Khusus Owner).
     */
    public function index(): View
    {
        $karyawan = Karyawan::withCount('nota')->orderBy('nama')->get();

        return view('karyawan.index', compact('karyawan'));
    }

    /**
     * Tambah akun baru (Owner atau Kasir).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', 'unique:karyawan,username'],
            'password' => ['required', 'string', 'min:6'],
            'peran'    => ['required', 'in:admin,kasir'],
        ], [
            'username.unique' => 'Nama pengguna ini sudah digunakan.',
            'password.min'    => 'Kata sandi minimal 6 karakter.',
        ]);

        Karyawan::create([
            'nama'          => $validated['nama'],
            'username'      => $validated['username'],
            'password_hash' => Hash::make($validated['password']),
            'peran'         => $validated['peran'],
        ]);

        return redirect()->route('karyawan.index')->with('success', "Akun untuk '{$validated['nama']}' berhasil dibuat.");
    }

    /**
     * Ubah profil atau reset password akun.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $karyawan = Karyawan::findOrFail($id);

        $validated = $request->validate([
            'nama'     => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:255', Rule::unique('karyawan', 'username')->ignore($karyawan->id_karyawan, 'id_karyawan')],
            'password' => ['nullable', 'string', 'min:6'],
            'peran'    => ['required', 'in:admin,kasir'],
        ]);

        $data = [
            'nama'     => $validated['nama'],
            'username' => $validated['username'],
            'peran'    => $validated['peran'],
        ];

        if (! empty($validated['password'])) {
            $data['password_hash'] = Hash::make($validated['password']);
        }

        $karyawan->update($data);

        return redirect()->route('karyawan.index')->with('success', "Akun '{$karyawan->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus akun staf.
     */
    public function destroy(int $id): RedirectResponse
    {
        if (Auth::id() === $id) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri yang sedang aktif digunakan.');
        }

        $karyawan = Karyawan::findOrFail($id);
        $nama = $karyawan->nama;
        $karyawan->delete();

        return redirect()->route('karyawan.index')->with('success', "Akun '{$nama}' berhasil dihapus.");
    }
}

