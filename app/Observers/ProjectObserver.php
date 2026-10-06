<?php

namespace App\Observers;

use App\Jobs\SyncProjectMediaVisibility;
use App\Models\Project;

class ProjectObserver
{
    public function saved(Project $project): void
    {
        if ($project->wasChanged(array_values(Project::IDENTIFIABLE_COLLECTIONS)) || $project->wasRecentlyCreated) {
            SyncProjectMediaVisibility::dispatch($project->getKey());
        }
    }
}
