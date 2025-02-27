<?php

namespace App\Http\Controllers;

abstract class Controller
{
    public function admin_check($user)
    {
        return ($user->is_master_admin() || $user->is_super_admin());
    }

    /**
     * Standardized success response.
     */
    public function successResponse($message, $data = null)
    {
        return response()->json(['success' => true, 'message' => $message, 'data' => $data]);
    }

    /**
     * Standardized error response.
     */
    public function errorResponse($message, $code = 500, $exception = null)
    {
        \Log::error($message . ($exception ? ' - ' . $exception->getMessage() : ''));
        return response()->json(['success' => false, 'message' => $message], $code);
    }
}
