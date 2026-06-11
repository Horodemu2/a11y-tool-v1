# a11y-tool — Concept

Status: 2026-06-11 (brainstorming phase, version 0.1)

## Core idea

**Positioning: Digital self-determination** — not "yet another WCAG
compliance tool", but a tool that gives people control over how they
perceive and use a page. Accessibility (WCAG 2.2) is the **core function**
and technical foundation — but the scope is broader: low vision, light
sensitivity, dyslexia, ADHD, screen reader use, motor impairments, overly
complex layouts — all of this should be compensated for.

No overlay widget (like Eye-Able) that gets placed on top of a finished
page afterwards. Instead: a lightweight component that hooks directly into
the **rendering pipeline** of existing frameworks — exactly the point where
templates (Blade, Twig, Jinja2, WordPress templates, ...) become real
HTML — and considers WCAG 2.2 from the ground up, instead of "fixing" it
afterwards.

## Guardrails

- **Lightweight**: no noticeable performance impact on existing pages
- **Non-invasive**: existing workflow/code remains untouched, the tool is
  purely additive, can be enabled/disabled via configuration
- **Cleanly embeddable**: one clearly defined hook point per framework, no
  deep interventions in application logic
- **No AI requirement in the hot path**: core works rule-based, synchronously,
  without external API calls on page load

## Architecture

```
┌─────────────────────────────────────────┐
│  Framework adapter (thin)                │
│  - Laravel/Blade: response middleware    │
│  - WordPress: the_content / output buffer│
│  - Python: WSGI/ASGI middleware          │
│  - more later (Twig, Node, ...)          │
└───────────────┬───────────────────────────┘
                │  rendered HTML
                ▼
┌─────────────────────────────────────────┐
│  Core engine (framework-agnostic)        │
│  - DOM parsing (single pass)             │
│  - rule set: check + auto-fix / lint     │
│  - caching (static parts only once)      │
└───────────────┬───────────────────────────┘
                │  corrected HTML / report
                ▼
            Response to client
```

### Modes

1. **Lint mode** (dev): violations are logged/displayed, including the
   location in the template and a concrete fix suggestion. No intervention
   in the output.
2. **Runtime correction mode** (prod): the engine adds/corrects markup live
   (e.g. setting missing ARIA attributes, fixing heading order).

Both modes can be used in parallel (e.g. lint in dev, correction in prod).

## Layer 2 — Self-determination layer (idea from colleague)

Building on the WCAG foundation from layer 1: a user-facing control panel
that lets visitors set their own needs — and the page adapts accordingly.
This works reliably because layer 1 already ensures correct semantics
(e.g. a real `<aside>`/`role="complementary"` for sidebars, real `<main>`
markup for main content) — no guessing like with classic overlay tools.

**Coverage of needs** (selectable in the control panel):
- Low vision (font size, contrast, zoom)
- Light sensitivity (dark mode, reduced brightness/saturation)
- Dyslexia (dyslexia-friendly font, line spacing)
- ADHD (focus mode, reduced stimuli)
- Screen reader (should already be covered by layer 1, possibly additional
  hints/shortcuts)
- Motor impairments (larger click targets, keyboard shortcuts)
- Overly complex layouts (simplified view)

**Step 2 — Set focus**
Hide distracting elements: ads, popups, animations, sidebars, and similar.
Works via the landmarks/roles established by layer 1 — "hide everything
except `<main>` and navigation" is robust because the structure is correct.

**Step 3 — Simplify text**
Legal text, applications/forms, complex wording → plain language. Requires
an LLM call (asynchronous, not in the hot path — e.g. on demand on click,
with caching of the result).

**Step 4 — Summarize text**
Long pages → table of contents/summary. Also LLM-powered, on demand +
caching.

## License model — update

Decision: **Layer 1 (WCAG foundation) and layer 2 (self-determination
layer incl. focus mode, text simplification, summarization) are fully
open source.** Differentiation for Pro/Enterprise happens **not via
features**, but via:

- Reporting/dashboard (violations & usage over time, per site/tenant)
- Multi-site management
- Support/SLA

The LLM-powered features (simplification, summarization) are also part of
the open-source core — operators bring their own LLM API key
(bring-your-own-key) to control cost/latency themselves. This strengthens
trustworthiness ("full transparency, no hidden paywall on core
accessibility functions").

## Configuration interface (idea: Umami-style dashboard)

So that operators without deep technical knowledge can make concrete
settings (rules on/off, configure layer 2 features, view reports) —
inspired by the simple, clean UX of Umami:

- **Core (open source)**: an embedded settings page in the respective
  framework admin (Laravel: Filament/Nova panel, WordPress: a dedicated
  admin page) — lightweight, no additional service/own database needed
- **Pro/Enterprise**: a standalone, Umami-style dashboard with a central
  overview across multiple sites, history/statistics on violations and
  usage of the self-determination features — this is the already planned
  "reporting/multi-site management" feature

## MVP

- **First adapter**: Laravel/Blade (dogfooding with pir-projekte)
- **Core**: designed as a standalone PHP package, but kept generic enough
  (pure HTML/DOM transformation, no Laravel dependencies in the core) that
  later adapters (WordPress, Python) can use or port the same core
- **Caching strategy**: hash of the rendered HTML fragment → cache the
  result, so recurring static components (header, footer, cards) aren't
  re-analyzed on every request

## License model (open core)

- **Open source (core)**: rule set, basic checks, basic corrections,
  Laravel adapter — freely usable, transparently auditable (important for
  the trustworthiness of an accessibility tool)
- **Pro/Enterprise**:
  - Reporting/dashboard (violations over time, per site/tenant)
  - Multi-site management
  - AI-powered features as an asynchronous additional pipeline (e.g.
    alt-text generation for images on upload, text simplification for
    cognitive impairments)
  - Additional framework adapters (WordPress, Python), possibly Pro first,
    later core
  - SLA/support

## Initial WCAG 2.2 rule list (draft)

### Safe to auto-fix (runtime fix)

- Set `lang` attribute on `<html>` if missing
- Automatically associate form labels (`for`/`id`, otherwise
  `aria-labelledby`)
- Add missing landmarks (`<main>`, `<nav>`, `role="navigation"` etc.) when
  semantic HTML is missing
- Insert a skip link if not present
- Set `aria-current` for active navigation items
- Add `aria-label` to empty links/buttons (heuristic: icon-only elements)
- Remove tabindex traps (`tabindex > 0` → `0` or removed)

### Lint only (hint + fix suggestion, no auto-fix)

- Heading hierarchy jumps (h1→h3) — suggestion, but the structural decision
  remains with the developer
- Missing `alt` attributes on images — hint; auto-generation only as a Pro
  AI feature (asynchronous, not in the hot path)
- Contrast issues (text/background) — calculation against actually applied
  CSS values, suggestion for color adjustment
- Link text quality ("click here", "more") — hint with context
- Target sizes < 24x24px (WCAG 2.2 / 2.5.8) — hint on affected elements
- Focus appearance (2.4.11) — check whether `:focus` styles are sufficiently
  visible
- Consistent help (3.2.6) — structural check whether help links/contact are
  positioned consistently
- Accessible authentication (3.3.8) — hint for cognitive tests in login
  forms (e.g. CAPTCHA without alternative)

## Open questions / notes

*(Space for further ideas while reading)*
