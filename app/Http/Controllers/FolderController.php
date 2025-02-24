<?php

namespace App\Http\Controllers;

use App\Models\CompanyRole;
use App\Models\Folder;
use App\Models\RoleFolderPermission;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class FolderController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('permission_check:Folder,view', only: ['index', 'show']),
            new Middleware('permission_check:Folder,create', only: ['create', 'store']),
            new Middleware('permission_check:Folder,update', only: ['edit', 'update']),
            new Middleware('permission_check:Folder,delete', only: ['destroy']),
        ];
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $data = [];
        $data['folderArr'] = Folder::where('company_id', get_active_company())->where('parent_id', null)->get();
        $data['allFolderArr'] = Folder::where('company_id', get_active_company())->get();
        $data['roleArr'] = CompanyRole::whereNot('role_name', 'Super Admin')->where('company_id', get_active_company())->get();
        return view('app.folder.index2', $data);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        // Fetch the active company ID using get_active_company()
        $company_id = get_active_company();

        // Check if the active company ID is valid
        if (!$company_id) {
            return response()->json([
                'success' => false,
                'message' => 'Active company not found.',
            ], 400); // 400 Bad Request status code
        }

        // Validate the incoming request (only validating name and parent_id)
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:folders,id', // Assuming parent_id refers to a valid folder
            'role' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    $invalidIds = array_filter($value, function ($roleId) {
                        return !CompanyRole::where('id', $roleId)->exists();
                    });

                    if (!empty($invalidIds)) {
                        $fail('The following role IDs are invalid: ' . implode(', ', $invalidIds));
                    }
                },
            ],
        ]);

        try {
            // Create the folder with the validated data
            $folder = Folder::create([
                'name' => $validated['name'],
                'parent_id' => $validated['parent_id'] ?? null, // Ensure parent_id is nullable
                'company_id' => $company_id, // Use the active company ID,
                'created_by' => current_user()->id

            ]);

            $roleIds = $request->role ?? []; // Use an empty array if no roles are provided

            foreach ($roleIds as $roleId) {
                RoleFolderPermission::create([
                    'company_role_id' => $roleId,
                    'folder_id' => $folder->id,
                ]);
            }

            // Return a success response
            return response()->json([
                'success' => true,
                'message' => 'Folder created successfully!',
                'data' => $folder // Optionally, return the created folder data
            ], 200); // 201 Created status code

        } catch (\Exception $e) {
            // Log the exception
            \Log::error('Folder creation failed: ' . $e->getMessage());

            // Return an error response
            return response()->json([
                'success' => false,
                'message' => 'There was an error creating the folder.',
                'error' => $e->getMessage() // Optionally, include the error message
            ], 500); // 500 Internal Server Error
        }
    }





    /**
     * Display the specified resource.
     */
    public function show(Folder $folder)
    {
        return response()->json($folder->access_to_role->pluck('company_role_id'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Folder $folder)
    {
        //
    }
    public function update(Request $request, $id)
    {
        // Find the folder
        $folder = Folder::findOrFail($id);

        // Validate the input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => [
                'nullable',
                'array',
                function ($attribute, $value, $fail) {
                    $invalidIds = array_filter($value, function ($roleId) {
                        return !CompanyRole::where('id', $roleId)->exists();
                    });

                    if (!empty($invalidIds)) {
                        $fail('The following role IDs are invalid: ' . implode(', ', $invalidIds));
                    }
                },
            ],
        ]);

        // Update folder details
        $folder->update([
            'name' => $validated['name'],
            'updated_by' => current_user()->id
        ]);

        // Sync roles in RoleFolderPermission
        $roleIds = $request->role ?? []; // Use an empty array if no roles are provided

        // Get existing role permissions for this folder
        $existingPermissions = RoleFolderPermission::where('folder_id', $id)
            ->pluck('company_role_id')
            ->toArray();

        // Find roles to add
        $rolesToAdd = array_diff($roleIds, $existingPermissions);

        // Find roles to delete
        $rolesToDelete = array_diff($existingPermissions, $roleIds);

        // Delete old permissions
        RoleFolderPermission::where('folder_id', $id)
            ->whereIn('company_role_id', $rolesToDelete)
            ->delete();

        // Insert new permissions
        foreach ($rolesToAdd as $roleId) {
            RoleFolderPermission::create([
                'company_role_id' => $roleId,
                'folder_id' => $id,
            ]);
        }

        // Return success response
        return response()->json(['success' => true, 'message' => 'Folder updated successfully!', 'data' => $folder]);
    }



    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $folder = Folder::findOrFail($id);

            // Ensure the folder is deleted
            $folder->delete();

            // Return success response
            return response()->json([
                'status' => 'success',
                'message' => 'Folder deleted successfully.'
            ]);
        } catch (\Exception $e) {
            // Return error response if something goes wrong
            return response()->json([
                'status' => 'error',
                'message' => 'There was an error deleting the folder.'
            ], 500);
        }
    }

    public function fileManager()
    {
        $folders = Folder::where('company_id', get_active_company())
            ->whereNull('parent_id')
            ->with('subfolders') // Eager load subfolders
            ->get();

        $fileManager = $this->buildFileTree($folders);

        return response()->json($fileManager);
    }

    /**
     * Recursive function to build folder structure
     */
    private function buildFileTree($folders)
    {
        $tree = [];

        foreach ($folders as $folder) {
            $node = [
                'id' => $folder->id,  // Unique ID required for frontend
                'parentId' => $folder->parent_id, // Reference to the parent
                'name' => $folder->name,
                'isDirectory' => true, // Required for folders
                "permissions" => ["delete" => (bool)rand(0, 1)], // ✅ Random true/false
                'items' => $folder->subfolders->isNotEmpty()
                    ? $this->buildFileTree($folder->subfolders)
                    : []
            ];
            $tree[] = $node;
        }

        return $tree;
    }
}
