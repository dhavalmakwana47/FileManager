<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'admin_id'
    ];
    public function admin_user()
    {

        return $this->hasMany(CompanyUserRole::class, 'company_id');
    }
    public function companyRoles()
    {
        return $this->hasMany(CompanyRole::class, 'company_id');
    }
}
