<?php

namespace App\Console\Commands;

use App\Models\SiteSettingSimple;
use App\Services\FaviconService;
use Illuminate\Console\Command;

class GenerateFavicons extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'favicon:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate favicons from uploaded logo';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $settings = SiteSettingSimple::getInstance();
            
            if (!$settings->logo_path) {
                $this->error('No logo found in site settings. Please upload a logo first.');
                return 1;
            }
            
            $this->info('Generating favicons from logo: ' . $settings->logo_path);
            
            $faviconService = new FaviconService();
            $success = $faviconService->generateFavicon($settings->logo_path);
            
            if ($success) {
                $this->info('Favicons generated successfully!');
                $this->line('Generated files:');
                $this->line('- favicon.ico');
                $this->line('- favicon-16x16.png');
                $this->line('- favicon-32x32.png');
                $this->line('- apple-touch-icon.png');
                $this->line('- android-chrome-192x192.png');
                $this->line('- android-chrome-512x512.png');
            } else {
                $this->error('Failed to generate favicons. Check the logs for more details.');
                return 1;
            }
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
}

