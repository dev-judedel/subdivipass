<?php

namespace App\Services;

use App\Models\LoginActivity;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class LoginActivityService
{
    /**
     * Log a login attempt (success or failed).
     */
    public function logActivity(
        ?User $user,
        string $email,
        string $type,  // 'success', 'failed', 'locked_out'
        Request $request,
        ?string $failureReason = null
    ): LoginActivity {
        try {
            $userAgent = $request->userAgent() ?? 'Unknown';
            $deviceInfo = $this->parseUserAgent($userAgent);

            $data = [
                'user_id' => $user?->id,
                'email' => $email,
                'type' => $type,
                'ip_address' => $request->ip(),
                'user_agent' => $userAgent,
                'device_type' => $deviceInfo['device_type'],
                'browser' => $deviceInfo['browser'],
                'platform' => $deviceInfo['platform'],
                'failure_reason' => $failureReason,
                'logged_in_at' => now(),
            ];

            // Optional: Add geolocation data if available
            // This would require a geolocation service
            $data['country'] = null;
            $data['city'] = null;

            return LoginActivity::create($data);
        } catch (\Exception $e) {
            Log::error('Failed to log login activity', [
                'email' => $email,
                'type' => $type,
                'error' => $e->getMessage(),
            ]);

            // Return a minimal login activity record
            return LoginActivity::create([
                'user_id' => $user?->id,
                'email' => $email,
                'type' => $type,
                'ip_address' => $request->ip(),
                'user_agent' => 'Unknown',
                'device_type' => 'unknown',
                'browser' => 'Unknown',
                'platform' => 'Unknown',
                'failure_reason' => $failureReason,
                'logged_in_at' => now(),
            ]);
        }
    }

    /**
     * Parse user agent and detect device information.
     */
    protected function parseUserAgent(string $userAgent): array
    {
        // Try to use Jenssegers\Agent if available
        if (class_exists('\Jenssegers\Agent\Agent')) {
            return $this->parseWithAgent($userAgent);
        }

        // Fallback to basic parsing
        return $this->parseBasic($userAgent);
    }

    /**
     * Parse user agent using Jenssegers\Agent package.
     */
    protected function parseWithAgent(string $userAgent): array
    {
        try {
            $agent = new \Jenssegers\Agent\Agent();
            $agent->setUserAgent($userAgent);

            // Determine device type
            $deviceType = 'desktop';
            if ($agent->isRobot()) {
                $deviceType = 'bot';
            } elseif ($agent->isTablet()) {
                $deviceType = 'tablet';
            } elseif ($agent->isMobile()) {
                $deviceType = 'mobile';
            }

            return [
                'device_type' => $deviceType,
                'browser' => $agent->browser() ?: 'Unknown',
                'platform' => $agent->platform() ?: 'Unknown',
            ];
        } catch (\Exception $e) {
            Log::warning('Failed to parse user agent with Agent package', [
                'error' => $e->getMessage(),
            ]);
            return $this->parseBasic($userAgent);
        }
    }

    /**
     * Basic user agent parsing without external packages.
     */
    protected function parseBasic(string $userAgent): array
    {
        $userAgent = strtolower($userAgent);

        // Detect device type
        $deviceType = 'desktop';
        if (preg_match('/bot|crawler|spider|scraper/i', $userAgent)) {
            $deviceType = 'bot';
        } elseif (preg_match('/tablet|ipad/i', $userAgent)) {
            $deviceType = 'tablet';
        } elseif (preg_match('/mobile|android|iphone|ipod/i', $userAgent)) {
            $deviceType = 'mobile';
        }

        // Detect browser
        $browser = 'Unknown';
        if (preg_match('/edg\//i', $userAgent)) {
            $browser = 'Edge';
        } elseif (preg_match('/chrome/i', $userAgent)) {
            $browser = 'Chrome';
        } elseif (preg_match('/safari/i', $userAgent)) {
            $browser = 'Safari';
        } elseif (preg_match('/firefox/i', $userAgent)) {
            $browser = 'Firefox';
        } elseif (preg_match('/opera|opr\//i', $userAgent)) {
            $browser = 'Opera';
        } elseif (preg_match('/msie|trident/i', $userAgent)) {
            $browser = 'Internet Explorer';
        }

        // Detect platform
        $platform = 'Unknown';
        if (preg_match('/windows/i', $userAgent)) {
            $platform = 'Windows';
        } elseif (preg_match('/macintosh|mac os x/i', $userAgent)) {
            $platform = 'Mac';
        } elseif (preg_match('/linux/i', $userAgent)) {
            $platform = 'Linux';
        } elseif (preg_match('/iphone|ipad|ipod/i', $userAgent)) {
            $platform = 'iOS';
        } elseif (preg_match('/android/i', $userAgent)) {
            $platform = 'Android';
        }

        return [
            'device_type' => $deviceType,
            'browser' => $browser,
            'platform' => $platform,
        ];
    }

    /**
     * Get recent login activities for a user.
     */
    public function getRecentActivities(User $user, int $limit = 10): Collection
    {
        return LoginActivity::where('user_id', $user->id)
            ->orderBy('logged_in_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get failed login attempts in last X minutes.
     */
    public function getRecentFailedAttempts(string $email, int $minutes = 15): int
    {
        return LoginActivity::where('email', $email)
            ->where('type', 'failed')
            ->where('logged_in_at', '>=', now()->subMinutes($minutes))
            ->count();
    }

    /**
     * Check if login is from new device.
     */
    public function isNewDevice(User $user, string $userAgent): bool
    {
        $deviceInfo = $this->parseUserAgent($userAgent);

        // Check if this combination of device_type, browser, and platform exists
        $existingLogin = LoginActivity::where('user_id', $user->id)
            ->where('device_type', $deviceInfo['device_type'])
            ->where('browser', $deviceInfo['browser'])
            ->where('platform', $deviceInfo['platform'])
            ->where('type', 'success')
            ->exists();

        return !$existingLogin;
    }

    /**
     * Get login statistics for a user.
     */
    public function getStatistics(User $user): array
    {
        $activities = LoginActivity::where('user_id', $user->id)->get();

        $lastLogin = LoginActivity::where('user_id', $user->id)
            ->where('type', 'success')
            ->orderBy('logged_in_at', 'desc')
            ->first();

        return [
            'total_logins' => $activities->where('type', 'success')->count(),
            'failed_attempts' => $activities->where('type', 'failed')->count(),
            'unique_ips' => $activities->pluck('ip_address')->unique()->count(),
            'unique_devices' => $activities
                ->filter(function ($activity) {
                    return $activity->device_type && $activity->browser && $activity->platform;
                })
                ->map(function ($activity) {
                    return $activity->device_type . '|' . $activity->browser . '|' . $activity->platform;
                })
                ->unique()
                ->count(),
            'last_login' => $lastLogin ? $lastLogin->logged_in_at : null,
        ];
    }

    /**
     * Clean old login activities (older than X days).
     */
    public function cleanOldActivities(int $days = 90): int
    {
        $cutoffDate = now()->subDays($days);

        return LoginActivity::where('logged_in_at', '<', $cutoffDate)->delete();
    }
}
