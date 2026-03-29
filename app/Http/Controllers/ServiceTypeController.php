<?php

namespace App\Http\Controllers;

use App\Models\ServiceType;
use Illuminate\Http\Request;

class ServiceTypeController extends Controller
{
    public function index()
    {
        $serviceTypes = ServiceType::withCount('monthlyPlans')->orderBy('name')->paginate(20);
        return view('service-types.index', compact('serviceTypes'));
    }

    public function create()
    {
        return view('service-types.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:service_types,name',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        ServiceType::create($data);
        return redirect()->route('service-types.index')->with('success', 'Tipo de servicio creado.');
    }

    public function edit(ServiceType $serviceType)
    {
        return view('service-types.edit', compact('serviceType'));
    }

    public function update(Request $request, ServiceType $serviceType)
    {
        $data = $request->validate([
            'name'        => 'required|string|max:100|unique:service_types,name,' . $serviceType->id,
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);
        $data['is_active'] = $request->boolean('is_active', true);
        $serviceType->update($data);
        return redirect()->route('service-types.index')->with('success', 'Tipo de servicio actualizado.');
    }

    public function destroy(ServiceType $serviceType)
    {
        $serviceType->delete();
        return redirect()->route('service-types.index')->with('success', 'Tipo de servicio eliminado.');
    }
}
