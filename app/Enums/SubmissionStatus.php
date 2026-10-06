<?php

namespace App\Enums;

enum SubmissionStatus: string
{
    case New = 'new';
    case Read = 'read';
    case Replied = 'replied';
    case Spam = 'spam';

    public function label(): string
    {
        return ucfirst($this->value);
    }

    public function color(): string
    {
        return match ($this) {
            self::New => 'warning',
            self::Read => 'gray',
            self::Replied => 'success',
            self::Spam => 'danger',
        };
    }
}
