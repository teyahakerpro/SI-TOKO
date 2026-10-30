<?php

namespace App\Http\Controllers;

use App\Models\DataPelanggan;
use App\Models\ItemPenjualan;
use App\Models\KatalogBarang;
use App\Models\NotaPenjualan;
use App\Models\PengeluaranToko;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LaporanController extends Controller
{
    /**
     * Dashboard Keuangan & Laporan Performa Toko (Khusus Owner).
     */
    public function index(Request $request): View
    {
        $rentang = $request->input('rentang', 'hari_ini');
        $now = Carbon::now();

        switch ($rentang) {
            case 'bulan_ini':
                $startDate = $now->copy()->startOfMonth()->toDateString();
                $endDate = $now->copy()->endOfMonth()->toDateString();
                $labelRentang = 'Bulan Ini (' . $now->isoFormat('MMMM Y') . ')';
                break;
            case '7_hari':
                $startDate = $now->copy()->subDays(6)->toDateString();
                $endDate = $now->toDateString();
                $labelRentang = '7 Hari Terakhir';
                break;
            case 'semua':
                $startDate = '2000-01-01';
                $endDate = $now->toDateString();
                $labelRentang = 'Semua Waktu';
                break;
            case 'hari_ini':
            default:
                $startDate = $now->toDateString();
                $endDate = $now->toDateString();
                $labelRentang = 'Hari Ini (' . $now->isoFormat('D MMMM Y') . ')';
                break;
        }

        // 1. Total Penjualan (Omzet) & Status Bayar
        $ringkasanNota = NotaPenjualan::whereBetween(DB::raw('DATE(tanggal)'), [$startDate, $endDate])
            ->selectRaw("
                COUNT(*) as total_transaksi,
                COALESCE(SUM(total_akhir), 0) as total_omzet,
                COALESCE(SUM(CASE WHEN metode_bayar = 'hutang' THEN total_akhir ELSE 0 END), 0) as kasbon_tertahan,
                COALESCE(SUM(CASE WHEN status_bayar = 'lunas' THEN total_akhir ELSE 0 END), 0) as uang_tunai_masuk
            ")
            ->first();

        // 2. Kalkulasi HPP dan Laba Kotor
        $ringkasanItem = ItemPenjualan::whereHas('nota', function ($q) use ($startDate, $endDate) {
            $q->whereBetween(DB::raw('DATE(tanggal)'), [$startDate, $endDate]);
        })
        ->selectRaw("
            COALESCE(SUM(subtotal), 0) as total_penjualan_item,
            COALESCE(SUM(hpp_saat_itu * jumlah), 0) as total_hpp
        ")
        ->first();

        $totalOmzet = (float) $ringkasanNota->total_omzet;
        $totalHpp = (float) $ringkasanItem->total_hpp;
        $labaKotor = max(0, $totalOmzet - $totalHpp);

        // 3. Biaya Operasional / Pengeluaran Toko
        $totalPengeluaran = (float) PengeluaranToko::whereBetween(DB::raw('DATE(tanggal)'), [$startDate, $endDate])
            ->sum('jumlah');

        // Laba Bersih = Laba Kotor - Pengeluaran Toko
        $labaBersih = $labaKotor - $totalPengeluaran;

        // 4. Total Piutang Kasbon Akumulasi Seluruh Toko
        $totalPiutangSeluruhToko = (float) DataPelanggan::sum('total_hutang');

        // 5. Barang Terlaris (Top 5 Best Seller)
        $topBarang = ItemPenjualan::whereHas('nota', function ($q) use ($startDate, $endDate) {
            $q->whereBetween(DB::raw('DATE(tanggal)'), [$startDate, $endDate]);
        })
        ->join('katalog_barang', 'item_penjualan.id_barang', '=', 'katalog_barang.id_barang')
        ->select('katalog_barang.nama', DB::raw('SUM(item_penjualan.jumlah) as total_terjual'), DB::raw('SUM(item_penjualan.subtotal) as total_omzet_barang'))
        ->groupBy('katalog_barang.id_barang', 'katalog_barang.nama')
        ->orderByDesc('total_terjual')
        ->limit(5)
        ->get();

        // 6. Riwayat Transaksi Terakhir (Menggunakan View MySQL jika tersedia atau Eloquent)
        $riwayatTransaksi = DB::table('v_detail_transaksi_lengkap')
            ->orderByDesc('tanggal')
            ->limit(10)
            ->get();

        // 7. Barang Stok Kritis (<= 5)
        $stokMenipis = KatalogBarang::where('stok_sekarang', '<=', 5)
            ->orderBy('stok_sekarang', 'asc')
            ->limit(6)
            ->get();

        return view('laporan.index', compact(
            'rentang',
            'labelRentang',
            'ringkasanNota',
            'totalOmzet',
            'totalHpp',
            'labaKotor',
            'totalPengeluaran',
            'labaBersih',
            'totalPiutangSeluruhToko',
            'topBarang',
            'riwayatTransaksi',
            'stokMenipis'
        ));
    }
}

