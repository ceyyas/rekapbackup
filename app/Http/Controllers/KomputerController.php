<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departemen;
use App\Models\Perusahaan;
use App\Models\Inventori;

class KomputerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // ambil data perusahaan untuk dropdown
        $perusahaans = Perusahaan::with('departemen')->get();

        $komputers = Inventori::with(['perusahaan', 'departemen'])
            ->where('kategori', 'PC')

            // filter perusahaan
            ->when($request->perusahaan_id, function ($query) use ($request) {
                $query->where('perusahaan_id', $request->perusahaan_id);
            })

            // filter departemen
            ->when($request->departemen_id, function ($query) use ($request) {
                $query->where('departemen_id', $request->departemen_id);
            })

            ->get();

        return view('komputer.index', compact('komputers', 'perusahaans'));
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $perusahaans = Perusahaan::with('departemen')->get();
        return view('komputer.create', compact('perusahaans'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
        'perusahaan_id' => 'required',
        'departemen_id' => 'required',
        'hostname'      => 'required',
        'username'      => 'required',
        'email'         => 'required|email',
        ]);

        Inventori::create([
            'perusahaan_id' => $request->perusahaan_id,
            'departemen_id' => $request->departemen_id,
            'hostname'      => $request->hostname,
            'username'      => $request->username,
            'email'         => $request->email,
            'kategori'      => 'PC', 
        ]);

        return redirect()->route('komputer.index')
            ->with('success', 'Data komputer berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $komputer = Inventori::with(['perusahaan', 'departemen'])
        ->where('kategori', 'PC')
        ->findOrFail($id);

        return view('komputer.show', compact('komputer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $komputer = Inventori::where('kategori', 'PC')->findOrFail($id);

        $perusahaans = Perusahaan::with('departemen')->get();
        $departemens = Departemen::where('perusahaan_id', $komputer->perusahaan_id)->get();

        return view('komputer.edit', compact('komputer', 'perusahaans', 'departemens'));

    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $komputer = Inventori::where('kategori', 'PC')->findOrFail($id);

        $komputer->update([
            'hostname'       => $request->hostname,
            'username'       => $request->username,
            'email'          => $request->email,
            'perusahaan_id'  => $request->perusahaan_id,
            'departemen_id'  => $request->departemen_id,
        ]);

        return redirect()->route('komputer.index')
                     ->with('success', 'Data komputer berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $komputer = Inventori::where('kategori', 'PC')->findOrFail($id);
        $komputer->delete();

        return redirect()->route('komputer.index')
            ->with('success', 'Data komputer berhasil dihapus');
    }
}
