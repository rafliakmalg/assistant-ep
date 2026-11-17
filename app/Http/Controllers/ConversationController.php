<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Storage\DatabaseConversationStore;

class ConversationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->attributes->get('user');

        $conversations = Conversation::query()
            ->where('participant_id', Conversation::participantKey($user))
            ->where('participant_type', Conversation::participantType($user))
            ->orderByDesc('updated_at')
            ->paginate(15, ['id', 'title', 'updated_at']);

        return response()->json($conversations);
    }

    public function show(Request $request, DatabaseConversationStore $store, string $conversation): JsonResponse
    {
        $user = $request->attributes->get('user');
        $type = Conversation::participantType($user);
        $key = Conversation::participantKey($user);

        abort_unless($store->conversationBelongsTo($conversation, $type, $key), 404, __('index.chat.not_found'));

        $messages = $store->paginateConversationMessages($conversation, 20, cursor: $request->query('cursor'));
        $pending = $store->pendingApprovalsFor($conversation);

        return response()->json([
            'messages' => $messages,
            'pending_approvals' => $pending,
        ]);
    }
}
