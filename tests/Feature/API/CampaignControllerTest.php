<?php
use Illuminate\Http\Response;
use Tests\TestCase;
use App\Models\User;
use App\Models\Campaign;

class CampaignControllerTest extends TestCase
{
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
            ->assertJson(['data' => ['campaign' => [
                'title' => 'Test Campaign',
                'description' => 'This is a test campaign.',
                'goal_amount' => 1000,
            ]]])
            ->assertJsonFragment(['title' => 'Test Campaign'])
            ->assertJsonFragment(['description' => 'This is a test campaign.'])
            ->assertJsonFragment(['goal_amount' => 1000]);
            
        $campaign = $response->json('data.campaign');
        $this->assertDatabaseHas('campaigns', ['id' => $campaign['id']]);
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