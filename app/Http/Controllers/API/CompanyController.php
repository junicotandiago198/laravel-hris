<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\User;
use App\Models\Company;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;

class CompanyController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        // powerhuman.com/api/company?id=1
        if($id)
        {
            $company = Company::with(['users'])->find($id);

            if($company)
            {
                return ResponseFormatter::success($company, 'Company found');
            }

            return ResponseFormatter::error('Company not found', 404);
        }

        // powerhuman.com/api/company
        $companies = Company::with(['users']);

        // powerhuman.com/api/company?name=Kunde
        if($name) {
            $companies->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $companies->paginate($limit),
            'Companies found'
        );
    }

    public function create(CreateCompanyRequest $request)
    {
        try {
            // Upload logo
            if($request->hasFile('logo'))
            {
                $path = $request->file('logo')->store('public/logos');
            }

            // Create data company
            $company = Company::create([
                'name'  => $request->name,
                'logo'  => $path,
            ]);

            // condition company request null
            if(!$company)
            {
                throw new Exception('Company not created');
            }

            // Attach company to user
            $user = User::find(Auth::id());
            $user->companies()->attach($company->id);

            // Load users to company
            $company->load('users');

            return ResponseFormatter::success($company, 'Company Created');
            
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateCompanyRequest $request, $id)
    {
        try {
            // Get Company
            $company = Company::find($id);

            // Check if company exists
            if(!$company)
            {
                throw new Exception('Company not found');
            }

            // Upload logo
            if ($request->hasFile('logo')) {
                $path = $request->file('logo')->store('public/logos');
            }

            // Update Company
            Company::where('id', $id)->update($request->all());
            
            return ResponseFormatter::success($company, 'Company updated');
            
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
