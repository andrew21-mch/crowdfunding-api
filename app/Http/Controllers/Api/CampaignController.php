<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Campaign;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function createCampaign(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'target_amount' => 'required|numeric|min:0',
        ]);

        // Create the campaign
        $campaign = Campaign::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'target_amount' => $validatedData['target_amount'],
            'current_amount' => 0,
            'user_id' => auth()->user()->id,
        ]);

        return response()->json(['campaign' => $campaign], 201);
    }

    public function getAllCampaigns()
    {
        $campaigns = Campaign::all();

        return response()->json(['campaigns' => $campaigns]);
    }

    public function getCampaign($campaignId)
    {
        $campaign = Campaign::findOrFail($campaignId);

        return response()->json(['campaign' => $campaign]);
    }

    public function updateCampaign(Request $request, $campaignId)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'target_amount' => 'required|numeric|min:0',
        ]);

        $campaign = Campaign::findOrFail($campaignId);

        // Update the campaign
        $campaign->update([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'target_amount' => $validatedData['target_amount'],
        ]);

        return response()->json(['campaign' => $campaign]);
    }

    public function deleteCampaign($campaignId)
    {
        $campaign = Campaign::findOrFail($campaignId);

        // Delete the campaign
        $campaign->delete();

        return response()->json(['message' => 'Campaign deleted successfully']);
    }

    public function makeDonation(Request $request, $campaignId)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:0',
        ]);

        $campaign = Campaign::findOrFail($campaignId);

        // Create the donation
        $donation = Donation::create([
            'amount' => $validatedData['amount'],
            'user_id' => auth()->user()->id,
            'campaign_id' => $campaign->id,
        ]);

        // Update the current amount of the campaign
        $campaign->current_amount += $donation->amount;
        $campaign->save();

        return response()->json(['donation' => $donation], 201);
    }
}