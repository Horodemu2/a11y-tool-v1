<?php

declare(strict_types=1);

namespace A11yTool\Core\Rules;

use DOMDocument;
use DOMElement;

/**
 * WCAG 2.4.1 (Bypass Blocks): ensures the page has a "skip to main content"
 * link as the first focusable element in <body>, pointing to <main>.
 */
final class SkipLinkRule implements Rule
{
    public function __construct(
        private readonly string $label = 'Zum Hauptinhalt springen',
    ) {
    }

    public function id(): string
    {
        return 'skip-link';
    }

    public function apply(DOMDocument $document): bool
    {
        $body = $document->getElementsByTagName('body')->item(0);
        $main = $document->getElementsByTagName('main')->item(0);

        if ($body === null || $main === null) {
            return false;
        }

        if ($this->hasSkipLink($document)) {
            return false;
        }

        $mainId = $main->getAttribute('id');
        if ($mainId === '') {
            $mainId = 'main-content';
            $main->setAttribute('id', $mainId);
        }

        /** @var DOMElement $link */
        $link = $document->createElement('a', $this->label);
        $link->setAttribute('href', '#' . $mainId);
        $link->setAttribute('class', 'a11y-skip-link');

        $body->insertBefore($link, $body->firstChild);

        return true;
    }

    private function hasSkipLink(DOMDocument $document): bool
    {
        foreach ($document->getElementsByTagName('a') as $anchor) {
            if ($anchor->getAttribute('class') === 'a11y-skip-link') {
                return true;
            }

            $href = $anchor->getAttribute('href');
            if (str_starts_with($href, '#') && $href !== '#') {
                return true;
            }
        }

        return false;
    }
}
