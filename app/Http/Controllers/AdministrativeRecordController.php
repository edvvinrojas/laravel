<?php

namespace App\Http\Controllers;

use App\Models\AdministrativeRecord;
use App\Models\Employee;
use Illuminate\Http\Request;

class AdministrativeRecordController extends Controller
{
    public function index(Request $request)
    {
        $records = AdministrativeRecord::with(['employee', 'issuedBy'])
            ->when($request->search, fn($q) => $q->whereHas('employee', fn($e) => $e->where('nombre', 'like', "%{$request->search}%")))
            ->when($request->type, fn($q) => $q->where('type_administrative', $request->type))
            ->orderBy('created_at', 'desc')
            ->paginate(20)->withQueryString();

        return view('administrative-records.index', compact('records'));
    }

    public function create()
    {
        $employees = Employee::where('is_active', true)->orderBy('nombre')->get();
        return view('administrative-records.create', compact('employees'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id'         => 'required|exists:employees,id',
            'type_administrative' => 'required|in:RETROALIMENTACION_ESCRITA,AMONESTACION,ACTA_ADMINISTRATIVA,ENTREVISTA_AUSENTISMO',
            'suspended_days'      => 'nullable|integer|min:0',
            'start_date'          => 'nullable|date',
            'end_date'            => 'nullable|date',
            'description'         => 'required|string',
        ]);

        $data['issued_by'] = auth()->id();
        $data['suspended_days'] = $data['suspended_days'] ?? 0;

        AdministrativeRecord::create($data);
        return redirect()->route('administrative-records.index')->with('success', 'Registro administrativo creado.');
    }

    public function show(AdministrativeRecord $administrativeRecord)
    {
        $administrativeRecord->load(['employee', 'issuedBy']);
        return view('administrative-records.show', compact('administrativeRecord'));
    }

    public function edit(AdministrativeRecord $administrativeRecord)
    {
        $employees = Employee::where('is_active', true)->orderBy('nombre')->get();
        return view('administrative-records.edit', compact('administrativeRecord', 'employees'));
    }

    public function update(Request $request, AdministrativeRecord $administrativeRecord)
    {
        $data = $request->validate([
            'type_administrative' => 'required|in:RETROALIMENTACION_ESCRITA,AMONESTACION,ACTA_ADMINISTRATIVA,ENTREVISTA_AUSENTISMO',
            'suspended_days'      => 'nullable|integer|min:0',
            'start_date'          => 'nullable|date',
            'end_date'            => 'nullable|date',
            'description'         => 'required|string',
        ]);

        $data['suspended_days'] = $data['suspended_days'] ?? 0;
        $administrativeRecord->update($data);
        return redirect()->route('administrative-records.show', $administrativeRecord)->with('success', 'Registro actualizado.');
    }

    public function destroy(AdministrativeRecord $administrativeRecord)
    {
        $administrativeRecord->delete();
        return redirect()->route('administrative-records.index')->with('success', 'Registro eliminado.');
    }
}
