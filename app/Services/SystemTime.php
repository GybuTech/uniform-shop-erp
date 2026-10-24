<?php

namespace App\Services;

use App\Models\BusinessSetting;
use Carbon\Carbon;

class SystemTime
{
    /**
     * Get the current authoritative operational business time.
     * Incorporates any official time offset set by the Owner/Admin.
     */
    public static function now(): Carbon
    {
        $timezone = config('app.timezone', 'Africa/Nairobi');
        $base = Carbon::now($timezone);
        $offset = (int) BusinessSetting::get('system_time_offset_seconds', 0);

        return $base->copy()->addSeconds($offset);
    }

    /**
     * Get start of current operational day.
     */
    public static function today(): Carbon
    {
        return static::now()->startOfDay();
    }

    /**
     * Formatted string of current operational system time.
     */
    public static function formatted(string $format = 'd M Y, h:i A'): string
    {
        return static::now()->format($format);
    }

    /**
     * Current offset in seconds.
     */
    public static function getOffsetSeconds(): int
    {
        return (int) BusinessSetting::get('system_time_offset_seconds', 0);
    }

    /**
     * Check if the system is running with an offset or on real network time.
     */
    public static function hasOffset(): bool
    {
        return static::getOffsetSeconds() !== 0;
    }

    /**
     * Set authoritative operational time to an explicit date and time string.
     */
    public static function setTargetDateTime(string $targetDateTimeString): void
    {
        $timezone = config('app.timezone', 'Africa/Nairobi');
        $realNow = Carbon::now($timezone);
        $target = Carbon::parse($targetDateTimeString, $timezone);

        $offsetSeconds = $target->diffInSeconds($realNow, false);
        // Note: target - realNow: if target is in the future, target > realNow, offset should be positive
        $difference = $target->timestamp - $realNow->timestamp;

        BusinessSetting::set('system_time_offset_seconds', (string) $difference);
    }

    /**
     * Reset operational time back to real Kenyan network time.
     */
    public static function resetToNetworkTime(): void
    {
        BusinessSetting::set('system_time_offset_seconds', '0');
    }
}
