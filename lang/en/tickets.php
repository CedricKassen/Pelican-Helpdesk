<?php

return [
    'ticket' => 'Ticket|Tickets',
    'title' => 'Subject|Subjects',
    'message' => 'Message|Messages',

    'category' => 'Category',
    'priority' => 'Priority',
    'status' => 'Status',
    'description' => 'Description',
    'no_description' => 'No description provided',

    'server' => 'Server',
    'server_help' => 'Optionally link this ticket to one of your servers. Leave empty for a general request.',
    'no_server' => 'No server',

    'assigned_to' => 'Assigned to',
    'assigned_to_me' => 'Assigned to me',
    'created_by' => 'Created by',
    'replied_by' => 'Replied by',
    'owner' => 'Owner',
    'noone' => 'No one',
    'unknown' => 'Unknown',
    'last_reply' => 'Last reply',
    'created_at' => 'Created at',

    'no_tickets' => 'No tickets yet',

    'open' => 'Open',
    'closed' => 'Closed',
    'all' => 'All',

    'internal' => 'Internal',
    'author' => 'Author',
    'staff' => 'Staff',

    'reply' => 'Reply',
    'inline_placeholder' => 'Write a reply…',
    'send' => 'Send',
    'uploading' => 'Uploading…',
    'internal_note' => 'Internal note',
    'edit_message' => 'Edit message',
    'assign_to_me' => 'Assign to me',
    'close' => 'Close ticket',
    'closing_message' => 'Closing message (optional)',
    'reopen' => 'Reopen',
    'create' => 'New ticket',
    'attachments' => 'Attachments',

    'statuses' => [
        'open' => 'Open',
        'in_progress' => 'In progress',
        'waiting' => 'Waiting for reply',
        'closed' => 'Closed',
    ],

    'priorities' => [
        'low' => 'Low',
        'normal' => 'Normal',
        'high' => 'High',
        'very_high' => 'Very high',
    ],

    'categories' => [
        'question' => 'Question',
        'issue' => 'Issue',
        'feedback' => 'Feedback',
        'billing' => 'Billing',
        'other' => 'Other',
    ],

    'notifications' => [
        'new_reply' => 'New reply on ":ticket"',
        'closed' => 'Ticket ":ticket" was closed',
        'reopened' => 'Ticket ":ticket" was reopened',
        'reply_sent' => 'Your reply has been sent',
        'note_added' => 'Internal note added',
        'assigned_to_you' => 'Ticket assigned to you',
        'closed_success' => 'Ticket closed',
        'reopened_success' => 'Ticket reopened',
        'attachment_error' => 'Attachments must be at most 5 files, 10 MB each.',
    ],

    'mail' => [
        'reply_subject' => '[Ticket #:id] :title',
        'reply_line' => 'There is a new reply on your ticket ":title".',
        'closed_subject' => '[Ticket #:id] Closed: :title',
        'closed_line' => 'Your ticket ":title" has been closed.',
        'reopened_subject' => '[Ticket #:id] Reopened: :title',
        'reopened_line' => 'Your ticket ":title" has been reopened.',
        'view_action' => 'View ticket',
    ],

    'webhook' => [
        'created_title' => 'New ticket #:id: :title',
        'reply_title' => 'New reply on ticket #:id: :title',
    ],

    'settings' => [
        'email_section' => 'Email notifications',
        'email_enabled' => 'Notify ticket authors by email',
        'email_enabled_help' => 'Send an email to the ticket author when staff reply or change the ticket status.',
        'webhook_section' => 'Admin webhook',
        'webhook_url' => 'Webhook URL',
        'webhook_url_help' => 'Called when a user creates a ticket or posts a reply. Leave empty to disable.',
        'webhook_type' => 'Webhook format',
        'webhook_type_help' => 'Choose the payload format. Auto-detects Discord webhooks from the URL when left empty.',
        'webhook_type_discord' => 'Discord',
        'webhook_type_generic' => 'Generic JSON',
        'webhook_type_auto' => 'Auto-detect',

        'categories_section' => 'Ticket categories',
        'categories_help' => 'Define the categories users can choose when opening a ticket.',
        'add_category' => 'Add category',
        'priorities_section' => 'Ticket priorities',
        'priorities_help' => 'Define the priorities available for tickets.',
        'add_priority' => 'Add priority',
        'option_label' => 'Label',
        'option_color' => 'Color',

        'colors' => [
            'primary' => 'Blue',
            'success' => 'Green',
            'warning' => 'Orange',
            'danger' => 'Red',
            'info' => 'Cyan',
            'gray' => 'Gray',
        ],
    ],
];
