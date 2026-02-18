<?php

namespace App\Support;

use Illuminate\Http\Request;

class ClientContext
{
    public static function token(Request $request): string
    {
        $headerToken = $request->header('X-Client-Token');
        if ($headerToken) {
            return substr($headerToken, 0, 64);
        }

        $cookieToken = $request->cookie('frendi_token');
        if ($cookieToken) {
            return substr($cookieToken, 0, 64);
        }

        // Generate a stable random token for this client (will be set as cookie by controllers)
        // Avoid IP/UserAgent fingerprinting to prevent cross-user collisions behind proxies
        return bin2hex(random_bytes(16));
    }

    public static function fingerprint(Request $request): string
    {
        // Try to get fingerprint from cookie first (most stable)
        $cookieFingerprint = $request->cookie('frendi_fingerprint');
        if ($cookieFingerprint && strlen($cookieFingerprint) === 64) {
            return $cookieFingerprint;
        }

        // Try to get from header (for API requests)
        $headerFingerprint = $request->header('X-Device-Fingerprint');
        if ($headerFingerprint && strlen($headerFingerprint) === 64) {
            return $headerFingerprint;
        }

        // Generate new stable fingerprint based on device characteristics
        $ip = $request->ip();
        $userAgent = $request->userAgent();
        $acceptLanguage = $request->header('Accept-Language', '');
        $acceptEncoding = $request->header('Accept-Encoding', '');
        
        // Create a stable hash from device characteristics
        $fingerprint = hash('sha256', $ip . '|' . $userAgent . '|' . $acceptLanguage . '|' . $acceptEncoding);
        
        return $fingerprint;
    }
}
