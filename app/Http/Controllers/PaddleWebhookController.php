<?php

namespace App\Http\Controllers;

use App\Services\PaymentGateways\PaddleBookingGateway;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PaddleWebhookController extends Controller
{
    public function handle(Request $request, PaddleBookingGateway $gateway): Response
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $gateway->handleWebhook($payload);

        return response('OK', 200);
    }
}
