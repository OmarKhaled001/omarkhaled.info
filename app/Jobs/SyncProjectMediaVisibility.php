<?php

namespace App\Jobs;

use App\Models\Project;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

/**
 * Moves identifiable media (logo, screenshots) between the private disk and the public disk
 * so that hidden files have no public URL at all — confidentiality by storage, not by template.
 */
class SyncProjectMediaVisibility implements ShouldBeUnique, ShouldQueue
{
    use Queueable;

    public function __construct(public int $projectId) {}

    public function uniqueId(): string
    {
        return (string) $this->projectId;
    }

    public function handle(): void
    {
        $project = Project::query()->find($this->projectId);

        if (! $project) {
            return;
        }

        foreach (Project::IDENTIFIABLE_COLLECTIONS as $collection => $toggle) {
            $targetDisk = $project->{$toggle} ? 'public' : 'media_private';

            $project->getMedia($collection)
                ->reject(fn (Media $media) => $media->disk === $targetDisk)
                ->each(function (Media $media) use ($project, $collection, $targetDisk): void {
                    $media->move($project, $collection, $targetDisk);
                });
        }
    }
}
