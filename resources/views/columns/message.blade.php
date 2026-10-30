<div class="helpdesk-prose">
    {!! \CedricKassen\Helpdesk\Support\MarkdownRenderer::toHtml($getState()) !!}
</div>

@once
    <style>
        /* Markdown typography */
        .helpdesk-prose { line-height: 1.5; word-break: break-word; }
        .helpdesk-prose > :first-child { margin-top: 0; }
        .helpdesk-prose > :last-child { margin-bottom: 0; }
        .helpdesk-prose p { margin: 0.5rem 0; }
        .helpdesk-prose h1 { font-size: 1.5rem; font-weight: 700; margin: 0.75rem 0 0.5rem; }
        .helpdesk-prose h2 { font-size: 1.3rem; font-weight: 700; margin: 0.75rem 0 0.5rem; }
        .helpdesk-prose h3 { font-size: 1.15rem; font-weight: 600; margin: 0.75rem 0 0.5rem; }
        .helpdesk-prose h4, .helpdesk-prose h5, .helpdesk-prose h6 { font-weight: 600; margin: 0.75rem 0 0.5rem; }
        .helpdesk-prose a { color: #2563eb; text-decoration: underline; }
        .dark .helpdesk-prose a { color: #60a5fa; }
        .helpdesk-prose ul { list-style: disc; padding-left: 1.5rem; margin: 0.5rem 0; }
        .helpdesk-prose ol { list-style: decimal; padding-left: 1.5rem; margin: 0.5rem 0; }
        .helpdesk-prose li { margin: 0.25rem 0; }
        .helpdesk-prose li > ul, .helpdesk-prose li > ol { margin: 0.25rem 0; }
        .helpdesk-prose blockquote { border-left: 3px solid rgba(120, 120, 120, 0.5); padding-left: 0.75rem; margin: 0.5rem 0; opacity: 0.85; font-style: italic; }
        .helpdesk-prose code { background: rgba(120, 120, 120, 0.18); padding: 0.1rem 0.3rem; border-radius: 0.25rem; font-size: 0.875em; }
        .helpdesk-prose pre { background: rgba(120, 120, 120, 0.18); padding: 0.75rem; border-radius: 0.375rem; overflow-x: auto; margin: 0.5rem 0; }
        .helpdesk-prose pre code { background: none; padding: 0; }
        .helpdesk-prose table { border-collapse: collapse; margin: 0.5rem 0; display: block; overflow-x: auto; }
        .helpdesk-prose th, .helpdesk-prose td { border: 1px solid rgba(120, 120, 120, 0.5); padding: 0.35rem 0.6rem; text-align: left; }
        .helpdesk-prose th { background: rgba(120, 120, 120, 0.15); font-weight: 600; }
        .helpdesk-prose hr { border: 0; border-top: 1px solid rgba(120, 120, 120, 0.4); margin: 0.75rem 0; }
        .helpdesk-prose img { max-width: 100%; height: auto; }
    </style>
@endonce
