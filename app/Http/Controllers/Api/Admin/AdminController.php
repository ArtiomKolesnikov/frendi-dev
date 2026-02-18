<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

abstract class AdminController extends Controller
{
    protected function ensureAdmin(Request $request): void
    {
        $token = $request->header('X-Admin-Token') ?? $request->input('admin_token');
        $expected = config('frendi.admin_token');

        abort_unless($expected && hash_equals($expected, (string) $token), 403, 'Требуется административный доступ.');
    }
}
