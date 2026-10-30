<?php

namespace CedricKassen\Helpdesk\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TicketCategory: string implements HasColor, HasIcon, HasLabel
{
    case Question = 'question';
    case Issue = 'issue';
    case Feedback = 'feedback';
    case Billing = 'billing';
    case Other = 'other';

    public function getIcon(): string
    {
        return match ($this) {
            self::Question => 'tabler-help-circle',
            self::Issue => 'tabler-exclamation-circle',
            self::Feedback => 'tabler-message-circle',
            self::Billing => 'tabler-credit-card',
            self::Other => 'tabler-dots',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Question => 'primary',
            self::Issue => 'danger',
            self::Feedback => 'success',
            self::Billing => 'warning',
            self::Other => 'gray',
        };
    }

    public function getLabel(): string
    {
        return trans('helpdesk::tickets.categories.' . $this->value);
    }
}
