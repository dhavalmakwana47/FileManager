<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'folder_id',
        'company_id',
        'created_by',
        'updated_by'
    ];

    public function rolePermissions()
    {
        return $this->hasMany(RoleFilePermission::class, 'file_id');
    }

    public function hasAccess()
    {
        if (current_user()->is_master_admin() || current_user()->id == $this->created_by || current_user()->is_super_admin()) {
            return true;
        }
        return $this->rolePermissions()
        ->whereIn('company_role_id', current_user()->companyRoles->pluck('id'))
        ->exists();
    }

    public function getPermissions()
    {
        return $this->rolePermissions()
            ->whereIn('company_role_id', current_user()->companyRoles->pluck('id'))
            ->first(['can_download', 'can_update', 'can_delete']);
    }
}
