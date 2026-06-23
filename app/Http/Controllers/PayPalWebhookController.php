<?php

namespace App\Http\Controllers;

use App\Services\PaymentGateways\PayPalBookingGateway;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PayPalWebhookController extends Controller
{
    public function handle(Request $request, PayPalBookingGateway $gateway): Response
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $gateway->handleWebhook($payload);

        return response('OK', 200);
    }
}
