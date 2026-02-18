<?php

namespace App\Support;

use Illuminate\Http\Request;

class ClientContext
{
    public static function token(Request $request): string
    {
        $cached = $request->attributes->get('client_token');
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $headerToken = $request->header('X-Client-Token');
        if ($headerToken) {
            $token = substr($headerToken, 0, 64);
            $request->attributes->set('client_token', $token);
            return $token;
        }

        $cookieToken = $request->cookie('frendi_token');
        if ($cookieToken) {
            $token = substr($cookieToken, 0, 64);
            $request->attributes->set('client_token', $token);
            return $token;
        }

        // Generate a stable random token for this client (will be set as cookie by controllers)
        // Avoid IP/UserAgent fingerprinting to prevent cross-user collisions behind proxies
        $token = bin2hex(random_bytes(16));
        $request->attributes->set('client_token', $token);
        return $token;
    }

    public static function fingerprint(Request $request): string
    {
        $cached = $request->attributes->get('device_fingerprint');
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        // Try to get fingerprint from cookie first (most stable)
        $cookieFingerprint = $request->cookie('frendi_fingerprint');
        if ($cookieFingerprint && strlen($cookieFingerprint) === 64) {
            $request->attributes->set('device_fingerprint', $cookieFingerprint);
            return $cookieFingerprint;
        }

        // Try to get from header (for API requests)
        $headerFingerprint = $request->header('X-Device-Fingerprint');
        if ($headerFingerprint && strlen($headerFingerprint) === 64) {
            $request->attributes->set('device_fingerprint', $headerFingerprint);
            return $headerFingerprint;
        }

        // Generate new stable fingerprint based on device characteristics
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $acceptLanguage = $request->header('Accept-Language', '');
        $acceptEncoding = $request->header('Accept-Encoding', '');
        
        // Create a stable hash from device characteristics
        $fingerprint = hash('sha256', $ip . '|' . $userAgent . '|' . $acceptLanguage . '|' . $acceptEncoding);
        $request->attributes->set('device_fingerprint', $fingerprint);
        return $fingerprint;
    }
}
