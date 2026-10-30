<?php

namespace CedricKassen\Helpdesk\Providers;

use App\Models\Role;
use App\Models\Server;
use App\Models\Subuser;
use CedricKassen\Helpdesk\Models\Ticket;
use Illuminate\Support\ServiceProvider;

class HelpdeskPluginProvider extends ServiceProvider
{
    public function register(): void
    {
        Role::registerCustomDefaultPermissions('ticket');
        Role::registerCustomModelIcon('ticket', 'tabler-ticket');

        // Server-panel subuser permission ("ticket.view") that controls access
        // to a server's tickets. Server owners always have it; other subusers
        // must be granted it. Tickets belong to the server, not the subuser.
        Subuser::registerCustomPermissions('ticket', ['view'], null, 'tabler-ticket', false);
    }

    public function boot(): void
    {
        Server::resolveRelationUsing('supportTickets', fn (Server $server) => $server->hasMany(Ticket::class, 'server_id', 'id'));
    }
}
