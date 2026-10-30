<?php

namespace CedricKassen\Helpdesk\Models;

use App\Models\Server;
use App\Models\User;
use Carbon\Carbon;
use CedricKassen\Helpdesk\Enums\TicketStatus;
use CedricKassen\Helpdesk\Notifications\TicketReplied;
use CedricKassen\Helpdesk\Notifications\TicketStatusChanged;
use CedricKassen\Helpdesk\Services\TicketWebhookService;
use Filament\Notifications\Notification;
use Filament\Support\Markdown;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * @property int $id
 * @property string $title
 * @property string $category
 * @property string $priority
 * @property TicketStatus $status
 * @property ?string $description
 * @property ?int $server_id
 * @property ?Server $server
 * @property ?int $author_id
 * @property ?User $author
 * @property ?int $assigned_user_id
 * @property ?User $assignedUser
 * @property ?Carbon $last_reply_at
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Ticket extends Model
{
    protected $fillable = [
        'title',
        'category',
        'priority',
        'status',
        'description',
        'server_id',
        'author_id',
        'assigned_user_id',
        'last_reply_at',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (self $model) {
            $model->status ??= TicketStatus::Open;
            $model->author_id ??= auth()->user()?->id;
        });
    }

    protected function casts(): array
    {
        return [
            // category and priority are admin-configurable, so they are stored as plain strings.
            'status' => TicketStatus::class,
            'last_reply_at' => 'datetime',
        ];
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class, 'server_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }

    public function messages(): HasMany
    {
        return $this->hasMany(TicketMessage::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TicketAttachment::class);
    }

    /**
     * Post a reply to the ticket.
     *
     * @param  string[]  $attachmentPaths  paths already stored on the attachment disk
     */
    public function reply(string $message, bool $internal = false, ?User $author = null, array $attachmentPaths = []): TicketMessage
    {
        $author ??= auth()->user();

        $ticketMessage = $this->messages()->create([
            'message' => $message,
            'is_internal' => $internal,
            'author_id' => $author?->id,
        ]);

        $this->recordAttachments($attachmentPaths, $ticketMessage);

        $this->last_reply_at = now();

        $isFromAuthor = $author && $this->author_id === $author->id;

        if (!$internal) {
            if ($isFromAuthor) {
                // The author replied: (re)open the ticket and put it back in the queue.
                $this->status = TicketStatus::Open;
            } else {
                // Staff replied: the ticket is now waiting for the author.
                $this->status = TicketStatus::Waiting;
            }
        }

        $this->save();

        if (!$internal) {
            if ($isFromAuthor) {
                app(TicketWebhookService::class)->ticketReplied($ticketMessage);
            } else {
                $this->notifyAuthorOfReply($ticketMessage);
            }
        }

        return $ticketMessage;
    }

    /**
     * @param  string[]  $attachmentPaths
     */
    public function close(?User $by = null, ?string $answer = null, array $attachmentPaths = []): void
    {
        if ($answer) {
            $message = $this->messages()->create([
                'message' => $answer,
                'is_internal' => false,
                'author_id' => ($by ?? auth()->user())?->id,
            ]);

            $this->recordAttachments($attachmentPaths, $message);
            $this->last_reply_at = now();
        }

        $this->status = TicketStatus::Closed;
        $this->save();

        $this->notifyAuthorOfStatus(TicketStatus::Closed, $answer);
    }

    public function reopen(?User $by = null): void
    {
        $this->status = TicketStatus::Open;
        $this->save();

        $this->notifyAuthorOfStatus(TicketStatus::Open);
    }

    public function assignTo(User $user, bool $setStatus = true): void
    {
        $this->assigned_user_id = $user->id;

        if ($setStatus && $this->status !== TicketStatus::Closed) {
            $this->status = TicketStatus::InProgress;
        }

        $this->save();
    }

    /**
     * Called from the user-facing create pages once a ticket has been created:
     * records the opening-post attachments and notifies staff via webhook.
     *
     * @param  string[]  $attachmentPaths
     */
    public function handleUserCreated(array $attachmentPaths = []): void
    {
        $this->recordAttachments($attachmentPaths, null);

        if ($this->last_reply_at === null) {
            $this->last_reply_at = $this->created_at;
            $this->save();
        }

        app(TicketWebhookService::class)->ticketCreated($this);
    }

    /**
     * @param  string[]  $paths
     */
    public function recordAttachments(array $paths, ?TicketMessage $message = null): void
    {
        $disk = config('helpdesk.attachment_disk', 'local');

        foreach (array_filter($paths) as $path) {
            $this->attachments()->create([
                'ticket_message_id' => $message?->id,
                'path' => $path,
                'name' => basename($path),
                'mime' => rescue(fn () => Storage::disk($disk)->mimeType($path), null, false),
                'size' => rescue(fn () => Storage::disk($disk)->size($path), null, false),
            ]);
        }
    }

    protected function notifyAuthorOfReply(TicketMessage $ticketMessage): void
    {
        if (!$this->author) {
            return;
        }

        if (config('helpdesk.email_enabled')) {
            $this->author->notify(new TicketReplied($this, $ticketMessage));
        }

        $this->sendDatabaseNotification(
            trans('helpdesk::tickets.notifications.new_reply', ['ticket' => $this->title]),
            $ticketMessage->message,
        );
    }

    protected function notifyAuthorOfStatus(TicketStatus $status, ?string $answer = null): void
    {
        if (!$this->author) {
            return;
        }

        if (config('helpdesk.email_enabled')) {
            $this->author->notify(new TicketStatusChanged($this, $status, $answer));
        }

        $title = $status === TicketStatus::Closed
            ? trans('helpdesk::tickets.notifications.closed', ['ticket' => $this->title])
            : trans('helpdesk::tickets.notifications.reopened', ['ticket' => $this->title]);

        $this->sendDatabaseNotification($title, $answer);
    }

    protected function sendDatabaseNotification(string $title, ?string $body): void
    {
        Notification::make()
            ->title($title)
            ->body($body ? Str::limit(strip_tags(Markdown::inline($body)), 120) : null)
            ->sendToDatabase($this->author);
    }
}
