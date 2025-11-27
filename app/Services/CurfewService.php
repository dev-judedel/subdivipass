<?php

namespace App\Services;

use App\Models\Pass;
use App\Models\Subdivision;
use Carbon\Carbon;

class CurfewService
{
    /**
     * Check if current time is within curfew hours for a subdivision.
     * Handles overnight curfews (e.g., 22:00 to 05:00)
     *
     * @param Subdivision $subdivision
     * @param Carbon|null $checkTime
     * @return bool True if currently in curfew period
     */
    public function isWithinCurfew(Subdivision $subdivision, ?Carbon $checkTime = null): bool
    {
        // Use provided time or current time
        $checkTime = $checkTime ?? now();

        // If curfew is not enabled, return false (not in curfew)
        if (!$subdivision->curfew_enabled) {
            return false;
        }

        // Get curfew times from subdivision
        $curfewStart = $subdivision->curfew_start; // e.g., '22:00:00'
        $curfewEnd = $subdivision->curfew_end;     // e.g., '05:00:00'

        if (!$curfewStart || !$curfewEnd) {
            return false;
        }

        // Parse curfew times as time strings
        $startTime = Carbon::createFromTimeString($curfewStart);
        $endTime = Carbon::createFromTimeString($curfewEnd);

        // Get just the time component of check time for comparison
        $currentTime = Carbon::createFromTimeString($checkTime->format('H:i:s'));

        // Check if curfew spans midnight (e.g., 22:00 to 05:00)
        if ($endTime->lessThan($startTime)) {
            // Overnight curfew: in curfew if time >= start OR time < end
            return $currentTime->greaterThanOrEqualTo($startTime) ||
                   $currentTime->lessThan($endTime);
        } else {
            // Same-day curfew: in curfew if time >= start AND time < end
            return $currentTime->greaterThanOrEqualTo($startTime) &&
                   $currentTime->lessThan($endTime);
        }
    }

    /**
     * Check if a pass can be used at the current/specified time.
     * Considers:
     * 1. Pass date validity (valid_from to valid_to)
     * 2. Pass time restrictions (allowed_entry_time_start/end)
     * 3. Subdivision curfew (if enabled and pass not exempt)
     *
     * @param Pass $pass
     * @param Carbon|null $checkTime
     * @return array ['allowed' => bool, 'reason' => string, 'details' => array]
     */
    public function canUsePassNow(Pass $pass, ?Carbon $checkTime = null): array
    {
        $checkTime = $checkTime ?? now();
        $details = [];

        // 1. Check pass date validity
        if ($checkTime->lessThan($pass->valid_from)) {
            return [
                'allowed' => false,
                'reason' => 'Pass is not yet valid',
                'details' => [
                    'valid_from' => $pass->valid_from->format('Y-m-d H:i:s'),
                    'current_time' => $checkTime->format('Y-m-d H:i:s'),
                ],
            ];
        }

        if ($checkTime->greaterThan($pass->valid_to)) {
            return [
                'allowed' => false,
                'reason' => 'Pass has expired',
                'details' => [
                    'valid_to' => $pass->valid_to->format('Y-m-d H:i:s'),
                    'current_time' => $checkTime->format('Y-m-d H:i:s'),
                ],
            ];
        }

        // 2. Check pass time restrictions (if set)
        if ($pass->allowed_entry_time_start && $pass->allowed_entry_time_end) {
            $currentTime = Carbon::createFromTimeString($checkTime->format('H:i:s'));
            $allowedStart = Carbon::createFromTimeString($pass->allowed_entry_time_start);
            $allowedEnd = Carbon::createFromTimeString($pass->allowed_entry_time_end);

            $withinAllowedTime = false;

            // Check if time restriction spans midnight
            if ($allowedEnd->lessThan($allowedStart)) {
                // Overnight restriction: allowed if time >= start OR time < end
                $withinAllowedTime = $currentTime->greaterThanOrEqualTo($allowedStart) ||
                                    $currentTime->lessThan($allowedEnd);
            } else {
                // Same-day restriction: allowed if time >= start AND time < end
                $withinAllowedTime = $currentTime->greaterThanOrEqualTo($allowedStart) &&
                                    $currentTime->lessThan($allowedEnd);
            }

            if (!$withinAllowedTime) {
                return [
                    'allowed' => false,
                    'reason' => 'Outside allowed entry time',
                    'details' => [
                        'allowed_time' => $this->formatTimeRange(
                            $pass->allowed_entry_time_start,
                            $pass->allowed_entry_time_end
                        ),
                        'current_time' => $checkTime->format('g:i A'),
                    ],
                ];
            }
        }

        // 3. Check subdivision curfew (if not exempt)
        $subdivision = $pass->subdivision;

        if (!$this->isPassExemptFromCurfew($pass)) {
            if ($this->isWithinCurfew($subdivision, $checkTime)) {
                $curfewMessage = $subdivision->curfew_message ??
                    'Entry restricted during curfew hours';

                return [
                    'allowed' => false,
                    'reason' => $curfewMessage,
                    'details' => [
                        'curfew_hours' => $this->formatTimeRange(
                            $subdivision->curfew_start,
                            $subdivision->curfew_end
                        ),
                        'current_time' => $checkTime->format('g:i A'),
                    ],
                ];
            }
        }

        // All checks passed
        return [
            'allowed' => true,
            'reason' => 'Pass is valid for entry',
            'details' => [
                'valid_until' => $pass->valid_to->format('Y-m-d g:i A'),
            ],
        ];
    }

