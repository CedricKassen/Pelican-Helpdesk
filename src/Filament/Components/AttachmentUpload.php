<?php

namespace CedricKassen\Helpdesk\Filament\Components;

use Filament\Forms\Components\FileUpload;

class AttachmentUpload
{
    public static function make(string $name = 'attachments'): FileUpload
    {
        return FileUpload::make($name)
            ->label(trans('helpdesk::tickets.attachments'))
            ->multiple()
            ->disk(config('helpdesk.attachment_disk', 'local'))
            ->directory('helpdesk')
            ->visibility('private')
            ->maxFiles(5)
            ->maxSize(10240)
            ->openable()
            ->downloadable()
            ->previewable(false)
            ->nullable()
            ->columnSpanFull();
    }
}
