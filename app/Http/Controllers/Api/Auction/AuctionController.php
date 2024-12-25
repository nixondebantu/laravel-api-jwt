<?php

namespace App\Http\Controllers\Api\Auction;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Auction;
use Illuminate\Support\Facades\Validator;

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
}
