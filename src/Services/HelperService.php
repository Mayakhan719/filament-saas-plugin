<?php

// app/Services/HelperService.php
namespace Maya\FilamentSaasPlugin\Services;

use Maya\FilamentSaasPlugin\Models\Customization;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Container\Attributes\Auth;
use Illuminate\Support\Facades\Auth as FacadesAuth;

class HelperService
{
    /**
     * Get the logo URL, falling back to a default if not set
     */
    public function logo(): ?string
    {
        $custom = Customization::first() ?? new Customization();
        $logo = $custom->logo ?? null;
        return asset('storage/' . $logo);
    }
    public function favicon(): ?string
    {
        $custom = Customization::first() ?? new Customization();
        $favicon = $custom->favicon ?? null;
        return asset('storage/' . $favicon);
    }
    public function primaryColor(): ?string
    {
        $custom = Customization::first() ?? new Customization();
        $primaryColor = $custom->primary_color ?? null;
        return $primaryColor ?: '#6366f1'; // Default to Laravel's primary color
    }
    public function secondaryColor(): ?string
    {
        $custom = Customization::first() ?? new Customization();
        $secondaryColor = $custom->secondary_color ?? null;
        return $secondaryColor ?: '#4f46e5';
    }

    /**
     * Format currency with proper symbols and formatting
     */
    public function formatCurrency(float $amount, string $currency = 'USD', string $locale = 'en_US'): string
    {
        $formatter = new \NumberFormatter($locale, \NumberFormatter::CURRENCY);
        return $formatter->formatCurrency($amount, $currency);
    }

    /**
     * Generate a random string with specified length and type
     */
    public function generateRandomString(int $length = 10, string $type = 'alphanumeric'): string
    {
        switch ($type) {
            case 'alpha':
                return Str::random($length);
            case 'numeric':
                return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
            case 'alphanumeric':
            default:
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                return substr(str_shuffle(str_repeat($characters, ceil($length / strlen($characters)))), 0, $length);
        }
    }

