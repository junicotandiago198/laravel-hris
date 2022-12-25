<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;

class TeamController extends Controller
{
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        $teamQuery = Team::query();

        // Get single data
        if($id)
        {
            $team = $teamQuery->find($id);

            if($team)
            {
                return ResponseFormatter::success($team, 'Team found');
            }

            return ResponseFormatter::error('Team not found', 404);
        }

        // Get multiple data
        $teams = $teamQuery->where('company_id', $request->company_id);

        // powerhuman.com/api/team?name=Kunde
        if($name) {
            $teams->where('name', 'like', '%' . $name . '%');
        }

        return ResponseFormatter::success(
            $teams->paginate($limit),
            'Teams found'
        );
    }

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
                'team_id'    => $request->team_id
            ]);

            // condition team request null
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

            // Check if team exists
            if(!$team)
            {
                throw new Exception('Team not found');
            }

            // Upload logo
            if ($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            // Update Team
            Team::where('id', $id)->update($request->all());
            
            return ResponseFormatter::success($team, 'Team updated');
            
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
