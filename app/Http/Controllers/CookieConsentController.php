<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class CookieConsentController extends Controller
{
    /**
     * Accept cookie consent
     */
    public function accept(Request $request)
    {
        $type = $request->input('type', 'all');
        
        // Store in cookie for 1 year
        $cookie = Cookie::make('cookie_consent_accepted', 'true', 525600); // 1 year in minutes
        $typeCookie = Cookie::make('cookie_consent_type', $type, 525600);
        
        return response()->json(['success' => true])
            ->cookie($cookie)
            ->cookie($typeCookie);
    }
}

