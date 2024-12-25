<?php

namespace App\Http\Controllers\Api\Auction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Auction;
use Illuminate\Support\Facades\Validator;
use App\Models\AuctionState;
use App\Models\BidHistory;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Response;

class AuctionController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api')->except(['index', 'show']);
    }

    public function index()
    {
        $auctions = Auction::all();
        return response()->json(['auctions' => $auctions]);
    }

    public function show($id)
    {
        $auction = Auction::find($id);
        if (!$auction) {
            return response()->json(['message' => 'Auction not found'], 404);
        }
        return response()->json(['auction' => $auction]);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'auction_date' => 'required|date',
            'bid_starting_price' => 'required|numeric',
            'team_balance' => 'required|numeric',
            'min_bid_increase_amount' => 'required|numeric',
            'min_player_amount' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $auction = new Auction($request->all());
        $auction->hostid = auth()->id();
        $auction->save();

        return response()->json(['auction' => $auction], 201);
    }

    public function update(Request $request, $id)
    {
        $auction = Auction::find($id);
        if (!$auction) {
            return response()->json(['message' => 'Auction not found'], 404);
        }

        if ($auction->hostid !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string',
            'description' => 'required|string',
            'auction_date' => 'required|date',
            'bid_starting_price' => 'required|numeric',
            'team_balance' => 'required|numeric',
            'min_bid_increase_amount' => 'required|numeric',
            'min_player_amount' => 'required|numeric'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $auction->update($request->all());
        return response()->json(['auction' => $auction]);
    }

    public function destroy($id)
    {
        $auction = Auction::find($id);
        if (!$auction) {
            return response()->json(['message' => 'Auction not found'], 404);
        }

        if ($auction->hostid !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $auction->delete();
        return response()->json(['message' => 'Auction deleted successfully']);
    }

    public function startAuction(Auction $auction)
    {
        if ($auction->hostid !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $firstPlayer = Player::where('aid', $auction->aid)
            ->where('status', 'Queued')
            ->orderBy('id')
            ->first();

        if (!$firstPlayer) {
            return response()->json(['message' => 'No players available to start auction'], Response::HTTP_BAD_REQUEST);
        }

        $auctionState = AuctionState::updateOrCreate(
            ['aid' => $auction->aid],
            [
                'current_player' => $firstPlayer->id,
                'current_bid' => 0,
                'bidder_team_id' => null
            ]
        );

        return response()->json([
            'message' => 'Auction started',
            'auction_state' => [
                'current_player' => [
                    'id' => $firstPlayer->id,
                    'name' => $firstPlayer->user->name,
                    'position' => $firstPlayer->position,
                    'category' => $firstPlayer->category,
                    'status' => $firstPlayer->status
                ],
                'current_bid' => 0,
                'bidder_team' => null
            ]
        ]);
    }

    public function nextPlayer(Auction $auction)
    {
        if ($auction->hostid !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], Response::HTTP_FORBIDDEN);
        }

        $auctionState = AuctionState::where('aid', $auction->aid)->firstOrFail();
        $currentPlayer = Player::with('user')->findOrFail($auctionState->current_player);

        DB::beginTransaction();
        try {
            // Update current player status
            if ($auctionState->bidder_team_id) {
                // Player is sold
                $bidderTeam = Team::findOrFail($auctionState->bidder_team_id);
                $currentPlayer->update([
                    'status' => 'Sold',
                    'tid' => $auctionState->bidder_team_id,
                    'cost' => $auctionState->current_bid
                ]);

                // Update team's cost
                $bidderTeam->update([
                    'cost' => $bidderTeam->cost + $auctionState->current_bid
                ]);
            } else {
                // Player is unsold
                $currentPlayer->update([
                    'status' => 'Unsold',
                    'cost' => null
                ]);
            }

            // Find next player
            $nextPlayer = Player::where('aid', $auction->aid)
                ->where('status', 'Queued')
                ->orderBy('id')
                ->first();

            if (!$nextPlayer) {
                // Check for unsold players if no queued players
                $nextPlayer = Player::where('aid', $auction->aid)
                    ->where('status', 'Unsold')
                    ->orderBy('id')
                    ->first();
            }

            if (!$nextPlayer) {
                DB::commit();
                return response()->json([
                    'message' => 'Auction completed',
                    'status' => 'completed'
                ]);
            }

            // Update auction state
            $auctionState->update([
                'current_player' => $nextPlayer->id,
                'current_bid' => 0,
                'bidder_team_id' => null
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Moved to next player',
                'auction_state' => [
                    'current_player' => [
                        'id' => $nextPlayer->id,
                        'name' => $nextPlayer->user->name,
                        'position' => $nextPlayer->position,
                        'category' => $nextPlayer->category,
                        'status' => $nextPlayer->status
                    ],
                    'current_bid' => 0,
                    'bidder_team' => null
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    public function placeBid(Request $request, Auction $auction)
    {
        $validated = $request->validate([
            'add' => 'nullable|integer|min:0',
            'fix' => 'nullable|integer|min:0'
        ]);

        // Get team of authenticated user for this auction
        $team = Team::where('manager_id', auth()->id())
            ->where('aid', $auction->aid)
            ->where('isAccepted', true)
            ->first();

        if (!$team) {
            return response()->json([
                'message' => 'No accepted team found for this auction'
            ], Response::HTTP_FORBIDDEN);
        }

        $auctionState = AuctionState::where('aid', $auction->aid)->firstOrFail();
        $currentPlayer = Player::with('user')->findOrFail($auctionState->current_player);

        // Calculate max possible bid for the team
        $playerCount = Player::where('tid', $team->tid)->where('status', 'Sold')->count();
        $remainingPlayers = $auction->min_player_amount - $playerCount;
        $maxPossibleBid = ($auction->team_balance - $team->cost) - ($remainingPlayers * $auction->bid_starting_price);

        // Determine new bid amount
        $newBidAmount = 0;
        if ($auctionState->current_bid === 0) {
            // First bid
            $newBidAmount = $auction->bid_starting_price;
        } else {
            // Handle add or fix bid
            if (isset($validated['add'])) {
                $newBidAmount = $auctionState->current_bid + $validated['add'];
            } elseif (isset($validated['fix'])) {
                // Only allow fix bid if it's higher than current bid
                if ($validated['fix'] <= $auctionState->current_bid) {
                    return response()->json([
                        'message' => 'Fixed bid must be higher than current bid',
                        'current_bid' => $auctionState->current_bid
                    ], Response::HTTP_BAD_REQUEST);
                }
                $newBidAmount = $validated['fix'];
            } else {
                // Default increment if no add or fix specified
                $newBidAmount = $auctionState->current_bid + $auction->min_bid_increase_amount;
            }
        }

        // Validate bid amount
        if ($newBidAmount > $maxPossibleBid) {
            return response()->json([
                'message' => 'Bid amount exceeds maximum possible bid',
                'current_bid' => $auctionState->current_bid,
                'max_possible_bid' => $maxPossibleBid,
                'team_balance' => $auction->team_balance - $team->cost,
                'min_required_balance' => $remainingPlayers * $auction->bid_starting_price
            ], Response::HTTP_BAD_REQUEST);
        }

        // Ensure minimum bid increase
        if (
            $auctionState->current_bid > 0 &&
            $newBidAmount < $auctionState->current_bid + $auction->min_bid_increase_amount
        ) {
            return response()->json([
                'message' => 'Bid increase must be at least ' . $auction->min_bid_increase_amount,
                'current_bid' => $auctionState->current_bid,
                'minimum_next_bid' => $auctionState->current_bid + $auction->min_bid_increase_amount
            ], Response::HTTP_BAD_REQUEST);
        }

        DB::beginTransaction();
        try {
            $auctionState->update([
                'current_bid' => $newBidAmount,
                'bidder_team_id' => $team->tid
            ]);

            BidHistory::create([
                'aid' => $auction->aid,
                'player_id' => $currentPlayer->id,
                'bidder_team_id' => $team->tid,
                'bid_amount' => $newBidAmount
            ]);

            DB::commit();
            return response()->json([
                'message' => 'Bid placed successfully',
                'auction_state' => [
                    'current_player' => [
                        'id' => $currentPlayer->id,
                        'name' => $currentPlayer->user->name,
                        'position' => $currentPlayer->position,
                        'category' => $currentPlayer->category,
                        'status' => $currentPlayer->status
                    ],
                    'current_bid' => $newBidAmount,
                    'bidder_team' => [
                        'id' => $team->tid,
                        'name' => $team->name
                    ],
                    'team_balance_remaining' => $maxPossibleBid - $newBidAmount
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Error processing bid'], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    public function getAuctionState(Auction $auction)
    {
        $auctionState = AuctionState::where('aid', $auction->aid)
            ->with(['currentPlayer.user', 'bidderTeam'])
            ->firstOrFail();

        $currentPlayer = $auctionState->currentPlayer;
        $bidderTeam = $auctionState->bidderTeam;

        return response()->json([
            'auction_state' => [
                'current_player' => $currentPlayer ? [
                    'id' => $currentPlayer->id,
                    'name' => $currentPlayer->user->name,
                    'position' => $currentPlayer->position,
                    'category' => $currentPlayer->category,
                    'status' => $currentPlayer->status
                ] : null,
                'current_bid' => $auctionState->current_bid,
                'bidder_team' => $bidderTeam ? [
                    'id' => $bidderTeam->tid,
                    'name' => $bidderTeam->name
                ] : null
            ]
        ]);
    }
}
