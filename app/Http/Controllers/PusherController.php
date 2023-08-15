<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Events\PusherBroadcast;
use App\Models\PushSubscription;
use Exception;
use Minishlink\WebPush\WebPush;
use Minishlink\WebPush\Subscription;

class PusherController
{
    public function broadcast(Request $request)
    {
        broadcast(new PusherBroadcast($request->get('message')))->toOthers();

        $subscriptions = PushSubscription::all();
        $notifications = [];

        foreach ($subscriptions as $subscription)  {
            $notifications[] = [
                'subscription' => Subscription::create(json_decode($subscription->fields, true)),
                'payload' => json_encode([
                    "title" => "Here is a sample push notif",
                    "body" => $request->get('message')
                ])
            ];
        };

        if (!config('vapid.public_key') || !config('vapid.private_key')) {
            throw new Exception('Missing required vapid keys');
        }

        $auth = [
            'VAPID' => [
                'subject' => config('vapid.subject'),
                'publicKey' => config('vapid.public_key'),
                'privateKey' => config('vapid.private_key')
            ],
        ];

        $webPush = new WebPush($auth);

        foreach ($notifications as $notification) {
            $webPush->queueNotification(
                $notification['subscription'],
                $notification['payload']
            );
        }

        foreach ($webPush->flush() as $report) {
            $endpoint = $report->getRequest()->getUri()->__toString();

            if ($report->isSuccess()) {
                \Log::info("[v] Message sent successfully for subscription {$endpoint}.");
            } else {
                \Log::info("[x] Message failed to sent for subscription {$endpoint}: {$report->getReason()}");
            }
        }

        return response()->json(['success' => true]);
    }
}
