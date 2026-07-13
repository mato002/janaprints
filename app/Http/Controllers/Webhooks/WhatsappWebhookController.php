<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Support\Communications\Whatsapp\WhatsappInboundWebhookService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WhatsappWebhookController extends Controller
{
    public function __construct(
        protected WhatsappInboundWebhookService $inbound,
    ) {}

    public function verify(Request $request): Response
    {
        $hub = $request->query('hub');
        if (is_array($hub)) {
            $mode = (string) ($hub['mode'] ?? '');
            $token = (string) ($hub['verify_token'] ?? '');
            $challenge = (string) ($hub['challenge'] ?? '');
        } else {
            $mode = (string) $request->query('hub_mode', $request->query('hub.mode', ''));
            $token = (string) $request->query('hub_verify_token', $request->query('hub.verify_token', ''));
            $challenge = (string) $request->query('hub_challenge', $request->query('hub.challenge', ''));
        }

        $result = $this->inbound->verify($mode, $token, $challenge);

        if ($result === null) {
            return response('Forbidden', 403);
        }

        return response($result, 200)->header('Content-Type', 'text/plain');
    }

    public function receive(Request $request): Response
    {
        $recorded = $this->inbound->handleMetaPayload($request->all());

        return response('OK '.$recorded, 200);
    }
}
