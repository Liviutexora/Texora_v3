<?php

namespace App\Http\Controllers;

use App\Services\PaymentGateways\RazorpayBookingGateway;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class RazorpayWebhookController extends Controller
{
    public function handle(Request $request, RazorpayBookingGateway $gateway): Response
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $gateway->handleWebhook($payload);

        return response('OK', 200);
    }
}
