<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Role;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;

class RoleController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $roleQuery = Role::query();

        // Get single data
        if($id)
        {
            $role = $roleQuery->find($id);

            if($role)
            {
                return ResponseFormatter::success($role, 'Role found');
            }

            return ResponseFormatter::error('Role not found', 404);
        }

        // Get multiple data
        $roles = $roleQuery->where('company_id', $request->company_id);

        // powerhuman.com/api/role?name=Kunde
        if($name) {
            $roles->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $roles->paginate($limit),
            'Roles found'
        );
    }

    public function create(CreateRoleRequest $request)
    {
        try {
            // Create data role
            $role = Role::create([
                'name'          => $request->name,
                'company_id'    => $request->company_id
            ]);

            // condition role request null
            if(!$role)
            {
                throw new Exception('Role not created');
            }

            return ResponseFormatter::success($role, 'Role Created');
            
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        try {
            // Get role
            $role = Role::find($id);

            // Check if role exists
            if(!$role)
            {
                throw new Exception('Role not found');
            }

            // Update Role
            $role->update([
                'name'      => $request->name,
                'company_id'   => $request->company_id
            ]);
            
            return ResponseFormatter::success($role, 'Role updated');
            
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Get role
            $role = Role::find($id);

            // TODO: Check if role is owned by user login

            if (!$role) {
                throw new Exception('Role not found');
            }

            // Delete role
            $role->delete();

            return ResponseFormatter::success('Role deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
