<?php

namespace App\Jobs;

use Carbon\Carbon;
use App\Models\Tagihan;
use App\Models\Pengaturan;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;

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
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://api.fonnte.com/send',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => array(
                'target' => $this->target,
                'message' => $this->pesanFinal,
            ),
            CURLOPT_HTTPHEADER => array(
                'Authorization' => $this->token,
            ),
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
            // Log error jika perlu
        }
        curl_close($curl);
    }
}
