<?php

namespace App\Http\Controllers\Api\Player;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\Auction;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PlayerController extends Controller
{
    public function register(Request $request)
    {
        $validated = $request->validate([
            'position' => 'required|string',
            'aid' => 'required|exists:auctions,aid',
        ]);

        $user = auth()->user();

        // Check if user is already registered in this auction
        $existingPlayer = Player::where('aid', $validated['aid'])
            ->where('uid', $user->id)
            ->first();

        if ($existingPlayer) {
            return response()->json([
                'message' => 'You are already registered as a player in this auction'
            ], Response::HTTP_CONFLICT);
        }

        $player = Player::create([
            'aid' => $validated['aid'],
            'uid' => $user->id,
            'position' => $validated['position'],
            'category' => 'Unknown',
            'status' => 'Queued'
        ]);

        return response()->json($player, Response::HTTP_CREATED);
    }

    public function update(Request $request, Player $player)
    {
        $validated = $request->validate([
            'position' => 'required|string',
        ]);

        // Check if the authenticated user owns this player registration
        if ($player->uid !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized to update this player'
            ], Response::HTTP_FORBIDDEN);
        }

        // Check if player is not sold
        if ($player->status === 'Sold') {
            return response()->json([
                'message' => 'Cannot update position after being sold'
            ], Response::HTTP_FORBIDDEN);
        }

        $player->update($validated);
        return response()->json($player);
    }

    public function destroy(Player $player)
    {
        // Check if the authenticated user owns this player registration
        if ($player->uid !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized to delete this player'
            ], Response::HTTP_FORBIDDEN);
        }

        // Check if player is not sold
        if ($player->status === 'Sold') {
            return response()->json([
                'message' => 'Cannot delete registration after being sold'
            ], Response::HTTP_FORBIDDEN);
        }

        $player->delete();
        return response()->json([
            'message' => 'Player registration deleted successfully'
        ]);
    }

    public function assignCategory(Request $request, Player $player)
    {
        $validated = $request->validate([
            'category' => 'required|string',
        ]);

        // Check if the authenticated user is the auction host
        $auction = Auction::findOrFail($player->aid);
        if ($auction->hostid !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized to assign category'
            ], Response::HTTP_FORBIDDEN);
        }

        // Check if player is not sold
        if ($player->status === 'Sold') {
            return response()->json([
                'message' => 'Cannot assign category after player is sold'
            ], Response::HTTP_FORBIDDEN);
        }

        $player->update(['category' => $validated['category']]);
        return response()->json($player);
    }

    public function index(Request $request)
    {
        $query = Player::query();

        // Filter by auction
        $auctionId = $request->query('auction_id');
        if ($auctionId) {
            $query->where('aid', $auctionId);
        }

        // Filter by status
        $status = $request->query('status');
        if ($status && in_array($status, ['Queued', 'Sold', 'Unsold'])) {
            $query->where('status', $status);
        }

        $players = $query->with(['user:id,name,email', 'team:tid,name'])
            ->get();

        return response()->json($players);
    }

    public function show(Player $player)
    {
        return response()->json(
            $player->load(['user:id,name,email', 'team:tid,name'])
        );
    }
}
