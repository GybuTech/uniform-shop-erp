<?php

namespace App\Console\Commands;

use App\Models\Branch;
use App\Models\BusinessSetting;
use App\Models\Register;
use App\Models\User;
use App\Services\BranchContext;
use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\DB;

/**
 * Start-up health check shown in the RUN_POS.bat window. Lists anything that would stop the shop
 * from trading or print wrong details on receipts. Never changes anything.
 */
class PosCheck extends Command
{
    protected $signature = 'pos:check';

    protected $description = 'Check that this shop PC is ready to trade';

    private int $problems = 0;
    private int $warnings = 0;

    public function handle(Migrator $migrator): int
    {
        $this->line('Checking this POS...');

        if (empty(config('app.key'))) {
            $this->problem('APP_KEY is missing in .env (run: php artisan key:generate).');
        }

        try {
            DB::connection()->getPdo();
        } catch (\Throwable $e) {
            $this->problem('Cannot open the database: ' . $e->getMessage());
            return $this->finish();
        }

        if ($migrator->repositoryExists()) {
            $files = $migrator->getMigrationFiles([database_path('migrations')]);
            $pending = count(array_diff(array_keys($files), $migrator->getRepository()->getRan()));
            if ($pending > 0) {
                $this->problem("{$pending} database update(s) were not applied. Close this window and start the POS again; if it repeats, call support.");
            }
        } else {
            $this->problem('The database is empty (no tables). Run: php artisan migrate --seed');
            return $this->finish();
        }

        if (BranchContext::isStandalone()) {
            $name = trim((string) config('app.default_branch'));
            $branch = Branch::where('name', $name)->first();
            if (!$branch) {
                $this->problem("This PC is set to shop \"{$name}\" (STORE_DEFAULT_BRANCH in .env) but no branch has that name. Branches: "
                    . Branch::orderBy('name')->pluck('name')->join(', '));
            } else {
                if (!$branch->is_active) {
                    $this->problem("Shop \"{$name}\" is switched off in Settings -> Branches.");
                }
                if (class_exists(Register::class) && \Illuminate\Support\Facades\Schema::hasTable('registers')) {
                    if (!Register::where('branch_id', $branch->id)->exists()) {
                        $this->warn_("Shop \"{$name}\" has no till yet; one is created when the first shift opens.");
                    }
                }
                if (!DB::table('branch_stocks')->where('branch_id', $branch->id)->where('stock_quantity', '>', 0)->exists()) {
                    $this->warn_("Shop \"{$name}\" has no stock recorded: the till will show 0 for everything until stock is added.");
                }
                if (empty($branch->address) || empty($branch->phone)) {
                    $this->warn_("Shop \"{$name}\" has no address or phone: add them in Settings -> Branches (they print on receipts).");
                } elseif (in_array(trim($branch->phone), ['+254 700 000 000', '0700000000'], true)) {
                    $this->warn_("Shop \"{$name}\" still has the sample phone {$branch->phone}: set the real one in Settings -> Branches.");
                }
            }
        }

        if (!User::where('is_active', true)->exists()) {
            $this->problem('There is no active staff login.');
        }

        $placeholders = [
            'business_name'  => ['Commercial Retail POS', 'Laravel'],
            'business_phone' => ['+254 700 000 000'],
        ];
        foreach ($placeholders as $key => $defaults) {
            $value = trim((string) BusinessSetting::get($key, ''));
            if ($value === '' || in_array($value, $defaults, true)) {
                $this->warn_("Receipts still show a sample {$key} (\"{$value}\"): set it in Settings -> Business Info.");
            }
        }

        if (!is_file(public_path('build/manifest.json'))) {
            $this->problem('Screen styles are missing (public/build). Copy the full project folder again, including public/build.');
        }
        if (!is_writable(storage_path('framework/sessions')) || !is_writable(storage_path('logs'))) {
            $this->problem('The storage folder is read-only; staff could not log in.');
        }
        if (config('app.debug') && app()->environment('production')) {
            $this->warn_('APP_DEBUG=true on a production setup shows error details to users.');
        }

        return $this->finish();
    }

    private function problem(string $message): void
    {
        $this->problems++;
        $this->error('  PROBLEM: ' . $message);
    }

    private function warn_(string $message): void
    {
        $this->warnings++;
        $this->warn('  CHECK:   ' . $message);
    }

    private function finish(): int
    {
        if ($this->problems === 0 && $this->warnings === 0) {
            $this->info('  All checks passed. Ready to trade.');
        } elseif ($this->problems === 0) {
            $this->info("  Ready to trade ({$this->warnings} thing(s) to tidy up above).");
        }

        return $this->problems > 0 ? self::FAILURE : self::SUCCESS;
    }
}
