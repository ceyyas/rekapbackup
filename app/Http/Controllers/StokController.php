<?php

namespace App\Http\Controllers;

use App\Models\Stok;
use Illuminate\Http\Request;

class StokController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $stoks = Stok::orderByDesc('created_at')->get();
        return view('stok.index', compact('stoks'));
    }

     public function data()
    {
        $stoks = Stok::orderByDesc('created_at')->get();

        $data = $stoks->map(function($stok){
            return [
                'id'            => $stok->id,
                'nomor_sppb'    => $stok->nomor_sppb,
                'nama_barang'   => $stok->nama_barang,
                'jumlah_barang' => $stok->jumlah_barang,
                'pemakaian'     => $stok->pemakaian, 
                'tersisa'       => $stok->tersisa,
                'aksi'          => view('stok.partials.actions', compact('stok'))->render()
            ];
        });

        return response()->json(['data' => $data]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $mediaList = [
            'CD 700 MB',
            'DVD 4.7 GB',
            'DVD 8.5 GB'
        ];

        return view('stok.create', compact('mediaList'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'nomor_sppb'    => 'required|string|max:255',
            'nama_barang'   => 'required|in:CD 700 MB,DVD 4.7 GB,DVD 8.5 GB',
            'jumlah_barang' => 'required|integer|min:1|max:127',
        ]);

        Stok::create([
            'nomor_sppb'    => $request->nomor_sppb,
            'nama_barang'   => $request->nama_barang,
            'jumlah_barang' => $request->jumlah_barang,
        ]);

        return redirect()
            ->route('stok.index')
            ->with('success', 'Stok berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $stok = Stok::findOrFail($id);
        return view('stok.show', compact('stok'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $stok = Stok::findOrFail($id);

        $mediaList = [
            'CD 700 MB',
            'DVD 4.7 GB',
            'DVD 8.5 GB'
        ];

        return view('stok.edit', compact('stok', 'mediaList'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nomor_sppb'    => 'required|string|max:255',
            'nama_barang'   => 'required|in:CD 700 MB,DVD 4.7 GB,DVD 8.5 GB',
            'jumlah_barang' => 'required|integer|min:1|max:127',
        ]);

        $stok = Stok::findOrFail($id);

        $stok->update([
            'nomor_sppb'    => $request->nomor_sppb,
            'nama_barang'   => $request->nama_barang,
            'jumlah_barang' => $request->jumlah_barang,
        ]);

        return redirect()
            ->route('stok.index')
            ->with('success', 'Stok berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $stok = Stok::findOrFail($id);
        $stok->delete();

        return redirect()
            ->route('stok.index')
            ->with('success', 'Stok berhasil dihapus');
    }
}
