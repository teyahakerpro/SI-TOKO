<?php

namespace App\Http\Controllers;

use App\Models\DataPelanggan;
use App\Models\NotaPenjualan;
use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PelangganController extends Controller
{
    /**
     * Menampilkan daftar pelanggan dan total piutang / kasbon.
     */
    public function index(Request $request): View
    {
        $keyword = trim((string) $request->input('q', ''));

        $query = DataPelanggan::query()->withCount([
            'nota as jumlah_transaksi',
            'nota as nota_belum_lunas_count' => function ($q) {
                $q->where('status_bayar', 'belum_lunas');
            },
        ]);

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('telepon', 'LIKE', "%{$keyword}%");
            });
        }

        $pelanggan = $query->orderByDesc('total_hutang')->paginate(10)->withQueryString();

        // Rekapitulasi Piutang
        $totalSemuaHutang = DataPelanggan::sum('total_hutang');
        $totalPelangganBerhutang = DataPelanggan::where('total_hutang', '>', 0)->count();

        return view('pelanggan.index', compact(
            'pelanggan',
            'keyword',
            'totalSemuaHutang',
            'totalPelangganBerhutang'
        ));
    }

    /**
     * Tambah pelanggan baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama'    => ['required', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:20'],
        ], [
            'nama.required' => 'Nama pelanggan wajib diisi.',
        ]);

        DataPelanggan::create([
            'nama'         => $validated['nama'],
            'telepon'      => $validated['telepon'] ?? null,
            'total_hutang' => 0,
        ]);

        return redirect()->route('pelanggan.index')->with('success', "Pelanggan '{$validated['nama']}' berhasil ditambahkan.");
    }

    /**
     * Ubah data kontak pelanggan.
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $pelanggan = DataPelanggan::findOrFail($id);

        $validated = $request->validate([
            'nama'    => ['required', 'string', 'max:255'],
            'telepon' => ['nullable', 'string', 'max:20'],
        ], [
            'nama.required' => 'Nama pelanggan wajib diisi.',
        ]);

        $pelanggan->update($validated);

        return redirect()->route('pelanggan.index')->with('success', "Data pelanggan '{$pelanggan->nama}' berhasil diperbarui.");
    }

    /**
     * Pelunasan Kasbon (Sebagian atau Lunas Penuh).
     * Sesuai PRD: Mengurangi saldo utang pelanggan & mengubah status nota menjadi 'lunas'.
     */
    public function bayarHutang(Request $request, int $id): RedirectResponse
    {
        $pelanggan = DataPelanggan::findOrFail($id);

        $validated = $request->validate([
            'nominal' => ['required', 'numeric', 'min:1'],
        ], [
            'nominal.required' => 'Masukkan nominal pembayaran kasbon.',
            'nominal.min'      => 'Nominal pembayaran minimal Rp 1.',
        ]);

        $nominalBayar = (float) $validated['nominal'];

        if ($nominalBayar > (float) $pelanggan->total_hutang) {
            return back()->with('error', "Nominal pembayaran (Rp " . number_format($nominalBayar, 0, ',', '.') . ") melebihi total utang pelanggan (Rp " . number_format($pelanggan->total_hutang, 0, ',', '.') . ").");
        }

        try {
            DB::transaction(function () use ($pelanggan, $nominalBayar) {
                // 1. Kurangi total utang pelanggan
                $sisaHutang = max(0, (float) $pelanggan->total_hutang - $nominalBayar);
                $pelanggan->total_hutang = $sisaHutang;
                $pelanggan->save();

                // 2. Tandai nota hutang menjadi lunas mulai dari yang terlama jika saldo mencukupi
                if ($sisaHutang <= 0) {
                    NotaPenjualan::where('id_pelanggan', $pelanggan->id_pelanggan)
                        ->where('metode_bayar', 'hutang')
                        ->where('status_bayar', 'belum_lunas')
                        ->update(['status_bayar' => 'lunas']);
                }
            });

            return redirect()->route('pelanggan.index')->with('success', "Pembayaran kasbon sebesar Rp " . number_format($nominalBayar, 0, ',', '.') . " untuk {$pelanggan->nama} berhasil dicatat.");
        } catch (Exception $e) {
            return back()->with('error', 'Gagal memproses pelunasan: ' . $e->getMessage());
        }
    }

    /**
     * Hapus data pelanggan (hanya jika tidak memiliki sisa utang).
     */
    public function destroy(int $id): RedirectResponse
    {
        $pelanggan = DataPelanggan::findOrFail($id);

        if ($pelanggan->total_hutang > 0) {
            return back()->with('error', "Pelanggan '{$pelanggan->nama}' belum bisa dihapus karena masih memiliki tanggungan kasbon Rp " . number_format($pelanggan->total_hutang, 0, ',', '.'));
        }

        $pelanggan->delete();

        return redirect()->route('pelanggan.index')->with('success', "Pelanggan '{$pelanggan->nama}' berhasil dihapus.");
    }
}

