<div>
    <div
        class="helpdesk-chat"
        x-data="{
            scrollDown() {
                const el = $refs.messages;
                if (el) { el.scrollTop = el.scrollHeight; }
            },
        }"
        x-init="$nextTick(() => scrollDown())"
        x-on:ticket-updated.window="$nextTick(() => setTimeout(() => scrollDown(), 50))"
    >
        <div class="helpdesk-chat-head">
            {{ trans_choice('helpdesk::tickets.message', 2) }}
        </div>

        <div class="helpdesk-chat-messages" x-ref="messages">
            {{ $this->table }}
        </div>

        <div
            class="helpdesk-composer"
            x-data="{
                text: '',
                resize() {
                    const el = $refs.input;
                    el.style.height = 'auto';
                    el.style.height = Math.min(el.scrollHeight, 180) + 'px';
                },
                send() {
                    if (! this.text.trim() && (! $wire.inlineAttachments || $wire.inlineAttachments.length === 0)) { return; }
                    $wire.sendInlineReply(this.text);
                    this.text = '';
                    $nextTick(() => this.resize());
                },
            }"
        >
            @if (! empty($inlineAttachments))
                <div class="helpdesk-composer-files">
                    @foreach ($inlineAttachments as $index => $file)
                        <span class="helpdesk-chip">
                            <x-filament::icon icon="tabler-paperclip" class="helpdesk-chip-icon" />
                            <span class="helpdesk-chip-name">{{ $file->getClientOriginalName() }}</span>
                            <button
                                type="button"
                                class="helpdesk-chip-remove"
                                wire:click="removeInlineAttachment({{ $index }})"
                                title="{{ trans('filament-actions::modal.actions.cancel.label') }}"
                            >
                                <x-filament::icon icon="tabler-x" class="helpdesk-chip-icon" />
                            </button>
                        </span>
                    @endforeach
                </div>
            @endif

            <div class="helpdesk-composer-bar">
                <input
                    type="file"
                    multiple
                    wire:model="inlineAttachments"
                    x-ref="fileInput"
                    style="display: none;"
                />

                <button
                    type="button"
                    class="helpdesk-composer-attach"
                    x-on:click="$refs.fileInput.click()"
                    title="{{ trans('helpdesk::tickets.attachments') }}"
                >
                    <x-filament::icon icon="tabler-paperclip" class="helpdesk-composer-attach-icon" />
                </button>

                <textarea
                    x-ref="input"
                    x-model="text"
                    rows="1"
                    class="helpdesk-composer-input"
                    placeholder="{{ trans('helpdesk::tickets.inline_placeholder') }}"
                    x-init="$nextTick(() => resize())"
                    x-on:input="resize()"
                    x-on:keydown.enter="if (! $event.shiftKey) { $event.preventDefault(); send(); }"
                ></textarea>

                <span class="helpdesk-composer-uploading" wire:loading wire:target="inlineAttachments">
                    {{ trans('helpdesk::tickets.uploading') }}
                </span>

                <button
                    type="button"
                    class="helpdesk-composer-send"
                    x-bind:disabled="! text.trim() && (! $wire.inlineAttachments || $wire.inlineAttachments.length === 0)"
                    x-on:click="send()"
                    title="{{ trans('helpdesk::tickets.send') }}"
                >
                    <x-filament::icon icon="tabler-send" class="helpdesk-composer-send-icon" />
                </button>
            </div>
        </div>
    </div>

    <x-filament-actions::modals />

    @once
        <style>
            .helpdesk-chat {
                display: flex;
                flex-direction: column;
                height: 72vh;
                min-height: 520px;
                max-height: 900px;
                border: 1px solid rgba(120, 120, 120, 0.3);
                border-radius: 0.75rem;
                overflow: hidden;
                background: #ffffff;
            }
            .dark .helpdesk-chat {
                background: rgba(255, 255, 255, 0.02);
                border-color: rgba(255, 255, 255, 0.12);
            }

            .helpdesk-chat-head {
                flex: 0 0 auto;
                padding: 0.75rem 1rem;
                font-weight: 600;
                font-size: 0.95rem;
                border-bottom: 1px solid rgba(120, 120, 120, 0.25);
                background: inherit;
            }

            .helpdesk-chat-messages {
                flex: 1 1 auto;
                min-height: 0;
                overflow-y: auto;
                /* No padding: the messages table runs edge to edge; its own
                   cell padding provides the inner spacing. */
                padding: 0;
            }
            /* Flatten the table's own card so it blends edge-to-edge into the
               chat box (the Filament version here has no Table::contained()).
               The card outline is a Tailwind ring, so box-shadow:none is what
               actually removes it. */
            .helpdesk-chat-messages > *,
            .helpdesk-chat-messages .fi-ta,
            .helpdesk-chat-messages .fi-ta-ctn,
            .helpdesk-chat-messages .fi-ta-content-ctn,
            .helpdesk-chat-messages .fi-ta-content {
                background: transparent !important;
                border: 0 !important;
                box-shadow: none !important;
                border-radius: 0 !important;
            }

            /* --- Sender indicators on the flat message list.
                   The table cells are transparent, so border/box-shadow on them
                   don't render; the row's own background does. Paint a thick
                   left bar as a background gradient on the <tr> + a subtle tint. */
            .helpdesk-msg td:first-child { padding-left: 1rem; }

            .helpdesk-msg-author {
                background-image: linear-gradient(to right, rgba(120, 120, 120, 0.7) 6px, transparent 6px) !important;
                background-repeat: no-repeat !important;
            }

            .helpdesk-msg-staff {
                background-color: rgba(37, 99, 235, 0.07) !important;
                background-image: linear-gradient(to right, #2563eb 6px, transparent 6px) !important;
                background-repeat: no-repeat !important;
            }
            .dark .helpdesk-msg-staff {
                background-color: rgba(59, 130, 246, 0.14) !important;
                background-image: linear-gradient(to right, #3b82f6 6px, transparent 6px) !important;
            }

            /* Internal staff notes (admin only). */
            .helpdesk-msg-internal {
                background-color: rgba(245, 158, 11, 0.1) !important;
                background-image: linear-gradient(to right, #f59e0b 6px, transparent 6px) !important;
                background-repeat: no-repeat !important;
            }
            .dark .helpdesk-msg-internal { background-color: rgba(245, 158, 11, 0.14) !important; }

            .helpdesk-composer {
                flex: 0 0 auto;
                border-top: 1px solid rgba(120, 120, 120, 0.25);
                padding: 0.5rem 0.75rem 0.625rem;
            }
            .helpdesk-composer-files {
                display: flex;
                flex-wrap: wrap;
                gap: 0.375rem;
                margin-bottom: 0.5rem;
            }
            .helpdesk-chip {
                display: inline-flex;
                align-items: center;
                gap: 0.25rem;
                max-width: 16rem;
                padding: 0.15rem 0.35rem 0.15rem 0.5rem;
                border-radius: 0.5rem;
                background: rgba(120, 120, 120, 0.15);
                font-size: 0.8rem;
            }
            .helpdesk-chip-name {
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }
            .helpdesk-chip-icon { width: 0.9rem; height: 0.9rem; flex: 0 0 auto; }
            .helpdesk-chip-remove {
                display: inline-flex;
                border: 0;
                background: transparent;
                color: inherit;
                opacity: 0.6;
                cursor: pointer;
            }
            .helpdesk-chip-remove:hover { opacity: 1; }

            .helpdesk-composer-bar {
                display: flex;
                align-items: flex-end;
                gap: 0.375rem;
                padding: 0.25rem 0.375rem 0.25rem 0.5rem;
                border: 1px solid rgba(120, 120, 120, 0.3);
                border-radius: 0.75rem;
                background: #ffffff;
            }
            .dark .helpdesk-composer-bar {
                background: rgba(255, 255, 255, 0.03);
                border-color: rgba(255, 255, 255, 0.15);
            }
            .helpdesk-composer-input {
                flex: 1 1 auto;
                min-width: 0;
                resize: none;
                border: 0;
                outline: none;
                background: transparent;
                color: inherit;
                font: inherit;
                line-height: 1.5;
                padding: 0.375rem 0;
                max-height: 180px;
                overflow-y: auto;
            }
            .helpdesk-composer-input::placeholder { color: rgba(120, 120, 120, 0.8); }

            .helpdesk-composer-attach,
            .helpdesk-composer-send {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                flex: 0 0 auto;
                width: 2.25rem;
                height: 2.25rem;
                border-radius: 0.5rem;
                border: 0;
                cursor: pointer;
                transition: opacity 0.15s ease, background 0.15s ease;
            }
            .helpdesk-composer-attach {
                background: transparent;
                color: inherit;
                opacity: 0.7;
            }
            .helpdesk-composer-attach:hover { opacity: 1; background: rgba(120, 120, 120, 0.12); }
            .helpdesk-composer-attach-icon { width: 1.2rem; height: 1.2rem; }

            .helpdesk-composer-send { background: #2563eb; color: #ffffff; }
            .dark .helpdesk-composer-send { background: #3b82f6; }
            .helpdesk-composer-send:hover { opacity: 0.9; }
            .helpdesk-composer-send:disabled { opacity: 0.4; cursor: not-allowed; }
            .helpdesk-composer-send-icon { width: 1.15rem; height: 1.15rem; }

            .helpdesk-composer-uploading {
                align-self: center;
                font-size: 0.8rem;
                opacity: 0.7;
                white-space: nowrap;
            }
        </style>
    @endonce
</div>
