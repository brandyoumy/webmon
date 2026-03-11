<?php

namespace App\Console\Commands;

use App\Mail\WebsiteDownAlert;
use App\Models\UptimeLogs;
use App\Models\User;
use App\Models\Website;
use Illuminate\Console\Command;
use Mail;

class MonitorWebsites extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'monitor:websites';

    /**
     * The console command description.
     *
     * @var string
     */
    
    protected $description = 'Check all monitored websites and log status including SSL validity';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $sites = Website::all();
        $alerts = [];

        foreach ($sites as $site) {
            $this->info("Checking {$site->url}...");

            $start = microtime(true);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $site->url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_HEADER => true
            ]);
            curl_exec($ch);

            $statusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $responseTime = microtime(true) - $start;

            curl_close($ch);

            $sslValid = null;

            if (str_starts_with($site->url, 'https')) {
                $host = parse_url($site->url, PHP_URL_HOST);

                $contextOptions = [
                    "ssl" => [
                        "capture_peer_cert" => true,
                        "verify_peer" => true,
                        "verify_peer_name" => true,
                        "SNI_enabled" => true,
                        "peer_name" => $host
                    ]
                ];

                $stream = @stream_socket_client(
                    "ssl://{$host}:443",
                    $errno,
                    $errstr,
                    30,
                    STREAM_CLIENT_CONNECT,
                    stream_context_create($contextOptions)
                );

                if ($stream) {
                    $params = stream_context_get_params($stream);
                    $peerCert = $params['options']['ssl']['peer_certificate'] ?? null;

                    if ($peerCert) {
                        $certInfo = openssl_x509_parse($peerCert);
                        // SSL is valid if expiration time is in the future
                        $sslValid = isset($certInfo['validTo_time_t']) && $certInfo['validTo_time_t'] > time();
                    } else {
                        $sslValid = false; // Could not get certificate
                    }
                } else {
                    $sslValid = false; // Could not connect SSL
                }
            }

            $isUp = $statusCode >= 200 && $statusCode < 400;

            // Log uptime
            UptimeLogs::create([
                'websites_id' => $site->id,
                'status_code' => $statusCode,
                'response_time' => $responseTime,
                'is_up' => $statusCode >= 200 && $statusCode < 400,
                'ssl_valid' => $sslValid,
                'checked_at' => now()
            ]);

            // Always update check_ssl: true = valid, false = invalid or null
            $site->update([
                'check_ssl' => $sslValid ?? false
            ]);

            // get latest log
           // $lastLog = $site->latestLog;
             $lastLog = UptimeLogs::where('websites_id', $site->id)
                ->orderBy('checked_at', 'desc')
                ->skip(1)
                ->first();

         

             // Determine if status changed
            $statusChanged = false;
            if ($lastLog) {
                if ($lastLog->is_up !== $isUp || $lastLog->ssl_valid !== $sslValid) {
                    $statusChanged = true;
                }
            } else {
                // First run, consider it a status change
                $statusChanged = true;
            }

            if ($statusChanged) {
                $alerts[] = [
                    'site' => $site,
                    'statusCode' => $statusCode,
                    'sslValid' => $sslValid,
                    'isUp' => $isUp
                ];
            }


            /*
                SEND EMAIL WHEN WEBSITE DOWN / SSL INVALID
            */
            // if(
            //     (!$isUp && ($lastLog || $lastLog->is_up)) ||
            //     ($sslValid === false && (!$lastLog || $lastLog->ssl_valid))
            // )
            // {
            //     Mail::to($emails)->send(new WebsiteDownAlert($site, $statusCode, $sslValid));

            // }

            $this->info(
                "Status: " . ($statusCode ?? 'N/A') .
                " | SSL: " . ($sslValid === null ? 'Invalid' : ($sslValid ? 'Valid' : 'Invalid')) .
                " | Response: " . round($responseTime, 2) . "s"
            );


        }

        
        // Send a single email if there are any status changes
        if (!empty($alerts)) {
            $emails = User::pluck('email')->toArray();
            Mail::to($emails)->send(new WebsiteDownAlert($alerts));
            $this->info("Summary email sent to " . implode(', ', $emails));
        }
        else
        {
            $this->info("No status changes detected. No email sent.");

        }

        $this->info("Total websites with status changes: " . count($alerts));
    }
}
