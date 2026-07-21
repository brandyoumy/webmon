<?php

namespace App\Console\Commands;

use App\Mail\WebsiteDownAlert;
use App\Models\UptimeLogs;
use App\Models\User;
use App\Models\Website;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Mail;

class MonitorWebsites extends Command
{
    protected $signature = 'monitor:websites';

    protected $description = 'Check all monitored websites and log status including SSL validity and domain expiry';

    private array $whoisServers = [
        'com'    => 'whois.verisign-grs.com',
        'net'    => 'whois.verisign-grs.com',
        'org'    => 'whois.pir.org',
        'my'     => 'whois.mynic.my',
        'io'     => 'whois.nic.io',
        'co'     => 'whois.nic.co',
        'uk'     => 'whois.nic.uk',
        'sg'     => 'whois.sgnic.sg',
        'edu'    => 'whois.educause.edu',
        'info'   => 'whois.afilias.net',
        'biz'    => 'whois.biz',
        'dev'    => 'whois.nic.google',
        'app'    => 'whois.nic.google',
        'id'     => 'whois.id',
        'au'     => 'whois.auda.org.au',
        'jp'     => 'whois.jprs.jp',
        'de'     => 'whois.denic.de',
        'fr'     => 'whois.nic.fr',
        'us'     => 'whois.nic.us',
        'ca'     => 'whois.cira.ca',
        'cn'     => 'whois.cnnic.cn',
        'in'     => 'whois.registry.in',
        'ru'     => 'whois.tcinet.ru',
        'xyz'    => 'whois.nic.xyz',
        'online' => 'whois.nic.online',
        'store'  => 'whois.nic.store',
        'tech'   => 'whois.nic.tech',
        'site'   => 'whois.nic.site',
        'cloud'  => 'whois.nic.cloud',
    ];

    public function handle()
    {
        $sites = Website::all();
        $alerts = [];

        foreach ($sites as $site) {
            $this->info("Checking {$site->url}...");

            $start = microtime(true);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL            => $site->url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_HEADER         => true,
                CURLOPT_USERAGENT      => 'BrandYou-Webmon/1.0 (+https://webmon.brandyou.my)',
            ]);
            curl_exec($ch);

            $statusCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $responseTime = microtime(true) - $start;

            curl_close($ch);

            $sslValid = null;

            if ($site->check_ssl && str_starts_with($site->url, 'https')) {
                $host = parse_url($site->url, PHP_URL_HOST);

                $contextOptions = [
                    'ssl' => [
                        'capture_peer_cert' => true,
                        'verify_peer'       => true,
                        'verify_peer_name'  => true,
                        'SNI_enabled'       => true,
                        'peer_name'         => $host,
                    ],
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
                    $params   = stream_context_get_params($stream);
                    $peerCert = $params['options']['ssl']['peer_certificate'] ?? null;

                    if ($peerCert) {
                        $certInfo = openssl_x509_parse($peerCert);
                        $sslValid = isset($certInfo['validTo_time_t']) && $certInfo['validTo_time_t'] > time();
                    } else {
                        $sslValid = false;
                    }
                } else {
                    $sslValid = false;
                }
            }

            // Domain expiry via WHOIS
            $domainExpiresAt      = $this->getDomainExpiry($site->url);
            $domainExpiresInDays  = $domainExpiresAt ? (int) now()->diffInDays($domainExpiresAt, false) : null;
            $domainExpiringSoon   = $domainExpiresInDays !== null && $domainExpiresInDays >= 0 && $domainExpiresInDays <= 30;

            $isUp = $statusCode >= 200 && $statusCode < 400;

            UptimeLogs::create([
                'websites_id'  => $site->id,
                'status_code'  => $statusCode,
                'response_time'=> $responseTime,
                'is_up'        => $isUp,
                'ssl_valid'    => $sslValid,
                'checked_at'   => now(),
            ]);

            $site->update([
                'is_up'             => $isUp,
                'ssl_valid'         => $sslValid ?? false,
                'last_checked_at'   => now(),
                'domain_expires_at' => $domainExpiresAt,
            ]);

