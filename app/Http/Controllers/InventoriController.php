<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departemen;
use App\Models\Perusahaan;
use App\Models\Inventori;
use Illuminate\Validation\Rule;

class InventoriController extends Controller
{
    public function index(Request $request)
    {
        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();
        $departemens = collect();

        if ($request->perusahaan_id) {
            $departemens = Departemen::where('perusahaan_id', $request->perusahaan_id)
                ->orderBy('nama_departemen')
                ->get();
        }

        $inventories = Inventori::with(['perusahaan', 'departemen'])
            ->when($request->perusahaan_id, fn($q) => $q->where('perusahaan_id', $request->perusahaan_id))
            ->when($request->departemen_id, fn($q) => $q->where('departemen_id', $request->departemen_id))
            ->orderByDesc('updated_at')
            ->get();

        return view('inventori.index', compact('inventories','perusahaans','departemens'));
    }

    public function filter(Request $request)
    {
        $inventories = Inventori::with(['perusahaan','departemen'])
            ->when($request->kategori, fn($q) => $q->where('kategori', $request->kategori)) // filter PC/Laptop kalau dipilih
            ->when($request->perusahaan_id, fn($q) => $q->where('perusahaan_id', $request->perusahaan_id))
            ->when($request->departemen_id, fn($q) => $q->where('departemen_id', $request->departemen_id))
            ->orderByDesc('updated_at')
            ->get();

        return view('inventori.partials.rows', compact('inventories'));
    }

    public function create()
    {
        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();
        $departemens = old('perusahaan_id')
            ? Departemen::where('perusahaan_id', old('perusahaan_id'))->get()
            : collect();

        return view('inventori.create', compact('perusahaans','departemens'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'perusahaan_id' => ['required','exists:perusahaan,id'],
            'departemen_id' => [
                'required',
                Rule::exists('departemen','id')->where(fn($q) => $q->where('perusahaan_id',$request->perusahaan_id)),
            ],
            'hostname' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email'    => 'nullable|email',
            'kategori' => 'required|in:PC,Laptop',
        ]);

        Inventori::create($request->all());

        return redirect()->route('inventori.index')->with('success','Data berhasil ditambahkan');
    }

    public function show(string $id)
    {
        $inventori = Inventori::with(['perusahaan','departemen'])->findOrFail($id);
        return view('inventori.show', compact('inventori'));
    }

    public function edit(string $id)
    {
        $inventori = Inventori::findOrFail($id);
        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();
        $departemens = Departemen::where('perusahaan_id',$inventori->perusahaan_id)->get();

        return view('inventori.edit', compact('inventori','perusahaans','departemens'));
    }

    public function update(Request $request, string $id)
    {
        $inventori = Inventori::findOrFail($id);

        $request->validate([
            'perusahaan_id' => ['required','exists:perusahaan,id'],
            'departemen_id' => [
                'required',
                Rule::exists('departemen','id')->where(fn($q) => $q->where('perusahaan_id',$request->perusahaan_id)),
            ],
            'hostname' => 'required|string|max:255',
            'username' => 'required|string|max:255',
            'email'    => 'nullable|email',
            'status'   => 'required|in:active,inactive',
            'kategori' => 'required|in:PC,Laptop',
        ]);

        $inventori->update($request->all());

        return redirect()->route('inventori.index')->with('success','Data berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $inventori = Inventori::findOrFail($id);
        $inventori->delete();

        return redirect()->route('inventori.index')->with('success','Data berhasil dihapus');
    }
}
