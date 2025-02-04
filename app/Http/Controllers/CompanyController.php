<?php

namespace App\Http\Controllers;

use App\Http\Requests\Company\AddUpdateRequest;
use App\Models\Company;
use App\Models\CompanyRole;
use App\Models\CompanyRolePermission;
use App\Models\CompanyUser;
use App\Models\CompanyUserRole;
use App\Models\Permission;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class CompanyController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission_check:Company,view', only: ['index', 'show']),
            new Middleware('permission_check:Company,create', only: ['create', 'store']),
            new Middleware('permission_check:Company,update', only: ['edit', 'update']),
            new Middleware('permission_check:Company,delete', only: ['edit', 'destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (request()->ajax()) {
            $companyArr = Company::select(['id', 'name', 'created_at']);
            return DataTables::of($companyArr)
                ->addColumn('action', function ($company) {
                    $editUrl = route('company.edit', $company->id);
                    $deleteUrl = route('company.destroy', $company->id); // Assuming 'company.destroy' is the delete route

                    return '
                    <a href="' . $editUrl . '" class="btn btn-sm btn-primary">Edit</a>
                    
                    <form action="' . $deleteUrl . '" method="POST" style="display:inline;">
                        ' . csrf_field() . '
                        ' . method_field('DELETE') . '
                        <button type="submit" class="btn btn-sm btn-danger" 
                            onclick="return confirm(\'Are you sure you want to delete this company?\');">Delete</button>
                    </form>
                ';
                })
                ->make(true);
        }

        return view('app.company.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('app.company.addupdate');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AddUpdateRequest $request)
    {
        // Start a database transaction
        DB::beginTransaction();

        try {


            $user = User::where('email', $request->input('user_email'))->first();
            if (!isset($user)) {
                // Create the admin user for the company
                $user = User::create([
                    'name' => $request->input('user_name'),
                    'email' => $request->input('user_email'),
                    'password' => bcrypt($request->input('password'))
                ]);
            }
            $company = Company::create([
                'name' => $request->input('company_name'),
                'admin_id' => $user->id
            ]);
            CompanyUser::create([
                'user_id' => $user->id,
                'company_id' => $company->id
            ]);

            $companyRole =  CompanyRole::create([
                'company_id' => $company->id,
                'role_name' => 'Super Admin'
            ]);

            CompanyUserRole::create([
                'company_id' => $company->id,
                'user_id' => $user->id,
                'company_role_id' => $companyRole->id
            ]);

            $permissions = Permission::all();

            foreach ($permissions as $permission) {
                CompanyRolePermission::create([
                    'company_role_id' => $companyRole->id,
                    'permission_id' => $permission->id
                ]);
            }

            // Commit the transaction
            DB::commit();

            return redirect()->route('company.index')->with('success', 'Company Created successfully.');
        } catch (\Exception $e) {
            // Rollback the transaction if an error occurs
            DB::rollBack();

            return redirect()->route('company.index')->with('error', 'Error creating company: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Company $company)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Company $company)
    {
        $data['company'] = $company;
        return view('app.company.addupdate', $data);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AddUpdateRequest $request, Company $company)
    {
        $company->update([
            'name' => $request->input('company_name'),
        ]);
        return redirect()->route('company.index')->with('success', 'Company Created successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Company $company)
    {
        $user = current_user();
        if (!$user->is_master_admin()) {
            return redirect()->route('login');
        }
        User::whereIn('id', CompanyUser::where('company_id', $company->id)->pluck('user_id')->toArray())->delete();
        $company->delete();
        return redirect()->route('company.index')->with('success', 'Company deleted successfully.');
    }
}
