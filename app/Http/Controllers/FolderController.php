<?php

namespace App\Http\Controllers;

use App\Http\Requests\Folder\FolderRequest;
use App\Models\CompanyRole;
use App\Models\File;
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
            new Middleware('permission_check:Folder,delete', only: ['destroy', 'deleteFolder']),
        ];
    }

    /**
     * Display a listing of folders.
     */
    public function index()
    {
        return view('app.folder.index', [
            'title' => "Add Folder",
            'assignedPermissions' => [],
            'folderArr' => Folder::where('company_id', get_active_company())->whereNull('parent_id')->get(),
            'allFolderArr' => Folder::where('company_id', get_active_company())->get(),
            'roleArr' => CompanyRole::whereNot('role_name', 'Super Admin')->where('company_id', get_active_company())->get(),
        ]);
    }

    /**
     * Store a new folder.
     */
    public function store(FolderRequest $request)
    {
        $company_id = get_active_company();
        if (!$company_id) {
            return $this->errorResponse('Active company not found.', 400);
        }

        try {
            $folder = Folder::create([
                'name' => $request['name'],
                'parent_id' => $request['parent_id'] ?? null,
                'company_id' => $company_id,
                'created_by' => current_user()->id
            ]);

            $this->syncPermissions($folder->id, $request->input('permissions', []));

            return $this->successResponse('Folder created successfully!', $folder);
        } catch (\Exception $e) {
            return $this->errorResponse('There was an error creating the folder.', 500, $e);
        }
    }

    /**
     * Show edit form for a folder.
     */
    public function edit(Folder $folder)
    {
        return view('app.folder.update', [
            'title' => "Edit Folder",
            'assignedPermissions' => $this->getFolderPermissions($folder->id),
            'roleArr' => CompanyRole::whereNot('role_name', 'Super Admin')->where('company_id', get_active_company())->get(),
            'folder' => $folder
        ]);
    }

    /**
     * Update a folder.
     */
    public function update(FolderRequest $request, $id)
    {
        $folder = Folder::findOrFail($id);
        $folder->update([
            'name' => $request->input('name'),
            'updated_by' => current_user()->id
        ]);

        $this->syncPermissions($id, $request->input('permissions', []));

        return $this->successResponse('Folder updated successfully!', $folder);
    }

    /**
     * Delete one or multiple folders.
     */
    public function deleteFolder(Request $request)
    {
        try {
            Folder::whereIn('id', (array) $request->folder_ids)->delete();
            return $this->successResponse('Folder deleted successfully.');
        } catch (\Exception $e) {
            return $this->errorResponse('There was an error deleting the folder.', 500, $e);
        }
    }

    /**
     * Get the folder structure for file manager.
     */
    public function fileManager()
    {
        $defaultAccess = current_user()->is_master_admin() || current_user()->is_super_admin();

        $folders = Folder::where('company_id', get_active_company())
            ->whereNull('parent_id')
            ->with(['subfolders', 'files'])
            ->get();

        // Fetch root-level files (files without a folder)
        $files = File::where('company_id', get_active_company())
            ->whereNull('folder_id')
            ->get();

        // Build hierarchical structure
        $fileTree = $this->buildFileTree($folders, $defaultAccess);

        // Merge root-level files into the structured tree
        foreach ($files as $file) {
            if($file->hasAccess() ){
            $fileTree[] = [
                'id' => $file->id,
                'parentId' =>null,
                'name' => $file->name,
                'isDirectory' => false,
                'permissions' => $this->formatPermissions($file, $defaultAccess, false),
            ];
        }
        }

        return response()->json($fileTree);
    }

    /**
     * Recursively build folder structure with permissions.
     */
    private function buildFileTree($folders, $defaultAccess)
    {
        return $folders->filter(fn($folder) => $folder->has_access() || $this->hasChildAccess($folder))
            ->map(fn($folder) => [
                'id' => $folder->id,
                'parentId' => $folder->parent_id,
                'name' => $folder->name,
                'isDirectory' => true,
                'permissions' => $this->formatPermissions($folder, $defaultAccess),
                'items' => array_merge(
                    $this->buildFileTree($folder->subfolders, $defaultAccess),
                    $this->getPermittedFiles($folder, $defaultAccess)
                ),
            ])
            ->values()
            ->all();
    }

    /**
     * Check if any child folder has access.
     */
    private function hasChildAccess($folder)
    {
        return $folder->subfolders->contains(fn($sub) => $sub->has_access() || $this->hasChildAccess($sub));
    }

    /**
     * Get files with access in a given folder.
     */
    private function getPermittedFiles($folder, $defaultAccess)
    {
        return $folder->files->filter(fn($file) => $file->hasAccess())
            ->map(fn($file) => [
                'id' => $file->id,
                'name' => $file->name,
                'isDirectory' => false,
                'permissions' => $this->formatPermissions($file, $defaultAccess, false),
            ])
            ->values()
            ->all();
    }

    /**
     * Format permissions with default values.
     */
    private function formatPermissions($model, $defaultAccess, $isFolder = true)
    {
        $permissions = $model->getPermissions();
        if ($isFolder) {
            return [
                'create' => $permissions->can_create ?? $defaultAccess,
                'update' => $permissions->can_update ?? $defaultAccess,
                'delete' => $permissions->can_delete ?? $defaultAccess,
            ];
        } else {
            return [
                'download' => $permissions->can_download ?? $defaultAccess,
                'update' => $permissions->can_update ?? $defaultAccess,
                'delete' => $permissions->can_delete ?? $defaultAccess,
            ];
        }
    }

    /**
     * Get assigned folder permissions.
     */
    private function getFolderPermissions($folderId)
    {
        return RoleFolderPermission::where('folder_id', $folderId)
            ->get()
            ->groupBy('company_role_id')
            ->map(fn($permissions) => [
                'can_view' => $permissions->contains('can_view', true),
                'can_create' => $permissions->contains('can_create', true),
                'can_update' => $permissions->contains('can_update', true),
                'can_delete' => $permissions->contains('can_delete', true),
            ])
            ->toArray();
    }

    /**
     * Sync folder permissions.
     */
    private function syncPermissions($folderId, array $permissions)
    {
        RoleFolderPermission::where('folder_id', $folderId)->delete();

        $rolePermissions = collect($permissions)
            ->map(fn($permissionArray, $roleId) => [
                'company_role_id' => $roleId,
                'folder_id' => $folderId,
                'can_view' => in_array('can_view', $permissionArray),
                'can_create' => in_array('can_create', $permissionArray),
                'can_update' => in_array('can_update', $permissionArray),
                'can_delete' => in_array('can_delete', $permissionArray),
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->values()
            ->all();

        if (!empty($rolePermissions)) {
            RoleFolderPermission::insert($rolePermissions);
        }
    }
}
