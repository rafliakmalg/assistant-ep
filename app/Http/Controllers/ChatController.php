<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatRequest;
use Illuminate\Support\Str;
use Laravel\Ai\Approvals\Decisions;
use Laravel\Ai\Models\Conversation;
use Laravel\Ai\Storage\DatabaseConversationStore;
use Symfony\Component\HttpFoundation\Response;

class ChatController extends Controller
{
    public function store(ChatRequest $request, DatabaseConversationStore $store): Response
    {
        $param = $request->validated();
        $client = $request->attributes->get('client');
        $site = $request->attributes->get('site');
        $user = $request->attributes->get('user');
        $type = Conversation::participantType($user);
        $key = Conversation::participantKey($user);
        $conversationId = $param['conversation_id'] ?? null;

        if ($conversationId === null) {
            $title = Str::limit($param['message'], 50, preserveWords: true);
            $conversationId = $store->storeConversation($type, $key, $title);
        }

        abort_unless($store->conversationBelongsTo($conversationId, $type, $key), 404, __('index.chat.not_found'));

        $prompt = isset($param['decisions']) ? Decisions::from($param['decisions']) : $param['message'];

        $agentClass = config("sites.drivers.{$site->driver}.agent");
        $agent = $agentClass::make(client: $client, user: $user)->continue($conversationId, as: $user);

        $stream = $agent
            ->stream($prompt, provider: config('services.assistant.providers'))
            ->usingVercelDataProtocol();

        $response = $stream->toResponse($request);
        $response->headers->set('X-Conversation-Id', $conversationId);

        return $response;
    }
}
