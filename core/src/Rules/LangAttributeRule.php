<?php

declare(strict_types=1);

namespace A11yTool\Core\Rules;

use DOMDocument;

/**
 * WCAG 3.1.1 (Language of Page): ensures <html> has a lang attribute.
 */
final class LangAttributeRule implements Rule
{
    public function __construct(
        private readonly string $defaultLanguage = 'de',
    ) {
    }

    public function id(): string
    {
        return 'lang-attribute';
    }

    public function apply(DOMDocument $document): bool
    {
        $html = $document->getElementsByTagName('html')->item(0);

        if ($html === null) {
            return false;
        }

        if ($html->hasAttribute('lang') && $html->getAttribute('lang') !== '') {
            return false;
        }

        $html->setAttribute('lang', $this->defaultLanguage);

        return true;
    }
}
