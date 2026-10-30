<?php

use CedricKassen\Helpdesk\Http\Controllers\TicketAttachmentController;
use Illuminate\Support\Facades\Route;

Route::get('/helpdesk/attachments/{attachment}', [TicketAttachmentController::class, 'download'])
    ->name('helpdesk.attachment.download');
