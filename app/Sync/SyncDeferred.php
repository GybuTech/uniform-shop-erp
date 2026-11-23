<?php

namespace App\Sync;

/**
 * A record arrived before something it depends on (e.g. a return before its sale).
 * Not an error: the shop keeps it in its outbox and sends it again on the next run.
 */
class SyncDeferred extends \RuntimeException
{
}
