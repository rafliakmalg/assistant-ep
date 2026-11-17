<?php

namespace Tests\Feature;

use App\Ai\Agents\WmsAssistant;
use App\Models\SiteUser;
use App\Services\WmsClient;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WmsAssistantTest extends TestCase
{
    use RefreshDatabase;

    public function test_history_sent_to_llm_is_limited_to_latest_30_messages(): void
    {
        $user = SiteUser::factory()->create(['name' => 'Budi']);
        $conversationId = '00000000-0000-0000-0000-000000000001';

        DB::table('agent_conversations')->insert([
            'id' => $conversationId,
            'participant_id' => $user->id,
            'participant_type' => SiteUser::class,
            'title' => 'Uji',
        ]);

        foreach (range(1, 40) as $number) {
            DB::table('agent_conversation_messages')->insert([
                'id' => sprintf('00000000-0000-0000-0000-%012d', $number),
                'agent' => WmsAssistant::class,
                'attachments' => '[]',
                'content' => "pesan {$number}",
                'conversation_id' => $conversationId,
                'meta' => '{}',
                'participant_id' => $user->id,
                'participant_type' => SiteUser::class,
                'role' => 'user',
                'status' => 'completed',
                'steps' => '[]',
                'usage' => '{}',
            ]);
        }

        $agent = WmsAssistant::make(client: new WmsClient('http://wms.test/api', 'token'), user: $user)
            ->continue($conversationId, as: $user);

        $messages = collect($agent->messages());

        $this->assertCount(30, $messages);
        $this->assertSame('pesan 11', $messages->first()->content);
        $this->assertSame('pesan 40', $messages->last()->content);
    }
}