    /**
     * Validate pass for QR scan entry.
     * Comprehensive validation including all time checks.
     *
     * @param Pass $pass
     * @param Carbon|null $scanTime
     * @return array ['valid' => bool, 'message' => string, 'code' => string]
     */
    public function validatePassEntry(Pass $pass, ?Carbon $scanTime = null): array
    {
        $scanTime = $scanTime ?? now();

        // Check if pass is not yet valid
        if ($scanTime->lessThan($pass->valid_from)) {
            return [
                'valid' => false,
                'message' => sprintf(
                    'Pass will be valid starting %s',
                    $pass->valid_from->format('M j, Y g:i A')
                ),
                'code' => 'not_yet_valid',
            ];
        }

        // Check if pass is expired
        if ($scanTime->greaterThan($pass->valid_to)) {
            return [
                'valid' => false,
                'message' => sprintf(
                    'Pass expired on %s',
                    $pass->valid_to->format('M j, Y g:i A')
                ),
                'code' => 'expired',
            ];
        }

        // Check pass time restrictions
        if ($pass->allowed_entry_time_start && $pass->allowed_entry_time_end) {
            $currentTime = Carbon::createFromTimeString($scanTime->format('H:i:s'));
            $allowedStart = Carbon::createFromTimeString($pass->allowed_entry_time_start);
            $allowedEnd = Carbon::createFromTimeString($pass->allowed_entry_time_end);

            $withinAllowedTime = false;

            if ($allowedEnd->lessThan($allowedStart)) {
                // Overnight restriction
                $withinAllowedTime = $currentTime->greaterThanOrEqualTo($allowedStart) ||
                                    $currentTime->lessThan($allowedEnd);
            } else {
                // Same-day restriction
                $withinAllowedTime = $currentTime->greaterThanOrEqualTo($allowedStart) &&
                                    $currentTime->lessThan($allowedEnd);
            }

            if (!$withinAllowedTime) {
                $nextAllowed = $this->getNextAllowedEntryTime($pass, $scanTime);
                $timeRange = $this->formatTimeRange(
                    $pass->allowed_entry_time_start,
                    $pass->allowed_entry_time_end
                );

                return [
                    'valid' => false,
                    'message' => sprintf(
                        'Entry only allowed between %s. %s',
                        $timeRange,
                        $nextAllowed ? 'Try again at ' . $nextAllowed->format('g:i A') : ''
                    ),
                    'code' => 'time_restriction',
                ];
            }
        }

        // Check curfew
        $subdivision = $pass->subdivision;

        if (!$this->isPassExemptFromCurfew($pass)) {
            if ($this->isWithinCurfew($subdivision, $scanTime)) {
                $curfewMessage = $subdivision->curfew_message ??
                    'Entry restricted during curfew hours';

                $nextAllowed = $this->getNextAllowedEntryTime($pass, $scanTime);

                return [
                    'valid' => false,
                    'message' => sprintf(
                        '%s (%s). %s',
                        $curfewMessage,
                        $this->formatTimeRange($subdivision->curfew_start, $subdivision->curfew_end),
                        $nextAllowed ? 'Try again at ' . $nextAllowed->format('g:i A') : ''
                    ),
                    'code' => 'curfew_violation',
                ];
            }
        }

        // All validations passed
        return [
            'valid' => true,
            'message' => 'Pass is valid. Entry allowed.',
            'code' => 'success',
        ];
    }

    /**
     * Get curfew status message for display.
     *
     * @param Subdivision $subdivision
     * @param Carbon|null $time
     * @return string|null
     */
    public function getCurfewMessage(Subdivision $subdivision, ?Carbon $time = null): ?string
    {
        if (!$subdivision->curfew_enabled) {
            return null;
        }

        if ($this->isWithinCurfew($subdivision, $time)) {
            $customMessage = $subdivision->curfew_message;
            $timeRange = $this->formatTimeRange(
                $subdivision->curfew_start,
                $subdivision->curfew_end
            );

            return $customMessage
                ? sprintf('%s (%s)', $customMessage, $timeRange)
                : sprintf('Curfew hours: %s', $timeRange);
        }

        return null;
    }

