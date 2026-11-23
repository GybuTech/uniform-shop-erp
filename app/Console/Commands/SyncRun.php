<?php

namespace App\Console\Commands;

use App\Sync\SyncPusher;
use Illuminate\Console\Command;

class SyncRun extends Command
{
    protected $signature = 'sync:run';

    protected $description = 'Upload this shop\'s queued sales, shifts and stock changes to HQ (does nothing until sync is configured)';

    public function handle(SyncPusher $pusher): int
    {
        $result = $pusher->run();

        $this->line($result['message']);
        $this->line("Still waiting to upload: {$result['pending']} record(s).");

        return $result['status'] === 'offline' ? self::FAILURE : self::SUCCESS;
    }
}
