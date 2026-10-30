<?php

namespace CedricKassen\Helpdesk\Policies;

use App\Policies\DefaultAdminPolicies;

class TicketPolicy
{
    use DefaultAdminPolicies;

    protected string $modelName = 'ticket';
}
