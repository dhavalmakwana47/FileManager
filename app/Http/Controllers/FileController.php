<?php

namespace App\Http\Controllers;

use App\Models\CompanyRole;
use App\Models\File;
use App\Models\RoleFilePermission;
use Illuminate\Http\Request;

class FileController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
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
        $company_id = get_active_company();
        if (!$company_id) {
            return $this->errorResponse('Active company not found.', 400);
        }

        try {
            $fileName = null;

            // Check if a file is uploaded
            if ($request->hasFile('file')) {
                $file = $request->file('file');
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = 'uploads/company_' . $company_id . '/';

                // Store file
                $file->move(public_path($filePath), $fileName);
            }

            // Save folder/file info in the database
            $folder = File::create([
                'name' => $fileName, // Use uploaded file name if no name is provided
                'folder_id' => $request->input('folder_id') ?? null,
                'company_id' => $company_id,
                'file_path' => isset($fileName) ? $filePath . $fileName : null, // Save file path if uploaded
                'created_by' => current_user()->id
            ]);

            $this->syncPermissions($folder->id, $request->input('permissions', []));
            return $this->successResponse('Folder/File created successfully!', $folder);
        } catch (\Exception $e) {
            return $this->errorResponse('There was an error creating the folder/file.', 500, $e);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show(File $file)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(File $file)
    {
        return view('app.folder.updatefile', [
            'title' => "Edit File",
            'assignedPermissions' => $this->getFilePermissions($file->id),
            'roleArr' => CompanyRole::whereNot('role_name', 'Super Admin')->where('company_id', get_active_company())->get(),
            'file' => $file
        ]);
    }
    private function getFilePermissions($fileId)
    {
        return RoleFilePermission::where('file_id', $fileId)
            ->get()
            ->groupBy('company_role_id')
            ->map(fn($permissions) => [
                'can_download' => $permissions->contains('can_download', true),
                'can_view' => $permissions->contains('can_view', true),
                'can_update' => $permissions->contains('can_update', true),
                'can_delete' => $permissions->contains('can_delete', true),
            ])
            ->toArray();
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request,  $id)
    {
        $folder = File::findOrFail($id);
        $folder->update([
            'updated_by' => current_user()->id
        ]);

        $this->syncPermissions($id, $request->input('permissions', []));

        return $this->successResponse('File updated successfully!', $folder);
    }

    private function syncPermissions($fileId, array $permissions)
    {
        RoleFilePermission::where('file_id', $fileId)->delete();

        $rolePermissions = collect($permissions)
            ->map(fn($permissionArray, $roleId) => [
                'company_role_id' => $roleId,
                'file_id' => $fileId,
                'can_view' => in_array('can_view', $permissionArray),
                'can_download' => in_array('can_download', $permissionArray),
                'can_update' => in_array('can_update', $permissionArray),
                'can_delete' => in_array('can_delete', $permissionArray),
                'created_at' => now(),
                'updated_at' => now(),
            ])
            ->values()
            ->all();

        if (!empty($rolePermissions)) {
            RoleFilePermission::insert($rolePermissions);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(File $file)
    {
        //
    }
}
