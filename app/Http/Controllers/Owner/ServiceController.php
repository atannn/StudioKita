<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ServiceController extends Controller
{
    public function index()
    {
        $tenantId = Auth::user()->tenants_idTenant;

        $services = Service::where('tenants_idTenant', $tenantId)
            ->orderBy('idservice', 'desc')
            ->get();

        return view('owner.services.index', compact('services'));
    }

    public function create()
    {
        return view('owner.services.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama_service'   => 'required|string|max:100',
            'tipe_service'   => 'required|in:latihan,rekaman',
            'durasi_menit'   => 'required|integer|min:15',
            'weekday_price'  => 'required|numeric|min:0',
            'weekend_price'  => 'required|numeric|min:0',
            'deskripsi'      => 'nullable|string|max:255',
            'status'         => 'required|in:0,1',
        ]);

        $tenantId = Auth::user()->tenants_idTenant;

        Service::create([
            'nama_service' => $request->nama_service,
            'tipe_service' => $request->tipe_service,
            'durasi_menit' => (int) $request->durasi_menit,
            'weekday_price' => $request->weekday_price,
            'weekend_price' => $request->weekend_price,
            'deskripsi' => $request->deskripsi,
            'status' => (int) $request->status,
            'tenants_idTenant' => $tenantId,
        ]);

        return redirect()->route('owner.services.index')
            ->with('success', 'Service berhasil ditambahkan.');
    }

    public function edit($id)
    {
        $tenantId = Auth::user()->tenants_idTenant;

        $service = Service::where('tenants_idTenant', $tenantId)
            ->where('idservice', $id)
            ->firstOrFail();

        return view('owner.services.edit', compact('service'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama_service'   => 'required|string|max:100',
            'tipe_service'   => 'required|in:latihan,rekaman',
            'durasi_menit'   => 'required|integer|min:15',
            'weekday_price'  => 'required|numeric|min:0',
            'weekend_price'  => 'required|numeric|min:0',
            'deskripsi'      => 'nullable|string|max:255',
            'status'         => 'required|in:0,1',
        ]);

        $tenantId = Auth::user()->tenants_idTenant;

        $service = Service::where('tenants_idTenant', $tenantId)
            ->where('idservice', $id)
            ->firstOrFail();

        $service->update([
            'nama_service' => $request->nama_service,
            'tipe_service' => $request->tipe_service,
            'durasi_menit' => (int) $request->durasi_menit,
            'weekday_price' => $request->weekday_price,
            'weekend_price' => $request->weekend_price,
            'deskripsi' => $request->deskripsi,
            'status' => (int) $request->status,
        ]);

        return redirect()->route('owner.services.index')
            ->with('success', 'Service berhasil diperbarui.');
    }

    public function destroy($id)
    {
        $tenantId = Auth::user()->tenants_idTenant;

        $service = Service::where('tenants_idTenant', $tenantId)
            ->where('idservice', $id)
            ->firstOrFail();

        $service->delete();

        return redirect()->route('owner.services.index')
            ->with('success', 'Service berhasil dihapus.');
    }
}
