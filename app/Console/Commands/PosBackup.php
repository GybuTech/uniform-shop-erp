<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Database\Migrations\Migrator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Symfony\Component\Process\Process;

/**
 * Copies the shop's database to storage/backups. RUN_POS.bat calls it with --if-pending before it
 * applies database updates, so a shop can always go back to how it was before an update.
 */
class PosBackup extends Command
{
    protected $signature = 'pos:backup {--if-pending : Only back up when database updates are waiting to be applied} {--keep=20 : How many backups to keep}';

    protected $description = 'Back up this shop\'s database to storage/backups';

    public function handle(Migrator $migrator): int
    {
        if ($this->option('if-pending')) {
            $pending = $this->pendingMigrations($migrator);
            if ($pending === 0) {
                $this->line('Database is up to date: no backup needed.');
                return self::SUCCESS;
            }
            $this->line("{$pending} database update(s) waiting: backing up first.");
        }

        $dir = storage_path('backups');
        File::ensureDirectoryExists($dir);
        $stamp = now()->format('Y-m-d_His');
        $connection = config('database.default');
        $config = config("database.connections.{$connection}");

        try {
            $file = match ($config['driver']) {
                'sqlite' => $this->backupSqlite($config, "{$dir}/pos-{$stamp}.sqlite"),
                'mysql', 'mariadb' => $this->backupMysql($config, "{$dir}/pos-{$stamp}.sql"),
                default => throw new \RuntimeException("Backups are not set up for the {$config['driver']} database."),
            };
        } catch (\Throwable $e) {
            $this->error('BACKUP FAILED: ' . $e->getMessage());
            return self::FAILURE;
        }

        $this->info('Backup saved: ' . $file . ' (' . number_format(filesize($file) / 1024, 0) . ' KB)');
        $this->prune($dir, max(1, (int) $this->option('keep')));

        return self::SUCCESS;
    }

    private function pendingMigrations(Migrator $migrator): int
    {
        if (!$migrator->repositoryExists()) {
            return 0; // brand-new database: nothing to lose
        }
        $files = $migrator->getMigrationFiles(array_merge([database_path('migrations')], $migrator->paths()));
        $ran = $migrator->getRepository()->getRan();

        return count(array_diff(array_keys($files), $ran));
    }

    private function backupSqlite(array $config, string $target): string
    {
        $source = $config['database'];
        if (!is_file($source)) {
            throw new \RuntimeException("Database file not found: {$source}");
        }
        // VACUUM INTO writes a complete, consistent copy even if the file is in use
        DB::statement('VACUUM INTO ?', [$target]);
        if (!is_file($target) || filesize($target) === 0) {
            throw new \RuntimeException('The copy was not written.');
        }

        return $target;
    }

    private function backupMysql(array $config, string $target): string
    {
        $dump = $this->findMysqldump();
        $process = new Process([
            $dump,
            '--host=' . $config['host'], '--port=' . $config['port'], '--user=' . $config['username'],
            '--single-transaction', '--routines', '--default-character-set=utf8mb4',
            '--result-file=' . $target, $config['database'],
        ], null, ['MYSQL_PWD' => (string) $config['password']], null, 300);
        $process->run();

        if (!$process->isSuccessful() || !is_file($target) || filesize($target) === 0) {
            throw new \RuntimeException('mysqldump failed: ' . trim($process->getErrorOutput() ?: $process->getOutput()));
        }

        return $target;
    }

    private function findMysqldump(): string
    {
        foreach (['C:\\xampp\\mysql\\bin\\mysqldump.exe', 'D:\\xampp\\mysql\\bin\\mysqldump.exe'] as $path) {
            if (is_file($path)) {
                return $path;
            }
        }
        $finder = new \Symfony\Component\Process\ExecutableFinder();

        return $finder->find('mysqldump') ?? throw new \RuntimeException('mysqldump was not found (it comes with XAMPP in C:\\xampp\\mysql\\bin).');
    }

    private function prune(string $dir, int $keep): void
    {
        $files = collect(File::files($dir))
            ->filter(fn ($f) => str_starts_with($f->getFilename(), 'pos-'))
            ->sortByDesc(fn ($f) => $f->getFilename())
            ->values();

        $files->slice($keep)->each(fn ($f) => File::delete($f->getPathname()));
    }
}
