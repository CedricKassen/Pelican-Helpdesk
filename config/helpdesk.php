<?php

return [
    // Email notifications to ticket authors when staff reply / change status
    'email_enabled' => (bool) env('HELPDESK_EMAIL_ENABLED', true),

    // Webhook that notifies admins/staff about new tickets and new user replies.
    // Leave the URL empty to disable webhook notifications.
    'webhook_url' => env('HELPDESK_WEBHOOK_URL'),

    // Either "discord" (sends a Discord embed) or "generic" (sends a plain JSON payload).
    // When set to null/empty it is auto-detected from the webhook URL host.
    'webhook_type' => env('HELPDESK_WEBHOOK_TYPE'),

    // Storage disk used for ticket attachments. Should be a private (non-public) disk;
    // attachments are streamed through an authorized download route.
    'attachment_disk' => env('HELPDESK_ATTACHMENT_DISK', 'local'),
];
