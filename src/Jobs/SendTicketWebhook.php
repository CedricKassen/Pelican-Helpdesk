<?php

namespace CedricKassen\Helpdesk\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendTicketWebhook implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public int $timeout = 15;

    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(public string $url, public array $payload) {}

    public function handle(): void
    {
        try {
            $response = Http::asJson()
                ->timeout(10)
                ->post($this->url, $this->payload);

            if ($response->failed()) {
                Log::warning('[helpdesk] Webhook returned a non-successful status', [
                    'status' => $response->status(),
                ]);
            }
        } catch (Throwable $e) {
            Log::warning('[helpdesk] Failed to deliver ticket webhook: ' . $e->getMessage());
        }
    }
}
