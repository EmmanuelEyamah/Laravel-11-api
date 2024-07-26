<?php

namespace App\Http\Controllers\Api;

use App\Helper\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function getAllUsers()
    {
        try {
            $user = Auth::user();

            // Check if the authenticated user is an admin
            if (!$user || !$user->is_admin) {
                return ResponseHelper::error(message: 'Unauthorized access.', statusCode: 403);
            }

            // Retrieve all non-admin users
            $users = User::where('is_admin', false)->get();

            return ResponseHelper::success(message: 'Users retrieved successfully!', data: $users, statusCode: 200);
        } catch (Exception $e) {
            Log::error('Unable to Fetch Users: ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to fetch users! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }

        /**
     * Get a specific user by ID (Admin Only)
     * @param int $id
     * @return JSONResponse
     */
    public function getUserById($id)
    {
        try {
            $adminUser = Auth::user();

            // Check if the authenticated user is an admin
            if (!$adminUser || !$adminUser->is_admin) {
                return ResponseHelper::error(message: 'Unauthorized access.', statusCode: 403);
            }

            // Retrieve the user by ID
            $user = User::find($id);

            if (!$user) {
                return ResponseHelper::error(message: 'User not found.', statusCode: 404);
            }

            return ResponseHelper::success(message: 'User retrieved successfully!', data: $user, statusCode: 200);
        } catch (Exception $e) {
            Log::error('Unable to Fetch User: ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to fetch user! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }

        /**
     * Suspend a user by ID (Admin Only)
     * @param int $id
     * @return JSONResponse
     */
    public function suspendUser($id)
    {
        try {
            $adminUser = Auth::user();

            // Check if the authenticated user is an admin
            if (!$adminUser || !$adminUser->is_admin) {
                return ResponseHelper::error(message: 'Unauthorized access.', statusCode: 403);
            }

            // Retrieve the user by ID
            $user = User::find($id);

            if (!$user) {
                return ResponseHelper::error(message: 'User not found.', statusCode: 404);
            }

             // Ensure the admin cannot suspend themselves
            if ($adminUser->id === $user->id) {
                return ResponseHelper::error(message: 'You cannot suspend your own account.', statusCode: 403);
            }

            // Suspend the user
            $user->is_suspended = true;
            $user->save();

            return ResponseHelper::success(message: 'User suspended successfully!', data: $user, statusCode: 200);
        } catch (Exception $e) {
            Log::error('Unable to Suspend User: ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
            return ResponseHelper::error(message: 'Unable to suspend user! Please try again.' . $e->getMessage(), statusCode: 500);
        }
    }

    /**
     * Delete a user by ID (Admin Only)
     * @param int $id
     * @return JSONResponse
     */
    public function deleteUser($id)
{
    try {
        $adminUser = Auth::user();

        // Check if the authenticated user is an admin
        if (!$adminUser || !$adminUser->is_admin) {
            return ResponseHelper::error(message: 'Unauthorized access.', statusCode: 403);
        }

        // Retrieve the user by ID
        $user = User::find($id);

        if (!$user) {
            return ResponseHelper::error(message: 'User not found.', statusCode: 404);
        }

        // Ensure the admin cannot delete themselves
        if ($adminUser->id === $user->id) {
            return ResponseHelper::error(message: 'You cannot delete your own account.', statusCode: 403);
        }

        // Delete the user
        $user->delete();

        return ResponseHelper::success(message: 'User deleted successfully!', statusCode: 200);
    } catch (Exception $e) {
        Log::error('Unable to Delete User: ' . $e->getMessage() . ' - Line no. ' . $e->getLine());
        return ResponseHelper::error(message: 'Unable to delete user! Please try again.' . $e->getMessage(), statusCode: 500);
    }
}


}
