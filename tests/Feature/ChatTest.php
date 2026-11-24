<?php

namespace Tests\Feature;

use App\Ai\Agents\WmsAssistant;
use App\Models\Site;
use App\Models\SiteUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Site::factory()->create();
    }

    public function test_chat_accepts_string_user_ids_from_other_sites(): void
    {
        WmsAssistant::fake(['Halo']);

        $this->postJson('/api/chat', ['message' => 'halo'], $this->headers(['sub' => 'u-7f3a9c']))->assertOk();

        $this->assertDatabaseHas('site_users', ['external_id' => 'u-7f3a9c']);
    }

    public function test_chat_rejects_expired_token(): void
    {
        $this->postJson('/api/chat', ['message' => 'halo'], $this->headers(['exp' => time() - 120]))
            ->assertStatus(401)
            ->assertJsonPath('message', 'Token tidak valid.');
    }

    public function test_chat_rejects_inactive_site(): void
    {
        Site::query()->update(['active' => false]);

        $this->postJson('/api/chat', ['message' => 'halo'], $this->headers())->assertStatus(422);
    }

    public function test_chat_rejects_malformed_token(): void
    {
        $this->postJson('/api/chat', ['message' => 'halo'], [
            'Authorization' => 'Bearer bukan-jwt',
            'X-Site' => 'wms',
        ])
            ->assertStatus(401)
            ->assertJsonPath('message', 'Token tidak valid.');
    }

    public function test_chat_rejects_missing_token(): void
    {
        $this->postJson('/api/chat', ['message' => 'halo'], ['X-Site' => 'wms'])->assertStatus(401);
    }

    public function test_chat_rejects_other_users_conversation(): void
    {
        WmsAssistant::fake(['Halo']);
        $conversationId = $this->postJson('/api/chat', ['message' => 'halo'], $this->headers(['sub' => 5]))
            ->headers
            ->get('X-Conversation-Id');

        $this->postJson('/api/chat', [
            'conversation_id' => $conversationId,
            'message' => 'halo lagi',
        ], $this->headers(['sub' => 6]))->assertStatus(404);
    }

    public function test_chat_rejects_site_with_unsupported_driver(): void
    {
        Site::factory()->create([
            'code' => 'lain',
            'driver' => 'other',
        ]);

        $this->postJson('/api/chat', ['message' => 'halo'], $this->headers(site: 'lain'))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Site ini belum didukung.');
    }

    public function test_chat_rejects_token_signed_with_another_key(): void
    {
        $this->postJson('/api/chat', ['message' => 'halo'], $this->headers(signingKey: 'foreign-private'))
            ->assertStatus(401)
            ->assertJsonPath('message', 'Token tidak valid.');
    }

    public function test_chat_rejects_unknown_site(): void
    {
        $this->postJson('/api/chat', ['message' => 'halo'], $this->headers(site: 'lain'))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Site tidak dikenal.');
    }

    public function test_chat_requires_message_or_decisions(): void
    {
        $this->postJson('/api/chat', [], $this->headers())->assertStatus(422);
    }

    public function test_chat_streams_and_stores_conversation_for_site_user(): void
    {
        WmsAssistant::fake(['Halo Budi']);

        $response = $this->postJson('/api/chat', ['message' => 'halo'], $this->headers(['roles' => ['operator', 'supervisor']]));

        $response->assertOk();
        $this->assertStringContainsString('"delta":"Halo"', $response->streamedContent());
        $this->assertNotNull($response->headers->get('X-Conversation-Id'));
        $siteUser = SiteUser::query()->where('external_id', '5')->firstOrFail();

        $this->assertDatabaseHas('site_users', [
            'name' => 'Budi',
            'role' => 'operator, supervisor',
            'site_id' => $siteUser->site_id,
        ]);
        $this->assertDatabaseHas('agent_conversations', [
            'participant_id' => $siteUser->id,
            'participant_type' => SiteUser::class,
        ]);
    }

    public function test_chat_updates_site_user_profile_without_duplicating_row(): void
    {
        WmsAssistant::fake(['Halo', 'Halo lagi']);
        $this->postJson('/api/chat', ['message' => 'halo'], $this->headers(['roles' => ['operator']]))->assertOk();

        $this->postJson('/api/chat', ['message' => 'halo lagi'], $this->headers(['roles' => ['supervisor']]))->assertOk();

        $this->assertDatabaseCount('site_users', 1);
        $this->assertDatabaseHas('site_users', [
            'external_id' => '5',
            'role' => 'supervisor',
        ]);
    }

    public function test_conversation_detail_rejects_other_user_and_site(): void
    {
        WmsAssistant::fake(['Halo']);
        $conversationId = $this->postJson('/api/chat', ['message' => 'halo'], $this->headers(['sub' => 5]))
            ->headers
            ->get('X-Conversation-Id');

        $this->getJson("/api/conversations/{$conversationId}", $this->headers(['sub' => 5]))->assertOk();
        $this->getJson("/api/conversations/{$conversationId}", $this->headers(['sub' => 6]))->assertStatus(404);

        Site::factory()->create([
            'code' => 'lain',
            'url' => 'http://lain.test/api',
        ]);

        $this->getJson("/api/conversations/{$conversationId}", $this->headers(['sub' => 5], 'lain'))->assertStatus(404);
    }

    public function test_conversations_are_listed_per_site_user(): void
    {
        WmsAssistant::fake(['Halo']);
        $this->postJson('/api/chat', ['message' => 'halo'], $this->headers(['sub' => 5]));

        $this->getJson('/api/conversations', $this->headers(['sub' => 5]))->assertOk()->assertJsonCount(1, 'data');
        $this->getJson('/api/conversations', $this->headers(['sub' => 6]))->assertOk()->assertJsonCount(0, 'data');
    }
}
