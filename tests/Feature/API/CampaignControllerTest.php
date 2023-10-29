<?php
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\Response;
use Illuminate\Testing\Fluent\AssertableJson;
use Tests\TestCase;
use App\Models\User;
use App\Models\Campaign;

class CampaignControllerTest extends TestCase
{
    use RefreshDatabase;
    use WithFaker;
    protected function authenticate()
    {
        $user = User::factory()->create();
        $token = $user->createToken('test_token')->plainTextToken;
        return $token;
    }

    public function test_create_campaign()
    {
        $token = $this->authenticate();

        $response = $this->post('/api/campaigns', [
            'title' => 'Test Campaign',
            'description' => 'This is a test campaign.',
            'goal_amount' => 1000,
        ], $this->getHeaders($token));

        $response->assertStatus(Response::HTTP_CREATED)
            ->assertJsonStructure(['data' => ['campaign']])
            ->assertJson([
                'data' => [
                    'campaign' => [
                        'title' => 'Test Campaign',
                        'description' => 'This is a test campaign.',
                        'goal_amount' => 1000,
                    ]
                ]
            ])
            ->assertJsonFragment(['title' => 'Test Campaign'])
            ->assertJsonFragment(['description' => 'This is a test campaign.'])
            ->assertJsonFragment(['goal_amount' => 1000]);

        $campaign = $response->json('data.campaign');
        $this->assertDatabaseHas('campaigns', ['id' => $campaign['id']]);
    }

    public function test_get_all_campaigns()
    {
        // Create some campaign records in the database
        $campaigns = Campaign::factory()->count(3)->create();

        $response = $this->get('/api/campaigns');

        $response->assertOk()
            ->assertJsonStructure(['data' => ['campaigns']])
            ->assertJsonCount(3, 'data.campaigns');
        $campaignIds = $campaigns->pluck('id')->all();
        $this->assertDatabaseHas('campaigns', ['id' => $campaignIds]);
    }

    public function test_get_campaign()
    {
        $token = $this->authenticate();

        // Create a campaign record in the database
        $campaign = Campaign::factory()->create();

        $response = $this->get('/api/campaigns/' . $campaign->id, $this->getHeaders($token));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['campaign']])
            ->assertJson([
                'data' => [
                    'campaign' => [
                        'id' => $campaign->id,
                        'title' => $campaign->title,
                        'description' => $campaign->description,
                        'goal_amount' => $campaign->goal_amount,
                    ]
                ]
            ]);

        $this->assertDatabaseHas('campaigns', ['id' => $campaign->id]);
    }

    public function test_update_campaign()
    {
        $token = $this->authenticate();

        // Create a campaign record in the database
        $campaign = Campaign::factory()->create();

        $updatedData = [
            'title' => 'Updated Campaign',
            'description' => 'This is an updated campaign.',
            'goal_amount' => 2000,
        ];

        $response = $this->put('/api/campaigns/' . $campaign->id, $updatedData, $this->getHeaders($token));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['campaign']])
            ->assertJson([
                'data' => [
                    'campaign' => $updatedData
                ]
            ]);

        $this->assertDatabaseHas('campaigns', $updatedData);
    }

    public function test_delete_campaign()
    {
        $token = $this->authenticate();

        // Create a campaign record in the database
        $campaign = Campaign::factory()->create();

        $response = $this->delete('/api/campaigns/' . $campaign->id, [], $this->getHeaders($token));

        $response->assertOk();

        $this->assertDatabaseMissing('campaigns', ['id' => $campaign->id]);
    }

    public function test_make_donation()
    {
        $token = $this->authenticate();

        // Create a campaign record in the database
        $campaign = Campaign::factory()->create();

        $initialAmount = $campaign->current_amount;

        $donationData = [
            'amount' => 100,
        ];

        $response = $this->post('/api/campaigns/' . $campaign->id . '/donate', $donationData, $this->getHeaders($token));

        $response->assertCreated()
            ->assertJsonStructure(['data' => ['donation']])
            ->assertJson([
                'data' => [
                    'donation' => [
                        'amount' => $donationData['amount'],
                    ]
                ]
            ]);

        $this->assertDatabaseHas('donations', [
            'campaign_id' => $campaign->id,
            'amount' => $donationData['amount'],
        ]);

        $campaign = $campaign->fresh(); // Refresh the campaign model from the database
        $newAmount = $initialAmount + $donationData['amount'];
        $this->assertEquals($newAmount, $campaign->current_amount);
    }

    public function test_search_campaign()
    {
        $token = $this->authenticate();

        // Create three campaign records in the database
        $campaigns = Campaign::factory()->count(3)->create();

        $searchQuery = 'nothing';

        $url = '/api/campaigns/search-campaigns/get?query=' . urlencode($searchQuery);

        $response = $this->get($url, $this->getHeaders($token));

        $response->assertOk()
            ->assertJsonStructure(['data' => ['campaigns']]);
    }
    private function getHeaders($token = null)
    {
        $headers = [];

        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        return $headers;
    }


}