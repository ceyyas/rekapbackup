<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departemen;
use App\Models\Perusahaan;
use Yajra\DataTables\Facades\DataTables;

class DepartemenController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perusahaans = Perusahaan::all();
        return view('departemen.index', compact('perusahaans'));
    }

    public function data(Request $request)
    {
        $query = Departemen::with('perusahaan')
            ->when($request->perusahaan_id, fn($q) => 
                $q->where('departemen.perusahaan_id', $request->perusahaan_id));

        return datatables()->eloquent($query)
            ->addIndexColumn()
            ->addColumn('perusahaan', fn($d) => $d->perusahaan->nama_perusahaan ?? '-')
            ->addColumn('aksi', fn($d) => view('departemen.partials.actions', ['departemen' => $d])->render())
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $perusahaan = Perusahaan::all();
        return view('departemen.create', compact('perusahaan'));    
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
        'nama_departemen' => 'required|string|max:255',
        'perusahaan_id'   => 'required|exists:perusahaan,id',
        ]);

        Departemen::create([
            'nama_departemen' => $request->nama_departemen,
            'perusahaan_id'   => $request->perusahaan_id,
        ]);

        return redirect()->route('departemen.index')
            ->with('success', 'Departemen berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $departemen = Departemen::with('perusahaan')->findOrFail($id);
        return view('departemen.show', compact('departemen'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $departemen = Departemen::findOrFail($id);
        $listPerusahaan = Perusahaan::pluck('nama_perusahaan', 'id');

        return view('departemen.edit', compact('departemen', 'listPerusahaan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $departemen = Departemen::findOrFail($id);
        $listPerusahaan = Perusahaan::pluck('nama_perusahaan', 'id');

        $departemen->update([
            'nama_departemen'=> $request->nama_departemen,
            'perusahaan_id'  => $request->perusahaan_id,
        ]);


        return redirect()->route('departemen.index')
            ->with('success', 'Data departemen berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $departemen = Departemen::findOrFail($id);
        $departemen->delete();

        return redirect()->route('departemen.index')
            ->with('success', 'Data departemen berhasil dihapus');
    }

    public function byPerusahaan(Request $request)
    {
        $departemen = Departemen::where('perusahaan_id', $request->perusahaan_id)->get();
        return response()->json($departemen);
    }

}
