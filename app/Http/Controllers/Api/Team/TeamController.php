<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $auctionId = $request->query('auction_id');

        if ($auctionId) {
            $teams = Team::where('aid', $auctionId)->get();
        } else {
            $teams = Team::all();
        }

        return response()->json($teams);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'aid' => 'required|exists:auctions,aid',
            'logo_url' => 'nullable|url'
        ]);

        $userId = auth()->id();

        // Ensure one user can register for one team in a single auction
        $existingTeam = Team::where('manager_id', $userId)
            ->where('aid', $validated['aid'])
            ->exists();

        if ($existingTeam) {
            return response()->json(['message' => 'You can only register one team per auction'], Response::HTTP_CONFLICT);
        }

        $validated['manager_id'] = $userId;
        $validated['isAccepted'] = false;

        $team = Team::create($validated);
        return response()->json($team, Response::HTTP_CREATED);
    }

    public function acceptTeam(Team $team)
    {
        $userId = auth()->id();

        // Check if the authenticated user is the auction host
        $auction = Auction::findOrFail($team->aid);
        if ($auction->hostid !== $userId) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $team->update(['isAccepted' => !$team->isAccepted]); // Toggle isAccepted
        return response()->json($team);
    }

    public function show(Team $team)
    {
        return response()->json($team);
    }

    public function update(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'string',
            'logo_url' => 'nullable|url',
        ]);

        // Check if the authenticated user is the team manager
        if (auth()->id() !== $team->manager_id) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $team->update($validated);
        return response()->json($team);
    }

    public function destroy(Team $team)
    {
        // Check if the authenticated user is the team manager
        if (auth()->id() !== $team->manager_id) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $team->delete();
        return response()->json(['message' => 'Team deleted successfully'], Response::HTTP_OK);
    }
}
