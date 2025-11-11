<?php

namespace App\Services;

use App\Models\Pass;

class PassStatusService
{
    public function __construct(private PassService $passService)
    {
    }

    /**
     * Activate approved passes whose validity window has started.
     */
    public function activateEligiblePasses(): int
    {
        $passes = Pass::where('status', 'approved')
            ->where('valid_from', '<=', now())
            ->where('valid_to', '>=', now())
            ->get();

        $activated = 0;

        foreach ($passes as $pass) {
            $fromStatus = $pass->status;

            if ($pass->activate()) {
                $activated++;
                $this->passService->recordStatusChange($pass, $fromStatus, $pass->status, null, 'Automatically activated.');
                $this->passService->notifyStatusChange($pass, $fromStatus, $pass->status, 'Automatically activated.');
            }
        }

        return $activated;
    }

    /**
     * Expire passes whose validity window has elapsed.
     */
    public function expireElapsedPasses(): int
    {
        $passes = Pass::whereIn('status', ['approved', 'active'])
            ->where('valid_to', '<', now())
            ->get();

        $expired = 0;

        foreach ($passes as $pass) {
            $fromStatus = $pass->status;

            if ($pass->expire()) {
                $expired++;
                $this->passService->recordStatusChange($pass, $fromStatus, $pass->status, null, 'Automatically expired.');
                $this->passService->notifyStatusChange($pass, $fromStatus, $pass->status, 'Automatically expired.');
            }
        }

        return $expired;
    }

    /**
     * Forcefully terminate a pass ahead of schedule.
     */
    public function terminatePass(Pass $pass, ?string $reason = null): Pass
    {
        return $this->passService->terminatePass($pass, null, $reason);
    }
}
