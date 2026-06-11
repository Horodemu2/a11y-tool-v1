# CLAUDE.md — a11y-tool

This file provides guidance to Claude Code when working in this repository.

## Project Overview

**a11y-tool** — "Digital self-determination" accessibility engine. Hooks
into the rendering pipeline of existing frameworks (Laravel/Blade,
WordPress, Python) and ensures HTML output is WCAG 2.2-compliant and
adaptable, instead of patching it afterwards with an overlay.

Full concept: see [`CONCEPT.md`](CONCEPT.md) (German) /
[`CONCEPT-ENG.md`](CONCEPT-ENG.md) (English).

Two layers:

1. **Layer 1 — Accessibility foundation**: rule-based, automatic WCAG 2.2
   fixes applied to rendered HTML (e.g. `lang` attribute, skip links,
   landmarks, form labels).
2. **Layer 2 — Self-determination layer**: user-facing control panel
   (focus mode, text simplification, summarization, speech via the Web
   Speech API — never device-dependent libraries).

## Repository Layout

```
a11y-tool/
├── CONCEPT.md / CONCEPT-ENG.md   # design notes (source of truth for direction)
├── README.md                     # public-facing description
├── core/                          # PHP core package (a11y-tool/core, AGPL-3.0)
│   ├── composer.json
│   ├── src/Engine/Pipeline.php   # loads HTML, runs rules, returns corrected HTML
│   ├── src/Rules/                # one class per WCAG rule, implements Rule interface
│   └── tests/                    # PHPUnit tests
└── Niklas Projekt/                # Python prototypes/sketches (reference for Layer 2 logic)
```

## Conventions

- **PHP**: 8.2+, `declare(strict_types=1)`, PSR-4 (`A11yTool\Core\` →
  `core/src/`), PSR-12 style.
- **Rules**: each WCAG fix is its own class implementing
  `A11yTool\Core\Rules\Rule` (`id()`, `apply(DOMDocument $document): bool`).
  Add a corresponding test in `core/tests/`.
- **License**: AGPL-3.0-or-later for the open-source core. Both layers are
  open source — Pro/Enterprise differentiates via dashboard/multi-site/
  support, not features.
- **Composer**: use `composer` (installed at `C:\composer`, in system PATH).
  Run commands from `core/`.
- **Tests**: `php vendor\bin\phpunit tests` from `core/`.

## Git Workflow

- Claude only uses **read-only** git commands (`status`, `log`, `diff`).
- `add`, `commit`, `push`, `pull`, `reset`, `checkout`, `restore` etc. are
  performed by the user.
- When asked to "commit", fill in `COMMIT.md` with the commit message —
  do not run git commands.
