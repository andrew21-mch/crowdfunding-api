<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Campaign;
use App\Models\Donation;
use Illuminate\Support\Facades\Cache;

class CampaignController extends ApiBaseController
{
    public function createCampaign(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'goal_amount' => 'required|numeric|min:0',
        ]);

        // Create the campaign
        $campaign = Campaign::create([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'goal_amount' => $validatedData['goal_amount'],
            'current_amount' => 0,
            'user_id' => auth()->user()->id,
        ]);

        return $this->successResponse('Campaign created successfully', ['campaign' => $campaign], Response::HTTP_CREATED);
    }

    public function getAllCampaigns()
    {
        $campaigns = Campaign::with('createdBy')->get();

        return $this->successResponse('All campaigns retrieved successfully', ['campaigns' => $campaigns]);
    }

    public function getCampaign($campaignId)
    {
        try {
            $campaign = Campaign::with('createdBy', 'donations')->findOrFail($campaignId);

            // Perform any additional validation checks here
            // For example, you can check if the campaign is active, etc.

            return $this->successResponse('Campaign retrieved successfully', ['campaign' => $campaign]);
        } catch (\Exception $e) {
            // Handle the case where the campaign is not found
            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return $this->errorResponse('Campaign not found', null, Response::HTTP_NOT_FOUND);
            }

            // Handle any other exceptions or validation errors
            return $this->errorResponse($e->getMessage(), null, Response::HTTP_BAD_REQUEST);
        }
    }

    public function updateCampaign(Request $request, $campaignId)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'title' => 'required',
            'description' => 'required',
            'goal_amount' => 'required|numeric|min:0',
        ]);

        $campaign = Campaign::findOrFail($campaignId);

        // Update the campaign
        $campaign->update([
            'title' => $validatedData['title'],
            'description' => $validatedData['description'],
            'goal_amount' => $validatedData['goal_amount'],
        ]);

        return $this->successResponse('Campaign updated successfully', ['campaign' => $campaign]);
    }

    public function deleteCampaign($campaignId)
    {
        $campaign = Campaign::findOrFail($campaignId);

        // Delete the campaign
        $campaign->delete();

        return $this->successResponse('Campaign deleted successfully', null);
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
            'user_id' => auth()->user()->id ?? null,
            'campaign_id' => $campaign->id,
        ]);

        // Update the current amount of the campaign
        $campaign->current_amount += $donation->amount;
        $campaign->save();

        return $this->successResponse('Donation made successfully', ['donation' => $donation], Response::HTTP_CREATED);
    }

    public function searchCampaign(Request $request)
{
    // Get the search term from the request
    $searchTerm = $request->query;

    $searchTerm = $request->query;
    $searchTerm = implode('', $searchTerm->all());

    // Check if the search results are cached
    $campaigns = Cache::get('campaigns.' . $searchTerm);

    // If the search results are not cached, perform the search and cache the results
    if ($campaigns === null) {
        $campaigns = Campaign::with('createdBy');

        $titleFilter = $request->query->get('title');
        $descriptionFilter = $request->query->get('description');

        if ($titleFilter !== null) {
            $campaigns = $campaigns->where('title', 'like', '%' . $titleFilter . '%');
        }

        if ($descriptionFilter !== null) {
            $campaigns = $campaigns->where('description', 'like', '%' . $descriptionFilter . '%');
        }

        $campaigns = $campaigns->get();

        Cache::put('campaigns.' . $searchTerm, $campaigns, 60);
    }

    return $this->successResponse('Campaigns search results', ['campaigns' => $campaigns]);
}

}