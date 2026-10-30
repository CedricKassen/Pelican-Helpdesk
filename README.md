# Helpdesk

A full-featured, native support ticket system for the [Pelican Panel](https://pelican.dev).

## Features

- **Server-independent & server-specific tickets** – users can open a general ticket or one tied to a specific server.
- **Threaded conversations** – users and staff reply back and forth; tickets can be closed and reopened.
- **Internal notes** – staff can post hidden notes that the ticket author never sees.
- **Assignments, priorities, categories & statuses** – organise and triage tickets.
- **File attachments** – users and staff can attach files to the opening post and to replies. Attachments are stored on a private disk and streamed through an authorized download route.
- **Email notifications** – ticket authors are emailed whenever staff post a public reply or change the ticket status (uses the panel's configured mail transport).
- **In-panel notifications** – authors also receive a Filament bell notification.
- **Admin webhook** – configure a webhook URL in the plugin settings to be notified of new tickets and new user replies. Supports Discord embeds and generic JSON payloads.

## Panels

- **Client / Account panel (`app`)** – the main surface for users to create and manage their tickets (with an optional server selector).
- **Server panel (`server`)** – create and view tickets for the current server in-context.
- **Admin panel (`admin`)** – staff see and manage every ticket, reply, add internal notes, assign and change status.

## Configuration

Open the plugin settings in the admin panel to configure:

- **Email notifications** – toggle author emails on/off.
- **Webhook URL** – where to send new-ticket / new-reply notifications (leave empty to disable).
- **Webhook type** – `discord` or `generic` (auto-detected from the URL when left unset).

## Permissions

The plugin registers a `ticket` permission set. Grant the relevant `view`/`update`/`delete ticket` permissions to the roles that should handle tickets in the admin panel. End users can always manage their own tickets in the client and server panels.

## License

GPL-3.0. See [LICENSE](LICENSE).
