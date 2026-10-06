<?php

namespace App\Enums;

enum TechnologyDomain: string
{
    case Backend = 'backend';
    case Frontend = 'frontend';
    case AdminData = 'admin-data';
    case Integrations = 'integrations';
    case Infrastructure = 'infrastructure';
    case Design = 'design';

    public function label(): string
    {
        return __("enums.domain.{$this->value}");
    }
}
