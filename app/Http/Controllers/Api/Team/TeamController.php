<?php

namespace App\Http\Controllers\Api\Team;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\Auction;
use App\Models\Player;
use App\Models\BidHistory;
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

    public function getTeamPlayers(Team $team)
    {
        // Check if user is authorized to view this team's players
        if ($team->manager_id !== auth()->id() && !$team->auction->hostid !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $players = Player::where('tid', $team->tid)
            ->with(['user:id,name,email'])
            ->get()
            ->map(function ($player) {
                // Get the maximum bid amount for this player
                $maxBid = BidHistory::where('player_id', $player->id)
                    ->where('bidder_team_id', $player->tid)
                    ->max('bid_amount');

                return [
                    'id' => $player->id,
                    'name' => $player->user->name,
                    'position' => $player->position,
                    'category' => $player->category,
                    'status' => $player->status,
                    'cost' => $maxBid ?? 0  // Use max bid amount or 0 if no bids
                ];
            });

        return response()->json([
            'team' => [
                'id' => $team->tid,
                'name' => $team->name,
                'total_cost' => $team->cost,
                'balance' => $team->auction->team_balance - $team->cost
            ],
            'players' => $players
        ]);
    }
}
