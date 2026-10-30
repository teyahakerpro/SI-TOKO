<?php

namespace App\Services;

use App\Models\AiPrediksiStok;
use App\Models\AiRekomendasiBundling;
use App\Models\ItemPenjualan;
use App\Models\KatalogBarang;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AiAnalyticsService
{
    /**
     * Menghitung Prediksi Restok Barang berdasarkan Moving Average penjualan harian.
     * Rumus: rata-rata keluar per hari -> sisa hari hingga stok habis -> estimasi tanggal habis.
     */
    public function generateStockPredictions(): int
    {
        // Bersihkan data prediksi lama agar selalu segar
        AiPrediksiStok::truncate();

        $barangList = KatalogBarang::all();
        $totalDibuat = 0;

        foreach ($barangList as $barang) {
            // Ambil total penjualan 30 hari terakhir
            $penjualan30Hari = ItemPenjualan::where('id_barang', $barang->id_barang)
                ->whereHas('nota', function ($q) {
                    $q->where('tanggal', '>=', now()->subDays(30));
                })
                ->sum('jumlah');

            $stokSekarang = (int) $barang->stok_sekarang;

            // Jika ada penjualan, hitung rata-rata per hari (moving average harian)
            if ($penjualan30Hari > 0) {
                $rataHarian = max(0.1, (float) $penjualan30Hari / 30);
                $sisaHari = (int) floor($stokSekarang / $rataHarian);
                $estimasiHabis = now()->addDays($sisaHari)->toDateString();
                // Rekomendasi restok untuk kebutuhan 14 hari ke depan
                $rekomendasiRestok = max(10, (int) ceil($rataHarian * 14));
                $confidence = min(0.95, round(0.5 + ($penjualan30Hari / 50), 2));
            } else {
                // Jika belum ada penjualan 30 hari ini
                $estimasiHabis = now()->addDays(60)->toDateString();
                $rekomendasiRestok = 10;
                $confidence = 0.50;
            }

            AiPrediksiStok::create([
                'id_barang'                 => $barang->id_barang,
                'tanggal_prediksi'          => now()->toDateString(),
                'estimasi_stok_habis'       => $estimasiHabis,
                'rekomendasi_jumlah_restok' => $rekomendasiRestok,
                'confidence_score'          => $confidence,
            ]);

            $totalDibuat++;
        }

        return $totalDibuat;
    }

    /**
     * Menghitung Rekomendasi Bundling / Market Basket Analysis (Item Pair yang sering dibeli bersama).
     */
    public function generateBundlingRecommendations(): int
    {
        AiRekomendasiBundling::truncate();

        // Cari pasangan barang yang berada di dalam nomor nota (id_nota) yang sama
        $pasangan = DB::table('item_penjualan as i1')
            ->join('item_penjualan as i2', function ($join) {
                $join->on('i1.id_nota', '=', 'i2.id_nota')
                     ->whereColumn('i1.id_barang', '<', 'i2.id_barang');
            })
            ->select('i1.id_barang as utama', 'i2.id_barang as pasangan', DB::raw('COUNT(*) as frekuensi_bersama'))
            ->groupBy('i1.id_barang', 'i2.id_barang')
            ->orderByDesc('frekuensi_bersama')
            ->limit(5)
            ->get();

        $totalDibuat = 0;

        foreach ($pasangan as $p) {
            $skor = min(0.99, round(0.60 + ($p->frekuensi_bersama * 0.1), 2));

            AiRekomendasiBundling::create([
                'id_barang_utama'    => $p->utama,
                'id_barang_pasangan' => $p->pasangan,
                'skor_asosiasi'      => $skor,
                'status_aktif'       => 1,
            ]);

            $totalDibuat++;
        }

        // Jika data transaksi bersama masih sedikit, buatkan asosiasi rekomendasi logis default
        if ($totalDibuat === 0) {
            $barangList = KatalogBarang::take(4)->get();
            if ($barangList->count() >= 2) {
                AiRekomendasiBundling::create([
                    'id_barang_utama'    => $barangList[0]->id_barang,
                    'id_barang_pasangan' => $barangList[1]->id_barang,
                    'skor_asosiasi'      => 0.85,
                    'status_aktif'       => 1,
                ]);
                $totalDibuat++;
            }
        }

        return $totalDibuat;
    }

    /**
     * AI Smart Assistant: Menjawab prompt Owner secara On-The-Fly.
     * Menganalisis kondisi penjualan real-time di server tanpa membebani database dengan riwayat permanen.
     */
    public function askAssistant(string $prompt): string
    {
        $promptLower = strtolower($prompt);

        // Ambil data ringkasan real-time untuk konteks AI
        $totalOmzetHariIni = DB::table('nota_penjualan')
            ->whereDate('tanggal', now()->toDateString())
            ->sum('total_akhir');

        $totalKasbon = DB::table('data_pelanggan')->sum('total_hutang');
        $stokMenipis = KatalogBarang::where('stok_sekarang', '<=', 5)->pluck('nama')->toArray();
        $totalBarang = KatalogBarang::count();

        // Logika Respons Cerdas Terkontekstual (Context-Aware Natural Language Response)
        if (str_contains($promptLower, 'omzet') || str_contains($promptLower, 'pendapatan') || str_contains($promptLower, 'laba') || str_contains($promptLower, 'penjualan')) {
            $formatOmzet = 'Rp ' . number_format($totalOmzetHariIni, 0, ',', '.');
            return "Berdasarkan rekaman transaksi sistem hari ini, total omzet yang berhasil dikumpulkan adalah **{$formatOmzet}**.\n\nTips Manajemen: Pastikan seluruh transaksi kasir tunai telah disetorkan saat tutup kasir sore/malam hari.";
        }

        if (str_contains($promptLower, 'stok') || str_contains($promptLower, 'restok') || str_contains($promptLower, 'habis') || str_contains($promptLower, 'kurang')) {
            if (count($stokMenipis) > 0) {
                $daftar = implode(', ', $stokMenipis);
                return "Perhatian Owner! Saat ini terdapat **" . count($stokMenipis) . " barang** dengan stok kritis (&le; 5 unit), yaitu: **{$daftar}**.\n\nRekomendasi Tindakan: Segera hubungi supplier distributor untuk melakukan pemesanan ulang sebelum stok menjadi 0 di kasir.";
            }
            return "Kabar baik! Seluruh katalog inventaris toko ({$totalBarang} barang) saat ini memiliki tingkat stok yang aman dan siap melayani penjualan.";
        }

        if (str_contains($promptLower, 'bundling') || str_contains($promptLower, 'paket') || str_contains($promptLower, 'promo') || str_contains($promptLower, 'cuci gudang')) {
            $bundling = AiRekomendasiBundling::with(['utama', 'pasangan'])->first();
            if ($bundling && $bundling->utama && $bundling->pasangan) {
                return "Rekomendasi Paket Bundling Minggu Ini:\n" .
                       "- **Paket Hemat Kebutuhan Dapur**: Satukan **{$bundling->utama->nama}** dengan **{$bundling->pasangan->nama}**.\n" .
                       "- Skor Asosiasi: " . ($bundling->skor_asosiasi * 100) . "%\n\nStrategi: Berikan potongan harga Rp 1.000 - Rp 2.000 jika pembeli mengambil kedua item ini secara bersamaan untuk mempercepat perputaran inventaris.";
            }
            return "Saran Bundling Cepat: Pasangkan komoditas pokok (seperti Beras atau Minyak Goreng) dengan bumbu instan/tepung serbaguna untuk menarik belanja harian ibu rumah tangga.";
        }

        if (str_contains($promptLower, 'kasbon') || str_contains($promptLower, 'utang') || str_contains($promptLower, 'piutang')) {
            $formatHutang = 'Rp ' . number_format($totalKasbon, 0, ',', '.');
            return "Total piutang kasbon pelanggan yang saat ini belum tertagih mencapai **{$formatHutang}**.\n\nSaran Keuangan: Anda dapat memeriksa daftar kontak pelanggan di menu *Pelanggan & Kasbon* untuk mengirimkan pengingat ramah via WhatsApp.";
        }

        // Jawaban Umum Konsultasi Bisnis Retail Tokoku
        return "Halo Pemilik Toko! Sistem SITOKO AI menganalisis toko Anda berjalan aktif. Anda memiliki {$totalBarang} produk terdaftar dan total kasbon tertahan sebesar Rp " . number_format($totalKasbon, 0, ',', '.') . ".\n\nAda yang ingin Anda konsultasikan lebih lanjut? Anda bisa bertanya tentang *prediksi restok*, *ide bundling diskon*, atau *rekapitulasi kasbon pelanggan*.";
    }
}
