<?php

use Tabi\SDK\TabiClient;
use Wasa\Env;

header('Content-Type: application/json');

$apiKey = Env::get('TABI_API_KEY');
if (!$apiKey) {
    echo json_encode(['error' => 'TABI_API_KEY not set — copy .env.example to .env']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: [];
$action = $input['action'] ?? '';
$channelId = $input['channelId'] ?? Env::get('TABI_CHANNEL_ID');

$tabi = new TabiClient($apiKey, Env::get('TABI_BASE_URL', 'https://api.c36.online/api/v1'));

try {
    $result = match ($action) {
        /* Channels */
        'channels.list'    => $tabi->channels()->list(),
        'channels.status'  => $tabi->channels()->status($channelId),

        /* Messages */
        'messages.send' => $tabi->messages()->send($channelId, [
            'to'          => $input['to'] ?? '',
            'content'     => $input['content'] ?? '',
            'messageType' => $input['messageType'] ?? 'text',
            'mediaUrl'    => $input['mediaUrl'] ?? null,
        ]),
        'messages.sendLocation' => $tabi->messages()->sendLocation($channelId, [
            'to'        => $input['to'] ?? '',
            'latitude'  => (string) ($input['latitude'] ?? '0'),
            'longitude' => (string) ($input['longitude'] ?? '0'),
        ]),
        'messages.sendPoll' => $tabi->messages()->sendPoll($channelId, [
            'to'        => $input['to'] ?? '',
            'question'  => $input['question'] ?? '',
            'options'   => $input['options'] ?? [],
            'maxAnswer' => (int) ($input['maxAnswer'] ?? 1),
        ]),
        'messages.sendContact' => $tabi->messages()->sendContact($channelId, [
            'to'           => $input['to'] ?? '',
            'contactName'  => $input['contactName'] ?? '',
            'contactPhone' => $input['contactPhone'] ?? '',
        ]),

        /* Contacts */
        'contacts.list'   => $tabi->contacts()->list(['page' => $input['page'] ?? 1, 'limit' => $input['limit'] ?? 20]),
        'contacts.create' => $tabi->contacts()->create($input['data'] ?? []),

        /* Conversations */
        'conversations.list' => $tabi->conversations()->list(['page' => $input['page'] ?? 1, 'limit' => $input['limit'] ?? 20]),

        /* Webhooks */
        'webhooks.list'   => $tabi->webhooks()->list(),
        'webhooks.create' => $tabi->webhooks()->create($input['data'] ?? []),

        default => throw new \RuntimeException("Unknown action: {$action}"),
    };

    echo json_encode(['ok' => true, 'data' => $result]);
} catch (\Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'error' => $e->getMessage()]);
}
