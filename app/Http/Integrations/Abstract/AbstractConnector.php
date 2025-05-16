<?php

namespace App\Http\Integrations\Abstract;

use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;
use Saloon\Traits\Plugins\AcceptsJson;
use App\Models\ApiLog;
use Illuminate\Support\Facades\Auth;

class AbstractConnector extends Connector
{
    use AcceptsJson;

    public function __construct()
    {
        $this->debugRequest(function (PendingRequest $pendingRequest) {
            ApiLog::create([
                'url' => $pendingRequest->getUrl(),
                'method' => $pendingRequest->getMethod(),
                'status' => 'pending',
                'request_headers' => json_encode($pendingRequest->headers()?->all()),
                'request_body' => json_encode($pendingRequest->body()?->all()),
                'user_id' => Auth::id(),
            ]);
        });

        $this->debugResponse(function (Response $response) {
            $log = ApiLog::where('url', $response->getPendingRequest()->getUrl())
                ->where('method', $response->getPendingRequest()->getMethod())
                ->latest()->first();
            if ($log) {
                $log->update([
                    'status' => $response->status(),
                    'response_headers' => json_encode($response->headers()?->all()),
                    'response_body' => $response->body(),
                ]);
            }
        });
    }

    public function resolveBaseUrl(): string
    {
        return config('services.openai.base_url');
    }
}
