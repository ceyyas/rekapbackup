<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departemen;
use App\Models\Perusahaan;
use App\Models\Inventori;
use Illuminate\Validation\Rule;

class LaptopController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();

        // dropdown departemen (kosong dulu)
        $departemens = collect();

        // isi departemen hanya jika perusahaan dipilih
        if ($request->perusahaan_id) {
            $departemens = Departemen::where('perusahaan_id', $request->perusahaan_id)
                ->orderBy('nama_departemen')
                ->get();
        }

        $laptops = Inventori::with(['perusahaan', 'departemen'])
            ->where('kategori', 'Laptop')

            // filter perusahaan
            ->when($request->perusahaan_id, function ($query) use ($request) {
                $query->where('perusahaan_id', $request->perusahaan_id);
            })

            // filter departemen
            ->when($request->departemen_id, function ($query) use ($request) {
                $query->where('departemen_id', $request->departemen_id);
            })

            ->get();

        return view('laptop.index', compact('laptops', 'perusahaans', 'departemens'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();

        // departemen kosong dulu
        $departemens = collect();

        if (old('perusahaan_id')) {
            $departemens = Departemen::where('perusahaan_id', old('perusahaan_id'))->get();
        }

        return view('laptop.create', compact('perusahaans', 'departemens'));
    }

    public function getDepartemen(Request $request)
    {
        return Departemen::where('perusahaan_id', $request->perusahaan_id)
            ->orderBy('nama_departemen')
            ->get();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'perusahaan_id' => ['required', 'exists:perusahaan,id'],
            'departemen_id' => [
                'required',
                Rule::exists('departemen', 'id')
                    ->where(fn ($q) =>
                        $q->where('perusahaan_id', $request->perusahaan_id)
                    ),
            ],
            'hostname' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email'    => 'required|email',
        ]);

        Inventori::create([
            'perusahaan_id' => $request->perusahaan_id,
            'departemen_id' => $request->departemen_id,
            'hostname'      => $request->hostname,
            'username'      => $request->username,
            'email'         => $request->email,
            'kategori'      => 'Laptop', 
        ]);

        return redirect()->route('laptop.index')
            ->with('success', 'Data laptop berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $laptop = Inventori::with(['perusahaan', 'departemen'])
        ->where('kategori', 'Laptop')
        ->findOrFail($id);

        return view('laptop.show', compact('laptop'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $laptop = Inventori::where('kategori', 'Laptop')->findOrFail($id);

        $perusahaans = Perusahaan::with('departemen')->get();
        $departemens = Departemen::where('perusahaan_id', $laptop->perusahaan_id)->get();

        return view('laptop.edit', compact('laptop', 'perusahaans', 'departemens'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $laptop = Inventori::where('kategori', 'Laptop')->findOrFail($id);

        $request->validate([
            'perusahaan_id' => ['required', 'exists:perusahaan,id'],
            'departemen_id' => [
                'required',
                Rule::exists('departemen', 'id')
                    ->where(fn ($q) =>
                        $q->where('perusahaan_id', $request->perusahaan_id)
                    ),
            ],

            'hostname' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email'    => 'required|email',
        ]);

        $laptop->update([
            'hostname'       => $request->hostname,
            'username'       => $request->username,
            'email'          => $request->email,
            'perusahaan_id'  => $request->perusahaan_id,
            'departemen_id'  => $request->departemen_id,
        ]);

        return redirect()->route('laptop.index')
                     ->with('success', 'Data laptop berhasil diupdate');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $laptop = Inventori::where('kategori', 'Laptop')->findOrFail($id);
        $laptop->delete();

        return redirect()->route('laptop.index')
            ->with('success', 'Data laptop berhasil dihapus');
    }
}
