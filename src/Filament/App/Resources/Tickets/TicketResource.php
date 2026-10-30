<?php

namespace CedricKassen\Helpdesk\Filament\App\Resources\Tickets;

use CedricKassen\Helpdesk\Filament\App\Resources\Tickets\Pages\CreateTicket;
use CedricKassen\Helpdesk\Filament\App\Resources\Tickets\Pages\ListTickets;
use CedricKassen\Helpdesk\Filament\App\Resources\Tickets\Pages\ViewTicket;
use CedricKassen\Helpdesk\Filament\App\Resources\Tickets\RelationManagers\MessagesRelationManager;
use CedricKassen\Helpdesk\Filament\Components\AttachmentList;
use CedricKassen\Helpdesk\Filament\Components\AttachmentUpload;
use CedricKassen\Helpdesk\Models\Ticket;
use CedricKassen\Helpdesk\Support\TicketOptions;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Markdown;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Auth\Access\Response;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static string|\BackedEnum|null $navigationIcon = 'tabler-ticket';

    protected static ?string $recordTitleAttribute = 'title';

    public static function getNavigationLabel(): string
    {
        return trans_choice('helpdesk::tickets.ticket', 2);
    }

    public static function getModelLabel(): string
    {
        return trans_choice('helpdesk::tickets.ticket', 1);
    }

    public static function getPluralModelLabel(): string
    {
        return trans_choice('helpdesk::tickets.ticket', 2);
    }

    // Users only ever see and manage their own tickets; the admin policy does not apply here.
    // Ownership is enforced by getEloquentQuery() below (records of other users do not resolve).
    public static function canViewAny(): bool
    {
        return true;
    }

    public static function getViewAnyAuthorizationResponse(): Response
    {
        return Response::allow();
    }

    public static function canView(Model $record): bool
    {
        return true;
    }

    public static function getViewAuthorizationResponse(Model $record): Response
    {
        return Response::allow();
    }

    public static function canCreate(): bool
    {
        return true;
    }

    public static function getCreateAuthorizationResponse(): Response
    {
        return Response::allow();
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('author_id', auth()->id());
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('#')
                    ->sortable(),
                TextColumn::make('title')
                    ->label(trans_choice('helpdesk::tickets.title', 1))
                    ->description(fn (Ticket $ticket) => Markdown::inline($ticket->description ?? ''))
                    ->searchable()
                    ->grow(),
                TextColumn::make('category')
                    ->label(trans('helpdesk::tickets.category'))
                    ->badge()
                    ->color(fn ($state) => TicketOptions::categoryColor($state))
                    ->formatStateUsing(fn ($state) => TicketOptions::categoryLabel($state))
                    ->toggleable(),
                TextColumn::make('status')
                    ->label(trans('helpdesk::tickets.status'))
                    ->badge(),
                TextColumn::make('server.name')
                    ->label(trans('helpdesk::tickets.server'))
                    ->icon('tabler-brand-docker')
                    ->placeholder(trans('helpdesk::tickets.no_server'))
                    ->toggleable(),
                TextColumn::make('last_reply_at')
                    ->label(trans('helpdesk::tickets.last_reply'))
                    ->since()
                    ->placeholder('-')
                    ->sortable(),
            ])
            ->recordUrl(fn (Ticket $ticket) => ViewTicket::getUrl(['record' => $ticket]))
            ->defaultSort('last_reply_at', 'desc')
            ->emptyStateIcon('tabler-ticket')
            ->emptyStateHeading(trans('helpdesk::tickets.no_tickets'));
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label(trans_choice('helpdesk::tickets.title', 1))
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull(),
                Select::make('category')
                    ->label(trans('helpdesk::tickets.category'))
                    ->required()
                    ->selectablePlaceholder(false)
                    ->options(TicketOptions::categoryOptions())
                    ->default(TicketOptions::defaultCategory()),
                Select::make('priority')
                    ->label(trans('helpdesk::tickets.priority'))
                    ->required()
                    ->selectablePlaceholder(false)
                    ->options(TicketOptions::priorityOptions())
                    ->default(TicketOptions::defaultPriority()),
                Select::make('server_id')
                    ->label(trans('helpdesk::tickets.server'))
                    ->helperText(trans('helpdesk::tickets.server_help'))
                    ->placeholder(trans('helpdesk::tickets.no_server'))
                    ->searchable()
                    ->options(fn () => auth()->user()->directAccessibleServers()->pluck('name', 'id'))
                    ->columnSpanFull(),
                MarkdownEditor::make('description')
                    ->label(trans('helpdesk::tickets.description'))
                    ->required()
                    ->columnSpanFull(),
                AttachmentUpload::make(),
            ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make()
                    ->columnSpanFull()
                    ->columns(['default' => 1, 'md' => 3])
                    ->schema([
                        TextEntry::make('category')
                            ->label(trans('helpdesk::tickets.category'))
                            ->badge()
                            ->color(fn ($state) => TicketOptions::categoryColor($state))
                            ->formatStateUsing(fn ($state) => TicketOptions::categoryLabel($state)),
                        TextEntry::make('priority')
                            ->label(trans('helpdesk::tickets.priority'))
                            ->badge()
                            ->color(fn ($state) => TicketOptions::priorityColor($state))
                            ->formatStateUsing(fn ($state) => TicketOptions::priorityLabel($state)),
                        TextEntry::make('status')
                            ->label(trans('helpdesk::tickets.status'))
                            ->badge(),
                        TextEntry::make('server.name')
                            ->label(trans('helpdesk::tickets.server'))
                            ->icon('tabler-brand-docker')
                            ->placeholder(trans('helpdesk::tickets.no_server')),
                    ]),
                Section::make(trans('helpdesk::tickets.description'))
                    ->columnSpanFull()
                    ->schema([
                        TextEntry::make('description')
                            ->hiddenLabel()
                            ->markdown()
                            ->placeholder(trans('helpdesk::tickets.no_description')),
                        TextEntry::make('opening_attachments')
                            ->label(trans('helpdesk::tickets.attachments'))
                            ->state(fn (Ticket $ticket) => AttachmentList::render($ticket->attachments->whereNull('ticket_message_id')->values()))
                            ->html()
                            ->hidden(fn (Ticket $ticket) => $ticket->attachments->whereNull('ticket_message_id')->isEmpty()),
                    ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTickets::route('/'),
            'create' => CreateTicket::route('/create'),
            'view' => ViewTicket::route('/{record}'),
        ];
    }

    public static function getRelations(): array
    {
        return [
            MessagesRelationManager::class,
        ];
    }
}
