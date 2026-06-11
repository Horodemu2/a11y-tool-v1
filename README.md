# a11y-tool

> Digital self-determination, built into your stack. WCAG 2.2 fixes and
> user-controlled accessibility — applied where pages are rendered, not
> bolted on after.

**Status:** concept / pre-alpha — see [`CONCEPT.md`](CONCEPT.md) for the full design notes.

## What it is

Most "accessibility widgets" are overlays: a third-party script injected on
top of a finished page, trying to patch problems the browser and assistive
technologies have already parsed. They don't fix the underlying HTML, and
they don't actually give people control over how they experience a page.

a11y-tool takes a different approach: it hooks directly into the
**rendering pipeline** of existing frameworks — the point where templates
(Blade, Twig, Jinja2, WordPress templates, ...) become real HTML — and
ensures the output is accessible and adaptable from the start.

## Two layers

### Layer 1 — Accessibility foundation (WCAG 2.2)

Structural and semantic fixes applied to the rendered HTML output:

- Landmarks and roles (`<main>`, `<nav>`, `aria-*`)
- Form label associations
- Heading hierarchy checks
- Skip links
- Focus order and keyboard traps
- Contrast checks against actual rendered styles
- New WCAG 2.2 criteria (target size, focus appearance, consistent help, accessible authentication)

Runs automatically, benefits every visitor, no interaction required.

### Layer 2 — Self-determination layer

A user-facing control panel built on top of the correctly structured DOM
from Layer 1, letting visitors adapt the page to their own needs:

- **Profiles**: low vision, light sensitivity, dyslexia, ADHD, screen reader
  use, motor impairments, complex layouts
- **Focus mode**: hide ads, popups, animations, sidebars, and other
  distracting elements
- **Text simplification**: turn legal text and forms into plain language
- **Summarization**: condense long pages into a short overview

Layer 2 works reliably because Layer 1 already guarantees correct semantics
(e.g. a real `<aside role="complementary">` instead of guessing what a
"sidebar" looks like).

## Architecture

```
Framework adapter (thin)
  Laravel/Blade  -> response middleware
  WordPress      -> the_content / output buffer
  Python         -> WSGI/ASGI middleware
        |
        v  rendered HTML
Core engine (framework-agnostic)
  - single-pass DOM parsing
  - rule-based checks + auto-fixes
  - caching for static fragments
        |
        v  corrected HTML / report
Response to client
```

Design principles:

- **Lightweight** — no noticeable performance impact, no LLM calls in the hot path
- **Non-invasive** — purely additive, configurable, doesn't touch existing application code
- **Cleanly embeddable** — one well-defined hook point per framework

## License model

Both layers are **fully open source**. LLM-powered features (text
simplification, summarization) are bring-your-own-key, run on demand, and
cached.

Commercial offering is differentiated **not by features**, but by:

- A standalone, Umami-style multi-site dashboard (reporting, usage over time)
- Support / SLA

A lightweight settings page lives in the open-source core (e.g. a
Filament/Nova panel for Laravel, an admin page for WordPress) for
configuring rules and Layer 2 features without touching code.

## Roadmap (MVP)

1. Core engine as a standalone, framework-agnostic HTML/DOM transformer
2. Laravel/Blade adapter (dogfooded on real projects)
3. Layer 2 control panel (focus mode first, then text simplification/summarization)
4. WordPress and Python adapters

## Contributing

Not yet open for contributions — still in the concept phase. Watch this
space.
