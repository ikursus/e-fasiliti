<?php

namespace App\Enums;

enum ChatRole: string
{
    case User = 'user';
    case Model = 'model';

    /**
     * Display label shown in the chat interface.
     */
    public function label(): string
    {
        return match ($this) {
            self::User => 'Anda',
            self::Model => 'Pembantu',
        };
    }
}
