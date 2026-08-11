<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class FacilityController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenants_idTenant;

        $facilities = Facility::where('tenants_idTenant', $tenantId)
            ->with(['rooms' => fn ($query) => $query->orderBy('nama_ruangan')])
            ->orderBy('idfasiltas', 'desc')
            ->get();

        return view('owner.facilities.index', compact('facilities'));
    }

    public function create()
    {
        return view('owner.facilities.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_fasilitas' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1|max:999',
            'status' => 'required|in:0,1',
        ]);

        $tenantId = Auth::user()->tenants_idTenant;

        Facility::create([
            'nama_fasilitas' => $request->nama_fasilitas,
            'deskripsi' => $request->deskripsi,
            'quantity' => (int) $request->quantity,
            'status' => (int) $request->status,
            'tenants_idTenant' => $tenantId,
        ]);

        return redirect()->route('owner.facilities.index')
            ->with('success', 'Fasilitas berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $tenantId = Auth::user()->tenants_idTenant;

        $facility = Facility::where('tenants_idTenant', $tenantId)
            ->where('idfasiltas', $id)
            ->firstOrFail();

        return view('owner.facilities.edit', compact('facility'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_fasilitas' => 'required|string|max:100',
            'deskripsi' => 'nullable|string|max:255',
            'quantity' => 'required|integer|min:1|max:999',
            'status' => 'required|in:0,1',
        ]);

        $tenantId = Auth::user()->tenants_idTenant;

        $facility = Facility::where('tenants_idTenant', $tenantId)
            ->with('rooms')
            ->where('idfasiltas', $id)
            ->firstOrFail();

        $facility->update([
            'nama_fasilitas' => $request->nama_fasilitas,
            'deskripsi' => $request->deskripsi,
            'quantity' => (int) $request->quantity,
            'status' => (int) $request->status,
        ]);

        return redirect()->route('owner.facilities.index')
            ->with('success', 'Fasilitas berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $tenantId = Auth::user()->tenants_idTenant;

        $facility = Facility::where('tenants_idTenant', $tenantId)
            ->where('idfasiltas', $id)
            ->firstOrFail();

        $facility->rooms()->detach();
        $facility->delete();

        return redirect()->route('owner.facilities.index')
            ->with('success', 'Fasilitas berhasil dihapus.');
    }
}
