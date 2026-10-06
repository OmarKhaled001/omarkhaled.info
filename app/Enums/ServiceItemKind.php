<?php

namespace App\Enums;

enum ServiceItemKind: string
{
    case Deliverable = 'deliverable';
    case ProcessStep = 'process_step';
}
