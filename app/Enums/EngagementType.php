<?php

namespace App\Enums;

enum EngagementType: string
{
    case Solo = 'solo';
    case Client = 'client';
    case Employer = 'employer';

    public function label(): string
    {
        return __("enums.engagement.{$this->value}");
    }
}
