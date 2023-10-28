<?php

namespace App\Http\Controllers\API;

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
            'goal_amount' => $validatedData['target_amount'],
            'current_amount' => 0,
            'user_id' => auth()->user()->id,
        ]);

        return response()->json(['campaign' => $campaign], 201);
    }

    public function getAllCampaigns()
    {
        $campaigns = Campaign::with('createdBy')->get();

        return response()->json(['campaigns' => $campaigns]);
    }

    public function getCampaign($campaignId)
{
    try {
        $campaign = Campaign::with('createdBy')->findOrFail($campaignId);

        // Perform any additional validation checks here
        // For example, you can check if the campaign is active, etc.

        return response()->json(['campaign' => $campaign]);
    } catch (\Exception $e) {
        // Handle the case where the campaign is not found
        if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
            return response()->json(['error' => 'Campaign not found'], 404);
        }

        // Handle any other exceptions or validation errors
        return response()->json(['error' => $e->getMessage()], 400);
    }
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