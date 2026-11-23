<?php

/*
|--------------------------------------------------------------------------
| Offline-first branch sync (see docs/Offline-Online Sync Design.md)
|--------------------------------------------------------------------------
|
| A SHOP PC runs with APP_STORE_MODE=standalone. It records every change in its
| outbox and, once SYNC_HQ_URL and SYNC_TOKEN are set, uploads them to HQ whenever
| the internet is available. Leave both empty until HQ is live: nothing is sent.
|
| HQ (the cloud copy on Railway) runs with APP_STORE_MODE=cloud and receives uploads
| at /api/sync/push. Each shop's SYNC_TOKEN is generated at HQ under
| Settings → Branches → "Generate sync key".
|
*/

return [
    // HQ address, e.g. https://tshaprint-pos.up.railway.app  (shop PCs only)
    'hq_url' => env('SYNC_HQ_URL'),

    // This shop's secret sync key, issued by HQ (shop PCs only)
    'token' => env('SYNC_TOKEN'),

    // Records per upload request
    'batch_size' => (int) env('SYNC_BATCH_SIZE', 150),

    // Seconds to wait for HQ before treating the connection as down
    'timeout' => (int) env('SYNC_TIMEOUT', 20),
];
