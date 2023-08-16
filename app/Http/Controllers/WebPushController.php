<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PushSubscription;
use Illuminate\Support\Facades\Log;

class WebPushController
{
    public function saveSubscription(Request $request)
    {
        Log::info('saving subscription', ['$request' => $request->all()]);

        $auth = $request->get('keys')['auth'];

        $exist = PushSubscription::query()->where('auth', $auth)->first();

        if (!$exist) {
            $pushSubscription = new PushSubscription();
            $pushSubscription->auth = $auth;
            $pushSubscription->fields = json_encode($request->all());
            $pushSubscription->save();
        }

        return response()->json(['success' => true]);
    }
}
