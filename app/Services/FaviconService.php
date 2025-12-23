<?php

namespace App\Services;

use App\Models\SiteSettingSimple;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class FaviconService
{
    /**
     * Generate favicon from uploaded logo
     */
    public function generateFavicon(string $logoPath): bool
    {
        try {
            $fullLogoPath = storage_path('app/public/logos/' . $logoPath);
            
            if (!file_exists($fullLogoPath)) {
                return false;
            }

            // Create favicon directory if it doesn't exist
            $faviconDir = public_path('favicons');
            if (!is_dir($faviconDir)) {
                mkdir($faviconDir, 0755, true);
            }

            // Generate different favicon sizes
            $sizes = [
                'favicon.ico' => 32,
                'favicon-16x16.png' => 16,
                'favicon-32x32.png' => 32,
                'apple-touch-icon.png' => 180,
                'android-chrome-192x192.png' => 192,
                'android-chrome-512x512.png' => 512,
            ];

            $manager = new ImageManager(new Driver());
            
            foreach ($sizes as $filename => $size) {
                $image = $manager->read($fullLogoPath);
                $image->resize($size, $size);
                
                if ($filename === 'favicon.ico') {
                    // For ICO files, we'll save as PNG and rename
                    $image->save($faviconDir . '/favicon.png');
                    $this->convertPngToIco($faviconDir . '/favicon.png', $faviconDir . '/favicon.ico');
                } else {
                    $image->save($faviconDir . '/' . $filename);
                }
            }

            return true;
        } catch (\Exception $e) {
            \Log::error('Favicon generation failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Convert PNG to ICO format (simple implementation)
     */
    private function convertPngToIco(string $pngPath, string $icoPath): void
    {
        // For now, we'll just copy the PNG as ICO
        // In a production environment, you might want to use a proper ICO converter
        copy($pngPath, $icoPath);
    }

    /**
     * Get favicon URL for the current site settings
     */
    public function getFaviconUrl(): string
    {
        try {
            $settings = SiteSettingSimple::getInstance();
            
            if ($settings->logo_path) {
                // Check if favicon exists
                $faviconPath = public_path('favicons/favicon.ico');
                if (file_exists($faviconPath)) {
                    return asset('favicons/favicon.ico');
                }
            }
            
            // Fallback to default favicon
            return asset('favicon.ico');
        } catch (\Exception $e) {
            return asset('favicon.ico');
        }
    }

    /**
     * Get all favicon URLs for different sizes
     */
    public function getFaviconUrls(): array
    {
        try {
            $settings = SiteSettingSimple::getInstance();
            $baseUrl = asset('favicons');
            
            if ($settings->logo_path) {
                $faviconDir = public_path('favicons');
                
                return [
                    'favicon' => file_exists($faviconDir . '/favicon.ico') ? $baseUrl . '/favicon.ico' : asset('favicon.ico'),
                    'favicon_16' => file_exists($faviconDir . '/favicon-16x16.png') ? $baseUrl . '/favicon-16x16.png' : null,
                    'favicon_32' => file_exists($faviconDir . '/favicon-32x32.png') ? $baseUrl . '/favicon-32x32.png' : null,
                    'apple_touch' => file_exists($faviconDir . '/apple-touch-icon.png') ? $baseUrl . '/apple-touch-icon.png' : null,
                    'android_192' => file_exists($faviconDir . '/android-chrome-192x192.png') ? $baseUrl . '/android-chrome-192x192.png' : null,
                    'android_512' => file_exists($faviconDir . '/android-chrome-512x512.png') ? $baseUrl . '/android-chrome-512x512.png' : null,
                ];
            }
            
            return [
                'favicon' => asset('favicon.ico'),
                'favicon_16' => null,
                'favicon_32' => null,
                'apple_touch' => null,
                'android_192' => null,
                'android_512' => null,
            ];
        } catch (\Exception $e) {
            return [
                'favicon' => asset('favicon.ico'),
                'favicon_16' => null,
                'favicon_32' => null,
                'apple_touch' => null,
                'android_192' => null,
                'android_512' => null,
            ];
        }
    }

    /**
     * Delete generated favicons
     */
    public function deleteFavicons(): void
    {
        $faviconDir = public_path('favicons');
        
        if (is_dir($faviconDir)) {
            $files = glob($faviconDir . '/*');
            foreach ($files as $file) {
                if (is_file($file)) {
                    unlink($file);
                }
            }
        }
    }
}
