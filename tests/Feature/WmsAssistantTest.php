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

    /**
     * @return array<int, string>
     */
    private function toolClasses(array $permissions): array
    {
        $user = SiteUser::factory()->create();
        $agent = WmsAssistant::make(client: new WmsClient('http://wms.test/api', 'token'), permissions: $permissions, user: $user);

        $classes = array_map(fn ($tool) => class_basename($tool), [...$agent->tools()]);
        sort($classes);

        return $classes;
    }

    public function test_user_without_permissions_only_gets_tools_that_need_none(): void
    {
        $this->assertSame(['GetUsers'], $this->toolClasses([]));
    }

    public function test_tools_are_limited_to_the_permissions_of_the_user(): void
    {
        $classes = $this->toolClasses(['product.read', 'handover.read', 'handover.create']);

        $this->assertSame(['CreateHandover', 'GetHandover', 'GetHandovers', 'GetProducts', 'GetUsers'], $classes);
    }

    public function test_user_with_every_permission_gets_all_tools(): void
    {
        $permissions = [
            'adjustment.cancel', 'adjustment.create', 'adjustment.read', 'handover.cancel', 'handover.complete', 'handover.create',
            'handover.read', 'handover.transfer', 'inventory.read', 'movement.create', 'movement.read', 'product.read', 'rack.read', 'warehouse.read',
        ];

        $this->assertCount(17, $this->toolClasses($permissions));
    }

    public function test_write_tools_are_hidden_when_only_read_permissions_are_granted(): void
    {
        $classes = $this->toolClasses(['adjustment.read', 'handover.read', 'inventory.read', 'movement.read', 'product.read', 'rack.read', 'warehouse.read']);

        $this->assertSame([], array_values(array_filter($classes, fn (string $class) => str_starts_with($class, 'Cancel') || str_starts_with($class, 'Complete') || str_starts_with($class, 'Create') || str_starts_with($class, 'Transfer'))));
    }
}
