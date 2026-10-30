<?php

namespace App\Http\Controllers;

use App\Models\DataPelanggan;
use App\Models\ItemPenjualan;
use App\Models\KatalogBarang;
use App\Models\NotaPenjualan;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PosController extends Controller
{
    /**
     * Tampilan Antarmuka Utama Kasir (POS).
     */
    public function index(): View
    {
        // Pelanggan untuk modal kasbon/hutang
        $daftarPelanggan = DataPelanggan::orderBy('nama', 'asc')->get();

        return view('pos.index', compact('daftarPelanggan'));
    }

    /**
     * Endpoint API pencarian barang untuk autocomplete/barcode scanner.
     * Mengamankan HPP (harga_beli): tidak pernah dikirim ke respon kasir.
     */
    public function cariBarang(Request $request): JsonResponse
    {
        $keyword = trim((string) $request->input('q', ''));

        $query = KatalogBarang::query()->select([
            'id_barang',
            'id_kategori',
            'nama',
            'barcode',
            'satuan',
            'harga_jual',
            'stok_sekarang',
        ]);

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('barcode', '=', $keyword);
            });
        }

        $barang = $query->limit(20)->get();

        return response()->json([
            'success' => true,
            'data'    => $barang,
        ]);
    }

    /**
     * Proses Checkout / Transaksi POS.
     * Mendukung pembayaran: Tunai, Transfer, dan Hutang (Kasbon).
     */
    public function checkout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'metode_bayar' => ['required', 'in:tunai,transfer,hutang'],
            'id_pelanggan' => ['nullable', 'required_if:metode_bayar,hutang', 'exists:data_pelanggan,id_pelanggan'],
            'diskon'       => ['nullable', 'numeric', 'min:0'],
            'bayar'        => ['nullable', 'numeric', 'min:0'],
            'items'        => ['required', 'array', 'min:1'],
            'items.*.id_barang' => ['required', 'exists:katalog_barang,id_barang'],
            'items.*.jumlah'    => ['required', 'numeric', 'min:1'],
        ], [
            'metode_bayar.required' => 'Pilih metode pembayaran.',
            'id_pelanggan.required_if' => 'Untuk metode Kasbon/Hutang, nama pelanggan wajib dipilih.',
            'items.required' => 'Keranjang belanja masih kosong.',
        ]);

        try {
            $nota = DB::transaction(function () use ($validated, $request) {
                $diskon = (float) ($validated['diskon'] ?? 0);
                $totalBelanja = 0;
                $itemsToInsert = [];

                // Validasi dan kunci baris barang (row-level locking FOR UPDATE) untuk mencegah race condition
                foreach ($validated['items'] as $item) {
                    $barang = KatalogBarang::where('id_barang', $item['id_barang'])
                        ->lockForUpdate()
                        ->first();

                    if (! $barang) {
                        throw new Exception("Barang ID {$item['id_barang']} tidak ditemukan.");
                    }

                    if ($barang->stok_sekarang < $item['jumlah']) {
                        throw new Exception("Stok untuk '{$barang->nama}' tidak mencukupi (Tersisa: {$barang->stok_sekarang}).");
                    }

                    $subtotal = (float) $barang->harga_jual * (float) $item['jumlah'];
                    $totalBelanja += $subtotal;

                    $itemsToInsert[] = [
                        'id_barang'           => $barang->id_barang,
                        'jumlah'              => $item['jumlah'],
                        'harga_jual_saat_itu' => $barang->harga_jual,
                        'hpp_saat_itu'        => $barang->harga_beli,
                        'subtotal'            => $subtotal,
                    ];
                }

                $totalAkhir = max(0, $totalBelanja - $diskon);

                // Buat nomor nota unik (misal: NOTA-20261030-001)
                $tanggalHariIni = now()->format('Ymd');
                $countNotaHariIni = NotaPenjualan::whereDate('tanggal', now()->toDateString())->count() + 1;
                $nomorNota = 'NOTA-' . $tanggalHariIni . '-' . str_pad((string) $countNotaHariIni, 4, '0', STR_PAD_LEFT);

                // Status bayar
                $statusBayar = ($validated['metode_bayar'] === 'hutang') ? 'belum_lunas' : 'lunas';

                // Insert ke nota_penjualan
                $notaBaru = NotaPenjualan::create([
                    'nomor_nota'    => $nomorNota,
                    'id_karyawan'   => Auth::id() ?? 1,
                    'id_pelanggan'  => $validated['id_pelanggan'] ?? null,
                    'tanggal'       => now(),
                    'total_belanja' => $totalBelanja,
                    'diskon'        => $diskon,
                    'total_akhir'   => $totalAkhir,
                    'metode_bayar'  => $validated['metode_bayar'],
                    'status_bayar'  => $statusBayar,
                ]);

                // Insert item_penjualan (Trigger MySQL otomatis memotong stok & mencatat hutang)
                foreach ($itemsToInsert as $detail) {
                    $detail['id_nota'] = $notaBaru->id_nota;
                    ItemPenjualan::create($detail);
                }

                return $notaBaru;
            });

            return response()->json([
                'success' => true,
                'message' => 'Transaksi berhasil diproses.',
                'data'    => [
                    'id_nota'     => $nota->id_nota,
                    'nomor_nota'  => $nota->nomor_nota,
                    'total_akhir' => $nota->total_akhir,
                    'kembalian'   => max(0, ((float) ($validated['bayar'] ?? 0)) - (float) $nota->total_akhir),
                ],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * API untuk sinkronisasi transaksi offline massal dari LocalStorage (OFF-01).
     */
    public function syncOffline(Request $request): JsonResponse
    {
        $transactions = $request->input('transactions', []);

        if (empty($transactions)) {
            return response()->json([
                'success' => true,
                'synced_count' => 0,
                'message' => 'Tidak ada transaksi antrean offline.',
            ]);
        }

        $berhasil = 0;
        $gagal = [];

        foreach ($transactions as $index => $trxData) {
            try {
                DB::transaction(function () use ($trxData) {
                    $diskon = (float) ($trxData['diskon'] ?? 0);
                    $totalBelanja = 0;
                    $itemsToInsert = [];

                    foreach ($trxData['items'] as $item) {
                        $barang = KatalogBarang::where('id_barang', $item['id_barang'])->lockForUpdate()->first();
                        if (! $barang) {
                            throw new Exception("Barang ID {$item['id_barang']} tidak valid.");
                        }

                        $subtotal = (float) $barang->harga_jual * (float) $item['jumlah'];
                        $totalBelanja += $subtotal;

                        $itemsToInsert[] = [
                            'id_barang'           => $barang->id_barang,
                            'jumlah'              => $item['jumlah'],
                            'harga_jual_saat_itu' => $barang->harga_jual,
                            'hpp_saat_itu'        => $barang->harga_beli,
                            'subtotal'            => $subtotal,
                        ];
                    }

                    $totalAkhir = max(0, $totalBelanja - $diskon);
                    $nomorNota = 'OFF-' . now()->format('YmdHis') . '-' . rand(100, 999);
                    $statusBayar = ($trxData['metode_bayar'] === 'hutang') ? 'belum_lunas' : 'lunas';

                    $notaBaru = NotaPenjualan::create([
                        'nomor_nota'    => $nomorNota,
                        'id_karyawan'   => Auth::id() ?? 1,
                        'id_pelanggan'  => $trxData['id_pelanggan'] ?? null,
                        'tanggal'       => $trxData['tanggal'] ?? now(),
                        'total_belanja' => $totalBelanja,
                        'diskon'        => $diskon,
                        'total_akhir'   => $totalAkhir,
                        'metode_bayar'  => $trxData['metode_bayar'],
                        'status_bayar'  => $statusBayar,
                    ]);

                    foreach ($itemsToInsert as $detail) {
                        $detail['id_nota'] = $notaBaru->id_nota;
                        ItemPenjualan::create($detail);
                    }
                });

                $berhasil++;
            } catch (Exception $e) {
                $gagal[] = [
                    'index'   => $index,
                    'message' => $e->getMessage(),
                ];
            }
        }

        return response()->json([
            'success'      => true,
            'synced_count' => $berhasil,
            'failed_count' => count($gagal),
            'errors'       => $gagal,
            'message'      => "{$berhasil} transaksi offline berhasil disinkronkan ke database.",
        ]);
    }
}

