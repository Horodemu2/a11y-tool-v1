<?php

declare(strict_types=1);

namespace A11yTool\Core\Engine;

use A11yTool\Core\Rules\Rule;
use DOMDocument;

final class Pipeline
{
    /** @var list<Rule> */
    private array $rules = [];

    public function addRule(Rule $rule): self
    {
        $this->rules[] = $rule;

        return $this;
    }

    /**
     * Runs all registered rules against the given HTML and returns the
     * (possibly corrected) HTML, along with the ids of rules that applied
     * a fix.
     *
     * @return array{html: string, applied: list<string>}
     */
    public function process(string $html): array
    {
        $document = new DOMDocument();

        $previousUseErrors = libxml_use_internal_errors(true);
        $document->loadHTML(
            $html,
            LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_HTML_NODEFDTD
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousUseErrors);

        $applied = [];
        foreach ($this->rules as $rule) {
            if ($rule->apply($document)) {
                $applied[] = $rule->id();
            }
        }

        return [
            'html' => $document->saveHTML() ?: $html,
            'applied' => $applied,
        ];
    }
}
