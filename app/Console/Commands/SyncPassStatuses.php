<?php

namespace App\Console\Commands;

use App\Services\PassStatusService;
use Illuminate\Console\Command;

class SyncPassStatuses extends Command
{
    protected $signature = 'passes:sync-statuses';

    protected $description = 'Activate or expire passes based on their validity window.';

    public function handle(PassStatusService $service): int
    {
        $activated = $service->activateEligiblePasses();
        $expired = $service->expireElapsedPasses();

        $this->info("Activated {$activated} passes.");
        $this->info("Expired {$expired} passes.");

        return Command::SUCCESS;
    }
}
