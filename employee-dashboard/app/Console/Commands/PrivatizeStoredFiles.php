<?php

namespace App\Console\Commands;

use App\Support\FileStorage;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PrivatizeStoredFiles extends Command
{
    protected $signature = 'storage:privatize
                            {--dry-run : Show what would move without changing any files}';

    protected $description = 'Move sensitive uploads (finance, messages, tasks, leave letters, journals, tower) from the public disk to private storage';

    public function handle(): int
    {
        $public = Storage::disk(FileStorage::LEGACY_DISK);
        $private = Storage::disk(FileStorage::DISK);
        $dryRun = (bool) $this->option('dry-run');

        $moved = 0;
        $alreadyPrivate = 0;

        foreach (FileStorage::SENSITIVE_PREFIXES as $prefix) {
            foreach ($public->allFiles($prefix) as $path) {
                if ($private->exists($path)) {
                    $this->line("already private, removing public copy: {$path}");

                    if (! $dryRun) {
                        $public->delete($path);
                    }

                    $alreadyPrivate++;

                    continue;
                }

                $this->line(($dryRun ? 'would move: ' : 'moving: ').$path);

                if ($dryRun) {
                    $moved++;

                    continue;
                }

                $stream = $public->readStream($path);

                if (! is_resource($stream)) {
                    $this->error("cannot read {$path}, skipping");
                    continue;
                }

                $private->writeStream($path, $stream);
                fclose($stream);
                $public->delete($path);

                $moved++;
            }
        }

        $this->info(sprintf(
            '%s%d file(s) moved, %d public copy/copies removed.',
            $dryRun ? 'Dry run: ' : '',
            $moved,
            $alreadyPrivate,
        ));

        return self::SUCCESS;
    }
}
