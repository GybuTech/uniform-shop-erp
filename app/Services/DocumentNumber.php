<?php

namespace App\Services;

use App\Models\Branch;

/**
 * Company-wide unique document numbers, prefixed with the branch code so that
 * receipts from different shops can never collide once they are combined at HQ.
 *
 *   MS-RCP-261124-4F9A2C   (receipt issued at the branch whose code is "MS")
 *
 * The branch code is the "Code" field of the branch (Settings → Branches).
 */
class DocumentNumber
{
    public static function make(string $type, ?string $branchName = null): string
    {
        $code = static::branchCode($branchName);

        return ($code !== '' ? $code . '-' : '')
            . strtoupper($type) . '-'
            . SystemTime::now()->format('ymd') . '-'
            . strtoupper(substr(bin2hex(random_bytes(4)), 0, 6));
    }

    public static function branchCode(?string $branchName): string
    {
        $branchName = trim((string) ($branchName ?: BranchContext::getActiveBranch()));
        if ($branchName === '') {
            return '';
        }

        $code = Branch::where('name', $branchName)->value('code');
        if (empty($code)) {
            // No code set yet: derive one from the branch name ("STALL 35" -> "STA")
            $code = substr(preg_replace('/[^A-Za-z0-9]/', '', $branchName), 0, 3);
        }

        return strtoupper(preg_replace('/[^A-Za-z0-9]/', '', $code));
    }
}
