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
    private function getHeaders($token = null)
    {
        $headers = [];

        if ($token) {
            $headers['Authorization'] = 'Bearer ' . $token;
        }

        return $headers;
    }
}