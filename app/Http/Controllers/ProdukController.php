<?php

namespace App\Http\Controllers;

use App\Models\KatalogBarang;
use App\Models\KategoriBarang;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProdukController extends Controller
{
    /**
     * Tampilan Katalog Barang (Dapat diakses Kasir & Owner).
     */
    public function index(Request $request): View
    {
        $idKategori = $request->integer('kategori') ?: null;
        $keyword = trim((string) $request->input('q', ''));

        $query = KatalogBarang::query()->with('kategori');

        if ($idKategori) {
            $query->where('id_kategori', $idKategori);
        }

        if ($keyword !== '') {
            $query->where(function ($q) use ($keyword) {
                $q->where('nama', 'LIKE', "%{$keyword}%")
                  ->orWhere('barcode', 'LIKE', "%{$keyword}%");
            });
        }

        $produk = $query->orderBy('nama')
            ->paginate(12)
            ->withQueryString();

        $daftarKategori = KategoriBarang::query()->orderBy('nama')->get();

        return view('produk.index', [
            'produk'         => $produk,
            'daftarKategori' => $daftarKategori,
            'kategoriAktif'  => $idKategori,
            'keyword'        => $keyword,
        ]);
    }

    /**
     * Tambah Barang Baru (Khusus Owner / Role admin).
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama'          => ['required', 'string', 'max:255'],
            'id_kategori'   => ['nullable', 'exists:kategori_barang,id_kategori'],
            'barcode'       => ['nullable', 'string', 'max:255', 'unique:katalog_barang,barcode'],
            'satuan'        => ['required', 'string', 'max:50'],
            'harga_beli'    => ['required', 'numeric', 'min:0'],
            'harga_jual'    => ['required', 'numeric', 'min:0'],
            'stok_sekarang' => ['required', 'integer', 'min:0'],
            'gambar'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'nama.required'       => 'Nama barang wajib diisi.',
            'barcode.unique'      => 'Barcode ini sudah terdaftar pada barang lain.',
            'harga_beli.required' => 'Harga beli (HPP) wajib diisi.',
            'harga_jual.required' => 'Harga jual wajib diisi.',
            'gambar.image'        => 'File yang dipilih harus berupa gambar.',
            'gambar.max'          => 'Ukuran foto maksimal 2MB.',
        ]);

        if ($request->hasFile('gambar')) {
            $path = $request->file('gambar')->store('produk', 'public');
            $validated['gambar'] = $path;
        }

        KatalogBarang::create($validated);

        return redirect()->route('produk.index')->with('success', "Barang '{$validated['nama']}' berhasil ditambahkan ke inventaris.");
    }

    /**
     * Update Barang (Khusus Owner / Role admin).
     */
    public function update(Request $request, int $id): RedirectResponse
    {
        $barang = KatalogBarang::findOrFail($id);

        $validated = $request->validate([
            'nama'          => ['required', 'string', 'max:255'],
            'id_kategori'   => ['nullable', 'exists:kategori_barang,id_kategori'],
            'barcode'       => ['nullable', 'string', 'max:255', Rule::unique('katalog_barang', 'barcode')->ignore($barang->id_barang, 'id_barang')],
            'satuan'        => ['required', 'string', 'max:50'],
            'harga_beli'    => ['required', 'numeric', 'min:0'],
            'harga_jual'    => ['required', 'numeric', 'min:0'],
            'stok_sekarang' => ['required', 'integer', 'min:0'],
            'gambar'        => ['nullable', 'image', 'mimes:jpeg,png,jpg,webp', 'max:2048'],
        ], [
            'nama.required'       => 'Nama barang wajib diisi.',
            'barcode.unique'      => 'Barcode ini sudah terdaftar pada barang lain.',
            'harga_beli.required' => 'Harga beli (HPP) wajib diisi.',
            'harga_jual.required' => 'Harga jual wajib diisi.',
            'gambar.image'        => 'File yang dipilih harus berupa gambar.',
            'gambar.max'          => 'Ukuran foto maksimal 2MB.',
        ]);

        if ($request->hasFile('gambar')) {
            // Hapus gambar lama jika ada
            if ($barang->gambar && Storage::disk('public')->exists($barang->gambar)) {
                Storage::disk('public')->delete($barang->gambar);
            }
            $path = $request->file('gambar')->store('produk', 'public');
            $validated['gambar'] = $path;
        }

        $barang->update($validated);

        return redirect()->route('produk.index')->with('success', "Data barang '{$barang->nama}' berhasil diperbarui.");
    }

    /**
     * Hapus Barang dari Katalog (Khusus Owner / Role admin).
     */
    public function destroy(int $id): RedirectResponse
    {
        $barang = KatalogBarang::findOrFail($id);
        $nama = $barang->nama;

        if ($barang->gambar && Storage::disk('public')->exists($barang->gambar)) {
            Storage::disk('public')->delete($barang->gambar);
        }

        $barang->delete();

        return redirect()->route('produk.index')->with('success', "Barang '{$nama}' berhasil dihapus dari sistem.");
    }
}
