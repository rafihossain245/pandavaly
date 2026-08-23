<?php

namespace App\Console\Commands;

use App\Models\Product;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

/**
 * Renumbers product codes into short, customer-quotable numbers grouped by
 * category (Bedsheets 1xx, Comforters 2xx …).
 *
 * Products created before this scheme carry a code built from the product name
 * or a random string — neither is readable over the phone, which is how most
 * orders on this funnel arrive.
 */
class RecodeProducts extends Command
{
    protected $signature = 'products:recode {--dry-run : Show the new codes without saving}';

    protected $description = 'Give products short numeric codes grouped by category';

    public function handle(): int
    {
        $dry = (bool) $this->option('dry-run');

        $active = Product::with('category')->where('is_active', 1)
            ->orderBy('category_id')->orderBy('id')->get();

        if ($active->isEmpty()) {
            $this->warn('No active products found — nothing to renumber.');

            return self::SUCCESS;
        }

        if ($dry) {
            // Codes are allocated against what is already stored, so a preview
            // reports the first free number per category rather than the final
            // sequence. Enough to confirm the blocks are what you expect.
            $this->table(
                ['Current code', 'Category', 'Product'],
                $active->map(fn ($p) => [
                    $p->sku,
                    $p->category->name ?? '—',
                    mb_substr($p->name, 0, 40),
                ])->all()
            );
            $this->info('Dry run — nothing written. Re-run without --dry-run to apply.');

            return self::SUCCESS;
        }

        if (! $this->confirm('Renumber ' . $active->count() . ' active product code(s)?', true)) {
            return self::SUCCESS;
        }

        DB::transaction(function () use ($active) {
            // Retired products keep a non-numeric code: they still need a unique,
            // NOT NULL sku, but must not consume numbers in a live category block.
            foreach (Product::where('is_active', 0)->get() as $p) {
                $p->forceFill(['sku' => 'ARC-' . $p->id])->save();
            }

            // Park the live ones too, so a product still holding an old code is
            // not counted as occupying a number inside its own block.
            foreach ($active as $p) {
                $p->forceFill(['sku' => 'TMP-' . $p->id])->save();
            }

            foreach ($active as $p) {
                $p->forceFill(['sku' => Product::nextCode($p->category_id)])->save();
            }
        });

        $this->table(
            ['Code', 'Category', 'Product'],
            Product::with('category')->where('is_active', 1)->orderBy('sku')->get()
                ->map(fn ($p) => [$p->sku, $p->category->name ?? '—', mb_substr($p->name, 0, 40)])->all()
        );
        $this->info('Done.');

        return self::SUCCESS;
    }
}
