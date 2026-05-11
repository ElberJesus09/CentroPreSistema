<?php

namespace App\Http\Controllers;

use App\Http\Requests\Staff\StoreStaffRequest;
use App\Http\Requests\Staff\UpdateStaffRequest;
use App\Models\Staff;
use App\Services\StaffService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class StaffController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Staff::class, 'staff');
    }

    public function index(StaffService $staffService): View
    {
        return view('staff.index', [
            'staffList' => $staffService->paginateIndex(),
        ]);
    }

    public function create(StaffService $staffService): View
    {
        return view('staff.create', [
            'roles' => $staffService->assignableRolesQuery()->get(),
        ]);
    }

    public function store(StoreStaffRequest $request, StaffService $staffService): RedirectResponse
    {
        $data = $request->validated();
        unset($data['password_confirmation']);
        $staffService->create($data);

        return redirect()->route('staff.index')->with('success', 'Empleado registrado correctamente.');
    }

    public function edit(Staff $staff, StaffService $staffService): View
    {
        return view('staff.edit', [
            'staffMember' => $staff->load('role'),
            'roles' => $staffService->assignableRolesQuery()->get(),
        ]);
    }

    public function update(UpdateStaffRequest $request, Staff $staff, StaffService $staffService): RedirectResponse
    {
        $staffService->update($staff, $request->validatedForUpdate());

        return redirect()->route('staff.index')->with('success', 'Empleado actualizado correctamente.');
    }

    public function destroy(Staff $staff, StaffService $staffService): RedirectResponse
    {
        $staffService->delete($staff);

        return redirect()->route('staff.index')->with('success', 'Empleado eliminado correctamente.');
    }
}
