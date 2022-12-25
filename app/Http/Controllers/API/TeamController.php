<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;

class TeamController extends Controller
{
    public function create(CreateTeamRequest $request)
    {
        try {
            // Upload icon
            if($request->hasFile('icon'))
            {
                $path = $request->file('icon')->store('public/icons');
            }

            // Create data team
            $team = Team::create([
                'name'          => $request->name,
                'icon'          => $path,
                'company_id'    => $request->company_id
            ]);

            // condition company request null
            if(!$team)
            {
                throw new Exception('Team not created');
            }

            return ResponseFormatter::success($team, 'Team Created');
            
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateTeamRequest $request, $id)
    {
        try {
            // Get team
            $team = Team::find($id);

            // Check if company exists
            if(!$team)
            {
                throw new Exception('Team not found');
            }

            // Upload logo
            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            // Update Company
            Team::where('id', $id)->update($request->all());
            
            return ResponseFormatter::success($team, 'Team updated');
            
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
