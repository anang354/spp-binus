<?php

namespace App\Jobs;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BroadcastTagihan implements ShouldQueue
{
    use Queueable;

    protected $pesanFinal;
    protected $target;
    protected $token;

    /**
     * Create a new job instance.
     */
    public function __construct($pesanFinal, $target, $token)
    {
        $this->pesanFinal = $pesanFinal;
        $this->target = $target;
        $this->token = $token;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $response = Http::withHeaders([
        'Authorization' => $this->token,
        ])->post('https://api.fonnte.com/send', [
            'target' => $this->target,
            'message' => $this->pesanFinal,
        ]);
        if ($response->failed()) {
            Log::error('Gagal mengirim WA Fonnte: ' . $response->body());
        }
    }
}
