<?php

namespace CedricKassen\Helpdesk\Models;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

/**
 * @property int $id
 * @property string $message
 * @property bool $is_internal
 * @property int $ticket_id
 * @property Ticket $ticket
 * @property ?int $author_id
 * @property ?User $author
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class TicketMessage extends Model
{
    protected $fillable = [
        'message',
        'is_internal',
        'ticket_id',
        'author_id',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->author_id ??= auth()->user()?->id;
        });

        // Remove attachment files from disk when a message is deleted (the DB
        // rows are cleaned up by the foreign-key cascade).
        static::deleting(function (self $model) {
            $disk = config('helpdesk.attachment_disk', 'local');

            foreach ($model->attachments as $attachment) {
                rescue(fn () => Storage::disk($disk)->delete($attachment->path), null, false);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'is_internal' => 'boolean',
        ];
    }

    public function ticket(): BelongsTo
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }
}
