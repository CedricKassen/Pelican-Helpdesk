<?php

namespace CedricKassen\Helpdesk\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property int $ticket_id
 * @property Ticket $ticket
 * @property ?int $ticket_message_id
 * @property ?TicketMessage $message
 * @property string $path
 * @property string $name
 * @property ?string $mime
 * @property ?int $size
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TicketAttachment extends Model
{
    protected $fillable = [
        'ticket_id',
        'ticket_message_id',
        'path',
        'name',
        'mime',
        'size',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(TicketMessage::class, 'ticket_message_id');
    }

    public function fileExists(): bool
    {
        return Storage::disk(config('helpdesk.attachment_disk', 'local'))->exists($this->path);
    }
}