            $lastLog = UptimeLogs::where('websites_id', $site->id)
                ->orderBy('checked_at', 'desc')
                ->skip(1)
                ->first();

            $statusChanged = false;
            if ($lastLog) {
                if ($lastLog->is_up !== $isUp || $lastLog->ssl_valid !== $sslValid) {
                    $statusChanged = true;
                }
            } else {
                $statusChanged = true;
            }

            if ($statusChanged || $domainExpiringSoon) {
                $alerts[] = [
                    'site'               => $site,
                    'statusCode'         => $statusCode,
                    'sslValid'           => $sslValid,
                    'isUp'               => $isUp,
                    'domainExpiresAt'    => $domainExpiresAt,
                    'domainExpiringSoon' => $domainExpiringSoon,
                    'domainExpiresInDays'=> $domainExpiresInDays,
                ];
            }

            $domainLabel = $domainExpiresAt
                ? $domainExpiresAt->format('Y-m-d') . " ({$domainExpiresInDays}d)"
                : 'N/A';

            $this->info(
                "Status: " . ($statusCode ?? 'N/A') .
                " | SSL: " . ($sslValid === null ? 'N/A' : ($sslValid ? 'Valid' : 'Invalid')) .
                " | Domain Expiry: {$domainLabel}" .
                " | Response: " . round($responseTime, 2) . "s"
            );
        }

        if (!empty($alerts)) {
            $activeEmails = \App\Models\NotificationEmail::where('is_active', true)->pluck('email')->toArray();
            $emails = !empty($activeEmails) ? $activeEmails : User::pluck('email')->toArray();

            Mail::to($emails)->send(new WebsiteDownAlert($alerts));
            $this->info("Summary email sent to " . implode(', ', $emails));
        } else {
            $this->info("No status changes detected. No email sent.");
        }

        $this->info("Total websites with alerts: " . count($alerts));
    }

    private function getDomainExpiry(string $url): ?Carbon
    {
        $host = parse_url($url, PHP_URL_HOST);
        if (!$host) return null;

        $domain = $this->extractRegistrableDomain($host);
        if (!$domain) return null;

        $tld         = substr($domain, strrpos($domain, '.') + 1);
        $whoisServer = $this->whoisServers[$tld] ?? null;

        if (!$whoisServer) return null;

        $response = $this->queryWhois($domain, $whoisServer);
        if (!$response) return null;

        return $this->parseExpiryDate($response);
    }

    private function extractRegistrableDomain(string $host): ?string
    {
        $parts = explode('.', $host);
        $count = count($parts);

        if ($count < 2) return null;

        $tld = $parts[$count - 1];
        $sld = $parts[$count - 2];

        // Handle ccSLDs like .com.my, .co.uk, .net.au, .org.uk
        $ccSLDs = ['com', 'co', 'org', 'net', 'edu', 'gov', 'ac', 'sch', 'web'];
        if (\strlen($tld) === 2 && \in_array($sld, $ccSLDs, true) && $count >= 3) {
            return $parts[$count - 3] . '.' . $sld . '.' . $tld;
        }

        return $sld . '.' . $tld;
    }

    private function queryWhois(string $domain, string $server): ?string
    {
        $socket = @fsockopen($server, 43, $errno, $errstr, 10);
        if (!$socket) return null;

        stream_set_timeout($socket, 10);
        fwrite($socket, $domain . "\r\n");

        $response = '';
        while (!feof($socket)) {
            $response .= fread($socket, 4096);
        }
        fclose($socket);

        return $response ?: null;
    }

    private function parseExpiryDate(string $response): ?Carbon
    {
        $patterns = [
            '/Registry Expiry Date:\s*(.+)/i',
            '/Registrar Registration Expiration Date:\s*(.+)/i',
            '/Expir(?:y|ation) Date:\s*(.+)/i',
            '/Domain Expiration Date:\s*(.+)/i',
            '/paid-till:\s*(.+)/i',
            '/expires:\s*(.+)/i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $response, $matches)) {
                $dateStr = trim($matches[1]);
                try {
                    return Carbon::parse($dateStr);
                } catch (\Exception $e) {
                    continue;
                }
            }
        }

        return null;
    }
}
