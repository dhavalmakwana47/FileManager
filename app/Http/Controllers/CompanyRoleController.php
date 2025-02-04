<?php

namespace App\Http\Controllers;

use App\Http\Requests\Company\CompanyRoleRequest;
use App\Models\Company;
use App\Models\CompanyRole;
use App\Models\CompanyUserRole;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CompanyRoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission_check:Company Role,view', only: ['index', 'show']),
            new Middleware('permission_check:Company Role,create', only: ['create', 'store']),
            new Middleware('permission_check:Company Role,update', only: ['edit', 'update']),
            new Middleware('permission_check:Company Role,delete', only: ['destroy']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $currentUser = current_user();

        if (request()->ajax()) {

            // If the user is not a master admin, select users associated with the active company
            $roles = CompanyRole::where('company_id', get_active_company())->whereNot('role_name', 'Super Admin')->get();

            return DataTables::of($roles)

                ->addColumn('action', function ($role) use ($currentUser) {
                    $actionButtons = '';

                    if ($currentUser->hasPermission('Company Role', 'update')) {
                        $editUrl = route('companyrole.edit', $role->id);
                        $actionButtons .= '<a href="' . $editUrl . '" class="btn btn-sm btn-primary">Edit</a> ';
                    }

                    if ($currentUser->hasPermission('Company Role', 'delete')) {
                        $deleteUrl = route('companyrole.destroy', $role->id);
                        $actionButtons .= '<form action="' . $deleteUrl . '" method="POST" style="display:inline;">
                                            ' . csrf_field() . '
                                            ' . method_field('DELETE') . '
                                            <button type="submit" class="btn btn-sm btn-danger" 
                                                onclick="return confirm(\'Are you sure you want to delete this role?\');">Delete</button>
                                       </form>';
                    }

                    return $actionButtons;
                })

                ->make(true);
        }

        return view('app.role.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('app.role.add-update');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(CompanyRoleRequest $request)
    {
        CompanyRole::create([
            'role_name' => $request->role_name,
            'company_id' => get_active_company()
        ]);

        return redirect()->route('companyrole.index')->with('success', 'Role created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(CompanyRole $companyRole)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(CompanyRole $companyrole)
    {
        if ($companyrole->role_name == "Super Admin") {
            return redirect()->route('login');
        }

        $data['role'] = $companyrole;

        return view('app.role.add-update', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CompanyRoleRequest $request, CompanyRole $companyrole)
    {
        $companyrole->update([
            'role_name' => $request->role_name
        ]);

        return redirect()->route('companyrole.index')->with('success', 'Role updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(CompanyRole $companyrole)
    {
        $usersrole =  CompanyUserRole::where('company_role_id', $companyrole->id)->count();
        if ($usersrole) {
            return redirect()->route('companyrole.index')->with('error', 'This role cannot be deleted because it is currently assigned to one or more users.');
        }
        $companyrole->delete();
        return redirect()->route('companyrole.index')->with('success', 'Role deleted successfully.');
    }
}
