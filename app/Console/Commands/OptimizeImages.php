<?php

namespace App\Console\Commands;

use App\Support\ImageOptimizer;
use Illuminate\Console\Command;
use Symfony\Component\Finder\Finder;

/**
 * One-off pass over images already uploaded, for the ones that predate the
 * resize now applied at upload time. Reports first and only writes when told
 * to, because it rewrites the originals in place.
 */
class OptimizeImages extends Command
{
    protected $signature = 'images:optimize
        {--dir=* : Folders under public/ to walk (defaults to the upload folders)}
        {--max= : Longest edge in pixels (default ' . ImageOptimizer::MAX_EDGE . ')}
        {--quality= : JPEG/WebP quality (default ' . ImageOptimizer::QUALITY . ')}
        {--apply : Actually rewrite the files; without it this only reports}';

    protected $description = 'Downscale and recompress uploaded images that are larger than the storefront can display';

    /** Where uploads land. Anything else has to be asked for with --dir. */
    private const DEFAULT_DIRS = [
        'images/product',
        'images/multi-pro',
        'images/variant',
        'images/settings',
        'images/category',
        'uploads/banners',
        'images/banners',
    ];

    public function handle(): int
    {
        $maxEdge = (int) ($this->option('max') ?: ImageOptimizer::MAX_EDGE);
        $quality = (int) ($this->option('quality') ?: ImageOptimizer::QUALITY);
        $apply = (bool) $this->option('apply');

        $dirs = collect($this->option('dir') ?: self::DEFAULT_DIRS)
            ->map(fn ($dir) => public_path(trim($dir, '/')))
            ->filter(fn ($dir) => is_dir($dir))
            ->values();

        if ($dirs->isEmpty()) {
            $this->error('None of those folders exist under public/.');

            return self::FAILURE;
        }

        $files = iterator_to_array(
            Finder::create()->files()->in($dirs->all())->name('/\.(jpe?g|png|webp)$/i'),
            false
        );

        if (! $files) {
            $this->info('No JPEG, PNG or WebP files found in those folders.');

            return self::SUCCESS;
        }

        $this->line(sprintf(
            '%s %d image%s — longest edge %dpx, quality %d.',
            $apply ? 'Optimising' : 'Checking',
            count($files),
            count($files) === 1 ? '' : 's',
            $maxEdge,
            $quality
        ));

        if (! $apply) {
            $this->comment('Dry run: nothing is written. Re-run with --apply to rewrite these files.');
        }

        $bar = $this->output->createProgressBar(count($files));
        $bar->start();

        $touched = 0;
        $before = 0;
        $after = 0;
        $worst = [];

        foreach ($files as $file) {
            $path = $file->getRealPath();
            $result = $apply
                ? ImageOptimizer::optimize($path, $maxEdge, $quality)
                : $this->wouldSave($path, $maxEdge, $quality);

            $bar->advance();

            if (! $result || $result['before'] === $result['after']) {
                continue;
            }

            $touched++;
            $before += $result['before'];
            $after += $result['after'];
            $worst[] = [
                'file' => str_replace(public_path() . DIRECTORY_SEPARATOR, '', $path),
                'saved' => $result['before'] - $result['after'],
            ];
        }

        $bar->finish();
        $this->newLine(2);

        if (! $touched) {
            $this->info('Every image is already within budget — nothing to do.');

            return self::SUCCESS;
        }

        usort($worst, fn ($a, $b) => $b['saved'] <=> $a['saved']);

        $this->table(
            ['Largest savings', 'Saved'],
            collect($worst)->take(10)->map(fn ($row) => [$row['file'], $this->size($row['saved'])])->all()
        );

        $this->info(sprintf(
            '%d image%s %s: %s → %s (%s saved, %d%%).',
            $touched,
            $touched === 1 ? '' : 's',
            $apply ? 'rewritten' : 'would be rewritten',
            $this->size($before),
            $this->size($after),
            $this->size($before - $after),
            (int) round(($before - $after) / max(1, $before) * 100)
        ));

        return self::SUCCESS;
    }

    /**
     * Dry run: encodes to a temporary copy so the reported saving is the real
     * one, then throws the copy away without touching the original.
     */
    private function wouldSave(string $path, int $maxEdge, int $quality): ?array
    {
        $copy = tempnam(sys_get_temp_dir(), 'imgopt') . '.' . pathinfo($path, PATHINFO_EXTENSION);

        if (! @copy($path, $copy)) {
            return null;
        }

        $result = ImageOptimizer::optimize($copy, $maxEdge, $quality);
        @unlink($copy);

        return $result;
    }

    private function size(int $bytes): string
    {
        return $bytes >= 1048576
            ? round($bytes / 1048576, 1) . ' MB'
            : round($bytes / 1024) . ' KB';
    }
}