    /**
     * Convert bytes to human readable format
     */
    public function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }

    /**
     * Calculate time difference in human readable format
     */
    public function timeAgo(string $datetime): string
    {
        return Carbon::parse($datetime)->diffForHumans();
    }

    /**
     * Validate and sanitize phone number
     */
    public function formatPhoneNumber(string $phone, string $countryCode = 'US'): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);

        // Format based on country code
        switch ($countryCode) {
            case 'US':
                if (strlen($phone) === 10) {
                    return sprintf(
                        '(%s) %s-%s',
                        substr($phone, 0, 3),
                        substr($phone, 3, 3),
                        substr($phone, 6, 4)
                    );
                }
                break;
            case 'UK':
                if (strlen($phone) === 11) {
                    return sprintf(
                        '%s %s %s',
                        substr($phone, 0, 4),
                        substr($phone, 4, 3),
                        substr($phone, 7, 4)
                    );
                }
                break;
        }

        return $phone;
    }

    /**
     * Generate a unique filename for file uploads
     */
    public function generateUniqueFilename(string $originalName, string $prefix = ''): string
    {
        $extension = pathinfo($originalName, PATHINFO_EXTENSION);
        $basename = pathinfo($originalName, PATHINFO_FILENAME);
        $timestamp = now()->format('Y-m-d_H-i-s');
        $randomString = $this->generateRandomString(8);

        return ($prefix ? $prefix . '_' : '') .
            $basename . '_' .
            $timestamp . '_' .
            $randomString .
            ($extension ? '.' . $extension : '');
    }

    /**
     * Slugify text for URLs
     */
    public function slugify(string $text, string $separator = '-'): string
    {
        return Str::slug($text, $separator);
    }

    /**
     * Extract initials from a full name
     */
    public function getInitials(string $name, int $maxLength = 2): string
    {
        $words = explode(' ', trim($name));
        $initials = '';

        foreach ($words as $word) {
            if (strlen($initials) < $maxLength && !empty($word)) {
                $initials .= strtoupper($word[0]);
            }
        }

        return $initials;
    }

    /**
     * Generate a color based on string (useful for avatars)
     */
    public function generateColorFromString(string $string): string
    {
        $colors = [
            '#FF6B6B',
            '#4ECDC4',
            '#45B7D1',
            '#96CEB4',
            '#FECA57',
            '#FF9FF3',
            '#54A0FF',
            '#5F27CD',
            '#00D2D3',
            '#FF9F43',
            '#10AC84',
            '#EE5A24',
            '#0984E3',
            '#A29BFE',
            '#FD79A8'
        ];

        $index = abs(crc32($string)) % count($colors);
        return $colors[$index];
    }

    /**
     * Clean and validate email address
     */
    public function sanitizeEmail(string $email): ?string
    {
        $email = strtolower(trim($email));
        return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
    }

    /**
     * Generate a secure token
     */
    public function generateSecureToken(int $length = 32): string
    {
        return bin2hex(random_bytes($length));
    }

    /**
     * Check if string is a valid JSON
     */
    public function isValidJson(string $string): bool
    {
        json_decode($string);
        return json_last_error() === JSON_ERROR_NONE;
    }

    /**
     * Convert array to CSV string
     */
    public function arrayToCsv(array $data, array $headers = []): string
    {
        $output = fopen('php://temp', 'r+');

        if (!empty($headers)) {
            fputcsv($output, $headers);
        }

        foreach ($data as $row) {
            fputcsv($output, $row);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }

    /**
     * Calculate percentage with precision
     */
    public function calculatePercentage(float $part, float $total, int $precision = 2): float
    {
        if ($total == 0) {
            return 0;
        }

        return round(($part / $total) * 100, $precision);
    }

    /**
     * Generate QR code data URL
     */
    public function generateQrCode(string $text, int $size = 200): string
    {
        // This is a simple implementation - in production, use a proper QR code library
        $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size={$size}x{$size}&data=" . urlencode($text);
        return $qrApiUrl;
    }

    /**
     * Mask sensitive data (like credit card numbers, phone numbers)
     */
    public function maskData(string $data, int $visibleStart = 4, int $visibleEnd = 4, string $maskChar = '*'): string
    {
        $length = strlen($data);

        if ($length <= $visibleStart + $visibleEnd) {
            return $data;
        }

        $start = substr($data, 0, $visibleStart);
        $end = substr($data, -$visibleEnd);
        $middle = str_repeat($maskChar, $length - $visibleStart - $visibleEnd);

        return $start . $middle . $end;
    }

    /**
     * Cache data with expiration
     */
    public function cacheData(string $key, $data, int $minutes = 60): bool
    {
        return Cache::put($key, $data, now()->addMinutes($minutes));
    }

    /**
     * Get cached data
     */
    public function getCachedData(string $key, $default = null)
    {
        return Cache::get($key, $default);
    }

    /**
     * Log activity with context
     */
    public function logActivity(string $message, array $context = [], string $level = 'info'): void
    {
        logger()->{$level}($message, array_merge($context, [
            'timestamp' => now()->toISOString(),
            'user_id' => FacadesAuth::id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]));
    }

    /**
     * Validate and format URL
     */
    public function validateUrl(string $url): ?string
    {
        // Add protocol if missing
        if (!preg_match('/^https?:\/\//', $url)) {
            $url = 'https://' . $url;
        }

        return filter_var($url, FILTER_VALIDATE_URL) ? $url : null;
    }

    /**
     * Generate breadcrumb from URL path
     */
    public function generateBreadcrumb(string $path = null): array
    {
        $path = $path ?: request()->path();
        $segments = explode('/', trim($path, '/'));
        $breadcrumb = [];
        $url = '';

        foreach ($segments as $segment) {
            if (!empty($segment)) {
                $url .= '/' . $segment;
                $breadcrumb[] = [
                    'title' => ucwords(str_replace(['-', '_'], ' ', $segment)),
                    'url' => $url
                ];
            }
        }

        return $breadcrumb;
    }
}
