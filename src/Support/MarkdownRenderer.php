<?php

namespace CedricKassen\Helpdesk\Support;

use League\CommonMark\GithubFlavoredMarkdownConverter;

class MarkdownRenderer
{
    /**
     * Convert (user-supplied) GitHub-flavored markdown to safe HTML.
     * Raw HTML is stripped and unsafe links are disallowed since ticket
     * messages are written by end users.
     */
    public static function toHtml(?string $markdown): string
    {
        if ($markdown === null || trim($markdown) === '') {
            return '';
        }

        $converter = new GithubFlavoredMarkdownConverter([
            'html_input' => 'strip',
            'allow_unsafe_links' => false,
        ]);

        return (string) $converter->convert($markdown);
    }
}
