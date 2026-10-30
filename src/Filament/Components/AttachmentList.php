<?php

namespace CedricKassen\Helpdesk\Filament\Components;

use CedricKassen\Helpdesk\Models\TicketAttachment;
use Illuminate\Support\Collection;

class AttachmentList
{
    /**
     * Render a collection of TicketAttachment models as an HTML string of download links,
     * suitable for a Filament column/entry configured with ->html(). Returns null when empty.
     *
     * @param  Collection<int, TicketAttachment>  $attachments
     */
    public static function render(Collection $attachments): ?string
    {
        $links = $attachments->map(function ($attachment) {
            $url = e(route('helpdesk.attachment.download', $attachment));

            return '<a href="' . $url . '" style="text-decoration: underline">' . e($attachment->name) . '</a>';
        });

        if ($links->isEmpty()) {
            return null;
        }

        return '<div style="display: flex; flex-direction: column; gap: 0.25rem">' . $links->implode('') . '</div>';
    }
}
