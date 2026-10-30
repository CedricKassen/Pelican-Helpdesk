<?php

namespace CedricKassen\Helpdesk\Http\Controllers;

use CedricKassen\Helpdesk\Models\TicketAttachment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TicketAttachmentController
{
    public function download(Request $request, TicketAttachment $attachment): StreamedResponse
    {
        $user = $request->user();
        $ticket = $attachment->ticket;

        abort_unless($user !== null && $ticket !== null, 404);

        $isStaff = $user->can('view ticket', $ticket);
        $isAuthor = $ticket->author_id === $user->id;

        // Internal-note attachments are staff-only.
        $isInternal = $attachment->message?->is_internal ?? false;

        abort_unless($isStaff || ($isAuthor && !$isInternal), 403);

        $disk = config('helpdesk.attachment_disk', 'local');

        abort_unless(Storage::disk($disk)->exists($attachment->path), 404);

        return Storage::disk($disk)->download($attachment->path, $attachment->name);
    }
}
