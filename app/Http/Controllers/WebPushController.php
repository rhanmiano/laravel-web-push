<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\PusherBroadcast;
use Minishlink\WebPush\WebPush;
use App\Models\PushSubscription;
use Minishlink\WebPush\Subscription;

class WebPushController
{
    public function saveSubscription(Request $request)
    {
        \Log::info('saving subscription', ['$request' => $request->all()]);

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
