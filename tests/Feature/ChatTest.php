<?php

namespace Tests\Feature;

use App\Ai\Agents\WmsAssistant;
use App\Models\Site;
use App\Models\SiteUser;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Factory;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ChatTest extends TestCase
{
    use RefreshDatabase;

    private function createCustomSite(): void
    {
        Site::factory()->create([
            'code' => 'toko',
            'identity' => [
                'endpoint' => 'me',
                'fields' => [
                    'id' => 'user.uid',
                    'name' => 'user.full_name',
                ],
            ],
            'url' => 'http://toko.test/api',
        ]);
    }

    private function fakeMe(int|string $id = 5, string $role = 'operator'): void
    {
        Http::swap(new Factory);
        Http::fake([
            '*/auth/me' => Http::response([
                'data' => [
                    'id' => $id,
                    'name' => 'Budi',
                    'role' => ['name' => $role],
                ],
            ]),
        ]);
    }

    private function headers(string $site = 'wms'): array
    {
        return [
            'Authorization' => 'Bearer token',
            'X-Site' => $site,
        ];
    }

    protected function setUp(): void
    {
        parent::setUp();

        Site::factory()->create();
    }

    public function test_chat_accepts_string_user_ids_from_other_sites(): void
    {
        WmsAssistant::fake(['Halo']);
        $this->fakeMe('u-7f3a9c');

        $this->postJson('/api/chat', ['message' => 'halo'], $this->headers())->assertOk();

        $this->assertDatabaseHas('site_users', ['external_id' => 'u-7f3a9c']);
    }

    public function test_chat_rejects_identity_response_without_mapped_fields(): void
    {
        $this->createCustomSite();
        Http::swap(new Factory);
        Http::fake(['*/me' => Http::response(['user' => ['full_name' => 'Sari']])]);

        $this->postJson('/api/chat', ['message' => 'halo'], $this->headers('toko'))->assertStatus(401);
    }

    public function test_chat_rejects_inactive_site(): void
    {
        $this->fakeMe();
        Site::query()->update(['active' => false]);

        $this->postJson('/api/chat', ['message' => 'halo'], $this->headers())->assertStatus(422);
    }

    public function test_chat_rejects_invalid_token(): void
    {
        Http::fake(['*/auth/me' => Http::response([], 401)]);

        $this->postJson('/api/chat', ['message' => 'halo'], $this->headers())
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
        $this->fakeMe(5);
        $conversationId = $this->postJson('/api/chat', ['message' => 'halo'], $this->headers())
            ->headers
            ->get('X-Conversation-Id');

        $this->fakeMe(6);

        $this->postJson('/api/chat', [
            'conversation_id' => $conversationId,
            'message' => 'halo lagi',
        ], $this->headers())->assertStatus(404);
    }

    public function test_chat_rejects_site_with_unsupported_driver(): void
    {
        $this->fakeMe();
        Site::factory()->create([
            'code' => 'lain',
            'driver' => 'other',
        ]);

        $this->postJson('/api/chat', ['message' => 'halo'], $this->headers('lain'))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Site ini belum didukung.');
    }

    public function test_chat_rejects_unknown_site(): void
    {
        $this->fakeMe();

        $this->postJson('/api/chat', ['message' => 'halo'], $this->headers('lain'))
            ->assertStatus(422)
            ->assertJsonPath('message', 'Site tidak dikenal.');
    }

    public function test_chat_requires_message_or_decisions(): void
    {
        $this->fakeMe();

        $this->postJson('/api/chat', [], $this->headers())->assertStatus(422);
    }

    public function test_chat_streams_and_stores_conversation_for_site_user(): void
    {
        WmsAssistant::fake(['Halo Budi']);
        $this->fakeMe();

        $response = $this->postJson('/api/chat', ['message' => 'halo'], $this->headers());

        $response->assertOk();
        $this->assertStringContainsString('"delta":"Halo"', $response->streamedContent());
        $this->assertNotNull($response->headers->get('X-Conversation-Id'));
        $siteUser = SiteUser::query()->where('external_id', '5')->firstOrFail();

        $this->assertDatabaseHas('site_users', [
            'name' => 'Budi',
            'role' => 'operator',
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
        $this->fakeMe(5, 'operator');
        $this->postJson('/api/chat', ['message' => 'halo'], $this->headers())->assertOk();

        $this->fakeMe(5, 'supervisor');
        $this->postJson('/api/chat', ['message' => 'halo lagi'], $this->headers())->assertOk();

        $this->assertDatabaseCount('site_users', 1);
        $this->assertDatabaseHas('site_users', [
            'external_id' => '5',
            'role' => 'supervisor',
        ]);
    }

    public function test_chat_uses_identity_mapping_of_the_site(): void
    {
        WmsAssistant::fake(['Halo']);
        $this->createCustomSite();
        Http::swap(new Factory);
        Http::fake(['*/me' => Http::response(['user' => ['full_name' => 'Sari', 'uid' => 77]])]);

        $this->postJson('/api/chat', ['message' => 'halo'], $this->headers('toko'))->assertOk();

        $this->assertDatabaseHas('site_users', [
            'external_id' => '77',
            'name' => 'Sari',
            'role' => null,
        ]);
    }

    public function test_conversation_detail_rejects_other_user_and_site(): void
    {
        WmsAssistant::fake(['Halo']);
        $this->fakeMe(5);
        $conversationId = $this->postJson('/api/chat', ['message' => 'halo'], $this->headers())
            ->headers
            ->get('X-Conversation-Id');

        $this->fakeMe(5);
        $this->getJson("/api/conversations/{$conversationId}", $this->headers())->assertOk();

        $this->fakeMe(6);
        $this->getJson("/api/conversations/{$conversationId}", $this->headers())->assertStatus(404);

        Site::factory()->create([
            'code' => 'lain',
            'url' => 'http://lain.test/api',
        ]);
        $this->fakeMe(5);
        $this->getJson("/api/conversations/{$conversationId}", $this->headers('lain'))->assertStatus(404);
    }

    public function test_conversations_are_listed_per_site_user(): void
    {
        WmsAssistant::fake(['Halo']);
        $this->fakeMe(5);
        $this->postJson('/api/chat', ['message' => 'halo'], $this->headers());

        $this->fakeMe(5);
        $this->getJson('/api/conversations', $this->headers())->assertOk()->assertJsonCount(1, 'data');

        $this->fakeMe(6);
        $this->getJson('/api/conversations', $this->headers())->assertOk()->assertJsonCount(0, 'data');
    }
}
