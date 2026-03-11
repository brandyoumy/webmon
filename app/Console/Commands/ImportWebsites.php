<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Website;

class ImportWebsites extends Command
{
    protected $signature = 'import:websites {file}';
    protected $description = 'Import websites from a TXT file';

    public function handle()
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("File not found: $file");
            return 1;
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        foreach ($lines as $line) {
            $url = trim($line);

            // Extract name after http:// or https://
            $name = preg_replace('#^https?://#', '', $url);

            // Remove trailing slash if exists
            $name = rtrim($name, '/');

            // Save to database
            Website::create([
                'url' => $url,
                'name' => $name,
            ]);
        }

        $this->info("Imported " . count($lines) . " websites.");
        return 0;
    }
}