<?php

declare(strict_types=1);

namespace A11yTool\Core\Rules;

use DOMDocument;

interface Rule
{
    /**
     * Unique, stable identifier for this rule (e.g. "lang-attribute").
     */
    public function id(): string;

    /**
     * Applies the rule to the given document, mutating it in place
     * if a fix was made.
     *
     * @return bool true if the rule changed the document
     */
    public function apply(DOMDocument $document): bool;
}
