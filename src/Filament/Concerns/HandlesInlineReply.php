<?php

namespace CedricKassen\Helpdesk\Filament\Concerns;

use CedricKassen\Helpdesk\Models\TicketMessage;
use Filament\Notifications\Notification;
use Illuminate\Validation\ValidationException;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;
use Livewire\WithFileUploads;

/**
 * Chat-style inline composer for the message relation managers: a quick text
 * reply with its own file-upload support (no modal required).
 */
trait HandlesInlineReply
{
    use WithFileUploads;

    // Bound to the inline composer textarea.
    public string $inlineReply = '';

    /**
     * Pending inline uploads (Livewire temporary files), bound to the composer's
     * hidden file input.
     *
     * @var array<int, TemporaryUploadedFile>
     */
    public array $inlineAttachments = [];

    /**
     * Post a quick inline reply from the chat composer.
     *
     * The text is passed explicitly from Alpine so we do not depend on the
     * deferred wire:model having synced before the request fires.
     */
    public function sendInlineReply(?string $message = null): void
    {
        $text = trim($message ?? $this->inlineReply);

        if ($text === '' && $this->inlineAttachments === []) {
            return;
        }

        try {
            $this->validate([
                'inlineAttachments' => ['array', 'max:5'],
                'inlineAttachments.*' => ['file', 'max:10240'],
            ]);
        } catch (ValidationException) {
            Notification::make()
                ->title(trans('helpdesk::tickets.notifications.attachment_error'))
                ->danger()
                ->send();

            return;
        }

        $disk = config('helpdesk.attachment_disk', 'local');

        $paths = [];
        foreach ($this->inlineAttachments as $file) {
            $paths[] = $file->store('helpdesk', ['disk' => $disk, 'visibility' => 'private']);
        }

        $this->getOwnerRecord()->reply($text, false, auth()->user(), $paths);

        $this->inlineReply = '';
        $this->inlineAttachments = [];

        $this->dispatch('ticket-updated');

        Notification::make()
            ->title(trans('helpdesk::tickets.notifications.reply_sent'))
            ->success()
            ->send();
    }

    public function removeInlineAttachment(int $index): void
    {
        unset($this->inlineAttachments[$index]);

        $this->inlineAttachments = array_values($this->inlineAttachments);
    }

    /**
     * Row classes that drive the chat-bubble styling: the ticket author's own
     * messages sit on one side, staff replies on the other, internal notes get
     * their own variant.
     */
    protected function messageRowClasses(TicketMessage $message): string
    {
        $isStaff = $message->author_id && $message->author_id !== $this->getOwnerRecord()->author_id;

        $classes = ['helpdesk-msg', $isStaff ? 'helpdesk-msg-staff' : 'helpdesk-msg-author'];

        if ($message->is_internal) {
            $classes[] = 'helpdesk-msg-internal';
        }

        return implode(' ', $classes);
    }
}
