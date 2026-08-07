<?php

namespace App\Http\Controllers;

use App\Models\EmailUnsubscribe;
use Illuminate\Http\Request;

class EmailUnsubscribeController extends Controller
{
    public function __invoke(Request $request, string $token)
    {
        $unsubscribe = EmailUnsubscribe::where('token', $token)->firstOrFail();

        if (! $unsubscribe->unsubscribed_at) {
            $unsubscribe->forceFill([
                'unsubscribed_at' => now(),
                'source' => $unsubscribe->source ?: 'marketing',
            ])->save();
        }

        return view('email.unsubscribe', compact('unsubscribe'));
    }
}
