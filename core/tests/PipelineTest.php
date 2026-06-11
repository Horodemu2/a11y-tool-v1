<?php

declare(strict_types=1);

namespace A11yTool\Core\Tests;

use A11yTool\Core\Engine\Pipeline;
use A11yTool\Core\Rules\LangAttributeRule;
use A11yTool\Core\Rules\SkipLinkRule;
use PHPUnit\Framework\TestCase;

final class PipelineTest extends TestCase
{
    public function testAddsMissingLangAttribute(): void
    {
        $html = '<html><head><title>Test</title></head><body><main><p>Hello</p></main></body></html>';

        $pipeline = (new Pipeline())->addRule(new LangAttributeRule('de'));
        $result = $pipeline->process($html);

        $this->assertContains('lang-attribute', $result['applied']);
        $this->assertStringContainsString('<html lang="de">', $result['html']);
    }

    public function testDoesNotOverrideExistingLangAttribute(): void
    {
        $html = '<html lang="en"><head><title>Test</title></head><body><main><p>Hello</p></main></body></html>';

        $pipeline = (new Pipeline())->addRule(new LangAttributeRule('de'));
        $result = $pipeline->process($html);

        $this->assertSame([], $result['applied']);
        $this->assertStringContainsString('<html lang="en">', $result['html']);
    }

    public function testInsertsSkipLinkBeforeFirstBodyElement(): void
    {
        $html = '<html lang="de"><head><title>Test</title></head><body><nav>Menu</nav><main><p>Hello</p></main></body></html>';

        $pipeline = (new Pipeline())->addRule(new SkipLinkRule());
        $result = $pipeline->process($html);

        $this->assertContains('skip-link', $result['applied']);
        $this->assertStringContainsString('class="a11y-skip-link"', $result['html']);
        $this->assertStringContainsString('href="#main-content"', $result['html']);
        $this->assertStringContainsString('id="main-content"', $result['html']);

        // Skip link must come before <nav> in the body
        $skipPos = strpos($result['html'], 'a11y-skip-link');
        $navPos = strpos($result['html'], '<nav>');
        $this->assertLessThan($navPos, $skipPos);
    }

    public function testSkipLinkRuleDoesNothingWithoutMain(): void
    {
        $html = '<html lang="de"><head><title>Test</title></head><body><p>Hello</p></body></html>';

        $pipeline = (new Pipeline())->addRule(new SkipLinkRule());
        $result = $pipeline->process($html);

        $this->assertSame([], $result['applied']);
    }

    public function testCombinesMultipleRules(): void
    {
        $html = '<html><head><title>Test</title></head><body><main><p>Hello</p></main></body></html>';

        $pipeline = (new Pipeline())
            ->addRule(new LangAttributeRule('de'))
            ->addRule(new SkipLinkRule());

        $result = $pipeline->process($html);

        $this->assertSame(['lang-attribute', 'skip-link'], $result['applied']);
        $this->assertStringContainsString('<html lang="de">', $result['html']);
        $this->assertStringContainsString('a11y-skip-link', $result['html']);
    }
}
