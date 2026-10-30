<?php

namespace CedricKassen\Helpdesk\Filament\Server\Resources\Tickets\Pages;

use CedricKassen\Helpdesk\Filament\Server\Resources\Tickets\TicketResource;
use Filament\Facades\Filament;
use Filament\Resources\Pages\CreateRecord;

class CreateTicket extends CreateRecord
{
    protected static string $resource = TicketResource::class;

    protected static bool $canCreateAnother = false;

    /** @var string[] */
    protected array $attachmentPaths = [];

    protected function getHeaderActions(): array
    {
        return [
            $this->getCreateFormAction()->formId('form')
                ->label(trans('helpdesk::tickets.create'))
                ->icon('tabler-plus'),
        ];
    }

    protected function getFormActions(): array
    {
        return [];
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $this->attachmentPaths = $data['attachments'] ?? [];
        unset($data['attachments']);

        $data['author_id'] = auth()->id();
        $data['server_id'] = Filament::getTenant()?->getKey();

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->record->handleUserCreated($this->attachmentPaths);
    }
}
