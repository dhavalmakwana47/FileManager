<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\UserRequest;
use App\Models\Company;
use App\Models\CompanyRole;
use App\Models\CompanyUser;
use App\Models\CompanyUserRole;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class UserController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission_check:Users,view', only: ['index', 'show']),
            new Middleware('permission_check:Users,create', only: ['create', 'store']),
            new Middleware('permission_check:Users,update', only: ['edit', 'update']),
            new Middleware('permission_check:Users,delete', only: ['edit', 'destroy']),
        ];
    }

    public function index(Request $request)
    {
        $currentUser = current_user();
        if (request()->ajax()) {

            // If the user is not a master admin, select users associated with the active company
            $users = User::with('companies', 'companyRoles')
                ->select(['id', 'name', 'email', 'created_at'])
                ->whereHas('companies', function ($query) {
                    $query->where('company_id', get_active_company());
                })
                ->whereHas('companyRoles', function ($query) {
                    $query->whereNot('role_name', 'Super Admin');
                })
                ->get();
            // dd($users);

            return DataTables::of($users)
                ->addColumn('action', function ($user) use ($currentUser) {
                    $actionButtons = '';

                    if ($currentUser->hasPermission('Users', 'update')) {
                        $editUrl = route('users.edit', $user->id);
                        $actionButtons .= '<a href="' . $editUrl . '" class="btn btn-sm btn-primary">Edit</a>';
                    }

                    if ($currentUser->hasPermission('Users', 'delete')) {
                        $deleteUrl = route('users.destroy', $user->id);
                        $actionButtons .= '<form action="' . $deleteUrl . '" method="POST" style="display:inline;">
                                        ' . csrf_field() . '
                                        ' . method_field('DELETE') . '
                                        <button type="submit" class="btn btn-sm btn-danger" 
                                            onclick="return confirm(\'Are you sure you want to delete this user?\');">Delete</button>
                                       </form>';
                    }

                    return $actionButtons;
                })


                ->make(true);
        }

        return view('app.users.index');
    }

    public function create()
    {

        $data['roleArr'] = CompanyRole::whereNot('role_name', 'Super Admin')->where('company_id', get_active_company())->get();
        return view('app.users.add-update', $data);
    }

    public function store(UserRequest $request)
    {
        $user = User::where('email', $request->input('user_email'))->first();
        $activeCompanyId = get_active_company();
        if (!isset($user)) {
            // Create the admin user for the company
            $user = User::create([
                'name' => $request->input('user_name'),
                'email' => $request->input('user_email'),
                'password' => bcrypt($request->input('password'))
            ]);
        }

        CompanyUser::updateOrCreate(
            [
                'user_id' => $user->id,
                'company_id' => get_active_company()
            ]
        );

        CompanyUserRole::create([
            'user_id' => $user->id,
            'company_id' => $activeCompanyId,
            'company_role_id' => $request->role
        ]);


        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    public function edit(User $user)
    {
        if ($this->admin_check($user)) {
            return redirect()->route('login');
        }

        $data['user'] = $user;
        $data['roleArr'] = CompanyRole::whereNot('role_name', 'Super Admin')->where('company_id', get_active_company())->get();

        return view('app.users.add-update', $data);
    }

    public function update(UserRequest $request, $id)
    {
        $user = User::findOrFail($id);

        // Update user information
        $user->update([
            'name' => $request->input('user_name'),
            'email' => $request->input('user_email'),
        ]);

        // Get the active company ID
        $activeCompanyId = get_active_company();

        // Retrieve the current role for the active company
        $currentRole = CompanyUserRole::where('user_id', $id)
            ->where('company_id', $activeCompanyId)
            ->first(); // Get the current role ID

        // Check if the new role is different from the current role
        if (!isset($currentRole) || $currentRole->company_role_id != $request->role) {
            // If different, delete the old role
            CompanyUserRole::where('company_id', $activeCompanyId)
                ->where('user_id', $id)
                ->delete();

            // Create a new CompanyUserRole entry
            CompanyUserRole::create([
                'user_id' => $id,
                'company_id' => $activeCompanyId,
                'company_role_id' => $request->role,
            ]);
        }

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    public function destroy($id)
    {
        $user = User::find($id);
        if ($this->admin_check($user)) {
            return redirect()->route('login');
        }
        $user->delete();
        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    public function change_company(Request $request)
    {

        session(['active_company' => $request->company_id]);
        return redirect()->route('dashboard');
    }
}
