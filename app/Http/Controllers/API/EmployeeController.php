<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Employee;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateEmployeeRequest;
use App\Http\Requests\UpdateEmployeeRequest;

class EmployeeController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $email = $request->input('email');
        $age = $request->input('age');
        $phone = $request->input('phone');
        $team_id = $request->input('team_id');
        $role_id = $request->input('role_id');
        $limit = $request->input('limit', 10);

        $employeeQuery = Employee::query();

        // Get single data
        if($id)
        {
            $employee = $employeeQuery->with(['team','role'])->find($id);

            if($employee)
            {
                return ResponseFormatter::success($employee, 'Employee found');
            }

            return ResponseFormatter::error('Employee not found', 404);
        }

        // Get multiple data
        $employees = $employeeQuery;

        // powerhuman.com/api/employee?name=Kunde
        if($name) {
            $employees->where('name', 'like', '%' . $name . '%');
        }

        if($email) {
            $employees->where('email', $email);
        }

        if($age) {
            $employees->where('age', $age);
        }

        if($phone) {
            $employees->where('phone', 'like', '%' . $phone . '%');
        }

        if($role_id) {
            $employees->where('role_id', $role_id);
        }

        if($team_id) {
            $employees->where('team_id', $team_id);
        }

        return ResponseFormatter::success(
            $employees->paginate($limit),
            'Employees found'
        );
    }

    public function create(CreateEmployeeRequest $request)
    {
        try {
            // Upload photo
            if($request->hasFile('photo'))
            {
                $path = $request->file('photo')->store('public/photos');
            }

            // Create data employee
            $employee = Employee::create([
                'name'          => $request->name,
                'email'         => $request->email,
                'gender'        => $request->gender,
                'age'           => $request->age,
                'phone'         => $request->phone,
                'photo'         => $path,
                'team_id'       => $request->team_id,
                'role_id'       => $request->role_id
            ]);

            // condition employee request null
            if(!$employee)
            {
                throw new Exception('Employee not created');
            }

            return ResponseFormatter::success($employee, 'Employee Created');
            
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateEmployeeRequest $request, $id)
    {
        try {
            // Get employee
            $employee = Employee::find($id);

            // Check if employee exists
            if(!$employee)
            {
                throw new Exception('Employee not found');
            }

            // Upload logo
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('public/photos');
            }

            // Update Employee
            $employee->update([
                'name'          => $request->name,
                'email'         => $request->email,
                'gender'        => $request->gender,
                'age'           => $request->age,
                'phone'         => $request->phone,
                'photo'         => isset($path) ? $path : $employee->photo,
                'team_id'       => $request->team_id,
                'role_id'       => $request->role_id
            ]);
            
            return ResponseFormatter::success($employee, 'Employee updated');
            
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function destroy($id)
    {
        try {
            // Get employee
            $employee = Employee::find($id);

            // TODO: Check if employee is owned by user login

            if (!$employee) {
                throw new Exception('Employee not found');
            }

            // Delete employee
            $employee->delete();

            return ResponseFormatter::success('Employee deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
