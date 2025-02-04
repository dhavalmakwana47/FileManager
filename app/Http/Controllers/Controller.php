<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function admin_check($user)
    {
        return ($user->is_master_admin() || $user->is_super_admin());
    }
}
