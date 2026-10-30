<?php

namespace App\Http\Controllers;

use App\Models\AiPrediksiStok;
use App\Models\AiRekomendasiBundling;
use App\Services\AiAnalyticsService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AiController extends Controller
{
    protected AiAnalyticsService $aiService;

    public function __construct(AiAnalyticsService $aiService)
    {
        $this->aiService = $aiService;
    }

    /**
     * Dashboard Modul Kecerdasan Buatan (Khusus Owner).
     * Memuat prediksi restok, rekomendasi bundling, dan antarmuka Smart Assistant.
     */
    public function index(): View
    {
        // Jika tabel prediksi masih kosong, jalankan kalkulasi awal otomatis
        if (AiPrediksiStok::count() === 0) {
            $this->aiService->generateStockPredictions();
        }
        if (AiRekomendasiBundling::count() === 0) {
            $this->aiService->generateBundlingRecommendations();
        }

        $prediksiStok = AiPrediksiStok::with('barang')
            ->orderBy('estimasi_stok_habis', 'asc')
            ->get();

        $rekomendasiBundling = AiRekomendasiBundling::with(['utama', 'pasangan'])
            ->where('status_aktif', true)
            ->orderByDesc('skor_asosiasi')
            ->get();

        return view('ai.index', compact('prediksiStok', 'rekomendasiBundling'));
    }

    /**
     * Refresh data kalkulasi AI dari transaksi terbaru.
     */
    public function refreshInsights(): RedirectResponse
    {
        $this->aiService->generateStockPredictions();
        $this->aiService->generateBundlingRecommendations();

        return redirect()->route('ai.index')->with('success', 'Model analisis AI berhasil diperbarui dengan data penjualan terkini.');
    }

    /**
     * Endpoint API Smart Assistant (On-The-Fly / Real-Time).
     * Menerima prompt user, menganalisis secara instan, dan mengembalikan jawaban tanpa menyimpan chat ke DB.
     */
    public function chat(Request $request): JsonResponse
    {
        $request->validate([
            'prompt' => ['required', 'string', 'max:500'],
        ]);

        $prompt = trim((string) $request->input('prompt'));
        $jawaban = $this->aiService->askAssistant($prompt);

        return response()->json([
            'success'  => true,
            'response' => $jawaban,
        ]);
    }
}

