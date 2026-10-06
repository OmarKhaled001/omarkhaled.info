<?php

namespace App\View\Components;

use App\Presenters\PublicProject;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

/**
 * Generated "print plate" artwork for anonymized projects: index number, category and a
 * small node diagram of the stack. Identity-safe, weightless and deterministic per project.
 */
class Plate extends Component
{
    /** @var list<array{x: float, y: float, label: string, accent: bool, labelDy: int}> */
    public array $nodes = [];

    /** @var list<array{0: int, 1: int}> */
    public array $edges = [];

    public string $number;

    public string $category;

    public function __construct(public PublicProject $project, public int $index = 1)
    {
        $this->number = str_pad((string) $index, 2, '0', STR_PAD_LEFT);
        $this->category = (string) ($project->categories()->first()?->getTranslation('name', 'en') ?? 'Platform');

        $labels = $project->technologies()->take(5)->pluck('name')->all() ?: ['Laravel'];
        $seed = crc32($project->slug());
        $count = count($labels);

        foreach (array_values($labels) as $i => $label) {
            // Spread nodes on a gentle arc; jitter derived from the slug keeps every plate unique but stable.
            $t = $count > 1 ? $i / ($count - 1) : 0.5;
            $jitter = (($seed >> ($i * 3)) & 15) - 7;
            $this->nodes[] = [
                'x' => round(340 + $t * 230, 1),
                'y' => round(290 - sin($t * M_PI) * 150 + $jitter * 4, 1),
                'label' => (string) $label,
                'accent' => $i === 0,
                // Alternate labels above/below so neighbours never collide.
                'labelDy' => $i % 2 === 0 ? 30 : -18,
            ];
        }

        for ($i = 1; $i < $count; $i++) {
            $this->edges[] = [$i - 1, $i];
        }
        if ($count > 2) {
            $this->edges[] = [0, $count - 1];
        }
    }

    public function render(): View
    {
        return view('components.plate');
    }
}
