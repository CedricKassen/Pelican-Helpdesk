<?php

return [
    'ticket' => 'Ticket|Tickets',
    'title' => 'Betreff|Betreffe',
    'message' => 'Nachricht|Nachrichten',

    'category' => 'Kategorie',
    'priority' => 'Priorität',
    'status' => 'Status',
    'description' => 'Beschreibung',
    'no_description' => 'Keine Beschreibung angegeben',

    'server' => 'Server',
    'server_help' => 'Verknüpfe dieses Ticket optional mit einem deiner Server. Für eine allgemeine Anfrage leer lassen.',
    'no_server' => 'Kein Server',

    'assigned_to' => 'Zugewiesen an',
    'assigned_to_me' => 'Mir zugewiesen',
    'created_by' => 'Erstellt von',
    'replied_by' => 'Beantwortet von',
    'owner' => 'Besitzer',
    'noone' => 'Niemand',
    'unknown' => 'Unbekannt',
    'last_reply' => 'Letzte Antwort',
    'created_at' => 'Erstellt am',

    'no_tickets' => 'Noch keine Tickets',

    'open' => 'Offen',
    'closed' => 'Geschlossen',
    'all' => 'Alle',

    'internal' => 'Intern',
    'author' => 'Autor',
    'staff' => 'Team',

    'reply' => 'Antworten',
    'inline_placeholder' => 'Antwort schreiben…',
    'send' => 'Senden',
    'uploading' => 'Wird hochgeladen…',
    'internal_note' => 'Interne Notiz',
    'edit_message' => 'Nachricht bearbeiten',
    'assign_to_me' => 'Mir zuweisen',
    'close' => 'Ticket schließen',
    'closing_message' => 'Abschlussnachricht (optional)',
    'reopen' => 'Wieder öffnen',
    'create' => 'Neues Ticket',
    'attachments' => 'Anhänge',

    'statuses' => [
        'open' => 'Offen',
        'in_progress' => 'In Bearbeitung',
        'waiting' => 'Wartet auf Antwort',
        'closed' => 'Geschlossen',
    ],

    'priorities' => [
        'low' => 'Niedrig',
        'normal' => 'Normal',
        'high' => 'Hoch',
        'very_high' => 'Sehr hoch',
    ],

    'categories' => [
        'question' => 'Frage',
        'issue' => 'Problem',
        'feedback' => 'Feedback',
        'billing' => 'Abrechnung',
        'other' => 'Sonstiges',
    ],

    'notifications' => [
        'new_reply' => 'Neue Antwort auf „:ticket“',
        'closed' => 'Ticket „:ticket“ wurde geschlossen',
        'reopened' => 'Ticket „:ticket“ wurde wieder geöffnet',
        'reply_sent' => 'Deine Antwort wurde gesendet',
        'note_added' => 'Interne Notiz hinzugefügt',
        'assigned_to_you' => 'Ticket dir zugewiesen',
        'closed_success' => 'Ticket geschlossen',
        'reopened_success' => 'Ticket wieder geöffnet',
        'attachment_error' => 'Anhänge dürfen höchstens 5 Dateien mit je 10 MB sein.',
    ],

    'mail' => [
        'reply_subject' => '[Ticket #:id] :title',
        'reply_line' => 'Es gibt eine neue Antwort auf dein Ticket „:title“.',
        'closed_subject' => '[Ticket #:id] Geschlossen: :title',
        'closed_line' => 'Dein Ticket „:title“ wurde geschlossen.',
        'reopened_subject' => '[Ticket #:id] Wieder geöffnet: :title',
        'reopened_line' => 'Dein Ticket „:title“ wurde wieder geöffnet.',
        'view_action' => 'Ticket ansehen',
    ],

    'webhook' => [
        'created_title' => 'Neues Ticket #:id: :title',
        'reply_title' => 'Neue Antwort auf Ticket #:id: :title',
    ],

    'settings' => [
        'email_section' => 'E-Mail-Benachrichtigungen',
        'email_enabled' => 'Ticket-Autoren per E-Mail benachrichtigen',
        'email_enabled_help' => 'Sende eine E-Mail an den Ticket-Autor, wenn das Team antwortet oder den Status ändert.',
        'webhook_section' => 'Admin-Webhook',
        'webhook_url' => 'Webhook-URL',
        'webhook_url_help' => 'Wird aufgerufen, wenn ein Benutzer ein Ticket erstellt oder antwortet. Zum Deaktivieren leer lassen.',
        'webhook_type' => 'Webhook-Format',
        'webhook_type_help' => 'Wähle das Payload-Format. Erkennt Discord-Webhooks automatisch an der URL, wenn leer.',
        'webhook_type_discord' => 'Discord',
        'webhook_type_generic' => 'Generisches JSON',
        'webhook_type_auto' => 'Automatisch erkennen',

        'categories_section' => 'Ticket-Kategorien',
        'categories_help' => 'Lege die Kategorien fest, die Benutzer beim Erstellen eines Tickets auswählen können.',
        'add_category' => 'Kategorie hinzufügen',
        'priorities_section' => 'Ticket-Prioritäten',
        'priorities_help' => 'Lege die für Tickets verfügbaren Prioritäten fest.',
        'add_priority' => 'Priorität hinzufügen',
        'option_label' => 'Bezeichnung',
        'option_color' => 'Farbe',

        'colors' => [
            'primary' => 'Blau',
            'success' => 'Grün',
            'warning' => 'Orange',
            'danger' => 'Rot',
            'info' => 'Cyan',
            'gray' => 'Grau',
        ],
    ],
];
