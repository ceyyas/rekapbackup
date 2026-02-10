<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Departemen;
use App\Models\Perusahaan;
use App\Models\Inventori;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;

class KomputerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();
        $departemens = Departemen::orderBy('nama_departemen')->get();

        $komputers = Inventori::with(['perusahaan', 'departemen'])
            ->when($request->perusahaan_id, fn($q) => $q->where('perusahaan_id', $request->perusahaan_id))
            ->when($request->departemen_id, fn($q) => $q->where('departemen_id', $request->departemen_id))
            ->when($request->kategori_id, fn($q) => $q->where('kategori', $request->kategori_id))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('hostname', 'like', "%{$request->search}%")
                        ->orWhere('username', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                });
            })
            ->orderByDesc('updated_at')
            ->get();

        return view('komputer.index', compact('perusahaans','departemens','komputers'));
    }

    public function data(Request $request)
    {
        $query = Inventori::with(['perusahaan','departemen'])
            ->when($request->perusahaan_id, fn($q) => $q->where('perusahaan_id', $request->perusahaan_id))
            ->when($request->departemen_id, fn($q) => $q->where('departemen_id', $request->departemen_id))
            ->when($request->kategori_id, fn($q) => $q->where('kategori', $request->kategori_id))
            ->when($request->search, function ($q) use ($request) {
                $q->where(function ($sub) use ($request) {
                    $sub->where('hostname', 'like', "%{$request->search}%")
                        ->orWhere('username', 'like', "%{$request->search}%")
                        ->orWhere('email', 'like', "%{$request->search}%");
                });
            });

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('aksi', function($row){
                return '
                    <button class="aksi-show">
                        <a href="'.route('komputer.show',$row->id).'"><i class="bx bx-show"></i></a>
                    </button>
                    <button class="aksi-edit">
                        <a href="'.route('komputer.edit',$row->id).'"><i class="bx bx-edit-alt"></i></a>
                    </button>
                    <form action="'.route('komputer.destroy',$row->id).'" method="POST" style="display:inline;" onsubmit="return confirm(\'Are you sure?\')">
                        '.csrf_field().method_field('DELETE').'
                        <button type="submit" class="aksi-delete"><i class="bx bx-trash"></i></button>
                    </form>
                ';
            })
            ->rawColumns(['aksi'])
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
       $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();
        $departemens = old('perusahaan_id')
            ? Departemen::where('perusahaan_id', old('perusahaan_id'))->get()
            : collect();

        return view('komputer.create', compact('perusahaans', 'departemens'));
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
            'email'    => 'nullable|email',
            'kategori' => 'required|in:PC,Laptop',

        ]);

        Inventori::create($request->all());

        return redirect()
            ->route('komputer.index')
            ->with('success', 'Data komputer berhasil ditambahkan');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $komputer = Inventori::with(['perusahaan', 'departemen'])
            ->findOrFail($id);

        return view('komputer.show', compact('komputer'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $komputer = Inventori::findOrFail($id);

        $perusahaans = Perusahaan::orderBy('nama_perusahaan')->get();
        $departemens = Departemen::where('perusahaan_id', $komputer->perusahaan_id)->get();

        return view('komputer.edit', compact(
            'komputer',
            'perusahaans',
            'departemens'
        ));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $komputer = Inventori::findOrFail($id);

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
            'email'    => 'nullable|email',
            'status'   => 'required|in:active,inactive',
            'kategori' => 'required|in:PC,Laptop',
        ]);

        $komputer->update($request->all());

        return redirect()
            ->route('komputer.index')
            ->with('success', 'Komputer berhasil diperbarui');
             
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $komputer = Inventori::findOrFail($id);
        $komputer->delete();

        return redirect()
            ->route('komputer.index')
            ->with('success', 'Data komputer berhasil dihapus');
    }
}
