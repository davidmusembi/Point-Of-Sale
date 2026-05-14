<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CreateCloudflareDnsRecord implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $backoff = 10;

    public function __construct(protected string $domain) {}

    public function handle(): void
    {
        $token  = config('services.cloudflare.token');
        $zoneId = config('services.cloudflare.zone_id');
        $ip     = config('services.cloudflare.origin_ip');

        if (! $token || ! $zoneId || ! $ip) {
            Log::warning("CreateCloudflareDnsRecord: skipped for {$this->domain} — CF_API_TOKEN, CF_ZONE_ID or SERVER_IP not configured.");
            return;
        }

        $response = Http::withToken($token)
            ->acceptJson()
            ->post("https://api.cloudflare.com/client/v4/zones/{$zoneId}/dns_records", [
                'type'    => 'A',
                'name'    => $this->domain,
                'content' => $ip,
                'proxied' => true,
                'ttl'     => 1, // 1 = automatic when proxied
            ]);

        $body = $response->json();

        if ($response->successful() && ($body['success'] ?? false)) {
            Log::info("Cloudflare DNS A record created: {$this->domain} → {$ip}");
            return;
        }

        // Code 81057 = record already exists — not an error worth retrying
        $errors = $body['errors'] ?? [];
        foreach ($errors as $error) {
            if (($error['code'] ?? 0) === 81057) {
                Log::info("Cloudflare DNS record for {$this->domain} already exists — skipping.");
                return;
            }
        }

        $message = collect($errors)->pluck('message')->implode(', ');
        Log::error("Cloudflare DNS creation failed for {$this->domain}: {$message}");

        $this->fail(new \RuntimeException("Cloudflare API error for {$this->domain}: {$message}"));
    }
}
