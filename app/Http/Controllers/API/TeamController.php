<?php

namespace App\Http\Controllers\API;

use Exception;
use App\Models\Team;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Helpers\ResponseFormatter;
use App\Http\Requests\CreateTeamRequest;

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
}