    /**
     * Calculate next allowed entry time for a pass.
     * Useful for showing "Try again at X" messages.
     *
     * @param Pass $pass
     * @param Carbon|null $fromTime
     * @return Carbon|null
     */
    public function getNextAllowedEntryTime(Pass $pass, ?Carbon $fromTime = null): ?Carbon
    {
        $fromTime = $fromTime ?? now();
        $subdivision = $pass->subdivision;

        // Check if pass is expired
        if ($fromTime->greaterThan($pass->valid_to)) {
            return null;
        }

        // Start with fromTime
        $nextTime = $fromTime->copy();

        // If there are pass time restrictions, calculate next allowed time
        if ($pass->allowed_entry_time_start && $pass->allowed_entry_time_end) {
            $allowedStart = Carbon::createFromTimeString($pass->allowed_entry_time_start);
            $allowedEnd = Carbon::createFromTimeString($pass->allowed_entry_time_end);
            $currentTime = Carbon::createFromTimeString($fromTime->format('H:i:s'));

            if ($allowedEnd->lessThan($allowedStart)) {
                // Overnight restriction
                if ($currentTime->greaterThanOrEqualTo($allowedStart) || $currentTime->lessThan($allowedEnd)) {
                    // Already in allowed time, no need to calculate next
                } else {
                    // Between end and start - wait for start time
                    $nextTime = $fromTime->copy()->setTimeFromTimeString($pass->allowed_entry_time_start);
                }
            } else {
                // Same-day restriction
                if ($currentTime->lessThan($allowedStart)) {
                    // Before start time - wait for start
                    $nextTime = $fromTime->copy()->setTimeFromTimeString($pass->allowed_entry_time_start);
                } elseif ($currentTime->greaterThanOrEqualTo($allowedEnd)) {
                    // After end time - wait for start time tomorrow
                    $nextTime = $fromTime->copy()->addDay()->setTimeFromTimeString($pass->allowed_entry_time_start);
                }
            }
        }

        // If curfew is enabled and pass is not exempt, consider curfew
        if (!$this->isPassExemptFromCurfew($pass) && $subdivision->curfew_enabled) {
            $curfewStart = $subdivision->curfew_start;
            $curfewEnd = $subdivision->curfew_end;

            if ($curfewStart && $curfewEnd) {
                $startTime = Carbon::createFromTimeString($curfewStart);
                $endTime = Carbon::createFromTimeString($curfewEnd);
                $checkTime = Carbon::createFromTimeString($nextTime->format('H:i:s'));

                // If next time falls within curfew, move it to curfew end
                if ($endTime->lessThan($startTime)) {
                    // Overnight curfew
                    if ($checkTime->greaterThanOrEqualTo($startTime) || $checkTime->lessThan($endTime)) {
                        $nextTime = $nextTime->copy()->setTimeFromTimeString($curfewEnd);
                    }
                } else {
                    // Same-day curfew
                    if ($checkTime->greaterThanOrEqualTo($startTime) && $checkTime->lessThan($endTime)) {
                        $nextTime = $nextTime->copy()->setTimeFromTimeString($curfewEnd);
                    }
                }
            }
        }

        // Ensure next time doesn't exceed pass validity
        if ($nextTime->greaterThan($pass->valid_to)) {
            return null;
        }

        return $nextTime;
    }

    /**
     * Check if pass is exempt from curfew.
     * Checks both pass-level exemption and pass type exemption.
     *
     * @param Pass $pass
     * @return bool
     */
    public function isPassExemptFromCurfew(Pass $pass): bool
    {
        // Check pass-level exemption flag
        if ($pass->curfew_exempt) {
            return true;
        }

        // Check if pass type is in subdivision's curfew exemptions
        $subdivision = $pass->subdivision;

        if ($subdivision->curfew_exemptions) {
            $exemptTypeIds = is_array($subdivision->curfew_exemptions)
                ? $subdivision->curfew_exemptions
                : json_decode($subdivision->curfew_exemptions, true);

            if (is_array($exemptTypeIds) && in_array($pass->pass_type_id, $exemptTypeIds)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Format time range for display (handles overnight ranges).
     *
     * @param string $start Time string (HH:MM:SS or HH:MM)
     * @param string $end Time string (HH:MM:SS or HH:MM)
     * @return string Formatted time range (e.g., "10:00 PM - 5:00 AM")
     */
    public function formatTimeRange(string $start, string $end): string
    {
        try {
            $startCarbon = Carbon::createFromTimeString($start);
            $endCarbon = Carbon::createFromTimeString($end);

            return sprintf(
                '%s - %s',
                $startCarbon->format('g:i A'),
                $endCarbon->format('g:i A')
            );
        } catch (\Exception $e) {
            // Fallback if time parsing fails
            return sprintf('%s - %s', $start, $end);
        }
    }
}
