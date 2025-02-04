<?php

use App\Models\Company;

function get_active_company()
{
    return session('active_company');
}

function current_user(){
    return auth()->user();
}
function fetch_company(){
    if (current_user()->is_master_admin()) {
     return Company::all();
    }
    return current_user()->companies;
}