# a11y-tool — Konzept

Stand: 2026-06-11 (Brainstorming-Phase, Version 0.1)

## Grundidee

**Positionierung: Digitale Selbstbestimmung** — nicht "noch ein
Compliance-Tool für WCAG", sondern ein Werkzeug, das Menschen die Kontrolle
darüber gibt, wie sie eine Seite wahrnehmen und nutzen. Barrierefreiheit
(WCAG 2.2) ist dabei die **Kernfunktion** und das technische Fundament —
aber der Rahmen ist breiter: schlechte Augen, Lichtempfindlichkeit,
Legasthenie, ADHS, Screenreader-Nutzung, motorische Einschränkungen, zu
komplexe Darstellungen — all das soll ausgeglichen werden können.

Kein Overlay-Widget (wie Eye-Able), das nachträglich über fertige Seiten gelegt wird.
Stattdessen: eine schlanke Komponente, die sich direkt in die **Rendering-Pipeline**
bestehender Frameworks einklinkt — also genau an der Stelle, an der aus
Templates (Blade, Twig, Jinja2, WordPress-Templates, ...) reales HTML wird —
und WCAG 2.2 von Grund auf mitdenkt, statt es im Nachhinein zu "reparieren".

## Leitplanken

- **Schlank**: keine spürbare Performance-Auswirkung auf bestehende Seiten
- **Nicht-invasiv**: bestehender Workflow/Code bleibt unangetastet, Tool ist additiv,
  per Konfiguration an-/abschaltbar
- **Sauber einbettbar**: ein klar definierter Hook-Punkt pro Framework, keine
  Eingriffe tief in die Anwendungslogik
- **Kein KI-Zwang im Hot Path**: Core arbeitet regelbasiert, synchron, ohne
  externe API-Calls beim Seitenaufruf

## Architektur

```
┌─────────────────────────────────────────┐
│  Framework-Adapter (dünn)                │
│  - Laravel/Blade: Response-Middleware    │
│  - WordPress: the_content / Output-Buffer│
│  - Python: WSGI/ASGI-Middleware          │
│  - weitere später (Twig, Node, ...)      │
└───────────────┬───────────────────────────┘
                │  rendertes HTML
                ▼
┌─────────────────────────────────────────┐
│  Core Engine (framework-agnostisch)      │
│  - DOM-Parsing (einmaliger Pass)         │
│  - Regelwerk: Check + Auto-Fix / Lint    │
│  - Caching (statische Teile nur 1x)      │
└───────────────┬───────────────────────────┘
                │  korrigiertes HTML / Report
                ▼
            Response an Client
```

### Modi

1. **Lint-Modus** (Dev): Verstöße werden geloggt/angezeigt, inkl. Fundstelle im
   Template und konkretem Fix-Vorschlag. Kein Eingriff in den Output.
2. **Runtime-Korrektur-Modus** (Prod): Engine ergänzt/korrigiert das Markup live
   (z.B. fehlende ARIA-Attribute setzen, Heading-Reihenfolge korrigieren).

Beide Modi parallel nutzbar (z.B. Lint in Dev, Korrektur in Prod).

## Schicht 2 — Selbstbestimmungs-Layer (Idee Kollege)

Aufbauend auf dem WCAG-Fundament aus Schicht 1: ein nutzerseitiges Bedienfeld,
mit dem Besucher:innen ihre eigenen Bedürfnisse einstellen — und die Seite
passt sich entsprechend an. Funktioniert zuverlässig, weil Schicht 1 bereits
für korrekte Semantik sorgt (z.B. echte `<aside>`/`role="complementary"`
für Sidebars, echte `<main>`-Markierung für Hauptinhalt) — kein Raten wie
bei klassischen Overlay-Tools.

**Abdeckung der Bedürfnisse** (Auswahl im Bedienfeld):
- Schlechte Augen (Schriftgröße, Kontrast, Zoom)
- Lichtempfindlichkeit (Dark Mode, reduzierte Helligkeit/Sättigung)
- Legasthenie (dyslexie-freundliche Schrift, Zeilenabstand)
- ADHS (Fokus-Modus, reduzierte Reize)
- Screenreader (sollte durch Schicht 1 ohnehin abgedeckt sein, ggf.
  zusätzliche Hinweise/Shortcuts)
- Motorische Einschränkungen (größere Klickflächen, Tastatur-Shortcuts)
- Zu komplexe Darstellung (vereinfachte Ansicht)

**Schritt 2 — Fokus setzen**
Störende Elemente ausblenden: Werbung, Popups, Animationen, Sidebars und
ähnliches. Funktioniert über die von Schicht 1 etablierten Landmarks/Rollen
— "blende alles außer `<main>` und Navigation aus" ist robust, weil die
Struktur stimmt.

**Schritt 3 — Text vereinfachen**
Juristische Texte, Anträge, komplexe Formulierungen → einfache Sprache.
Erfordert LLM-Call (asynchron, nicht im Hot Path — z.B. on-demand bei Klick,
mit Caching des Ergebnisses).

**Schritt 4 — Text zusammenfassen**
Lange Seiten → Inhaltsangabe/Zusammenfassung. Ebenfalls LLM-gestützt,
on-demand + Caching.

## Lizenzmodell — Update

Entscheidung: **Schicht 1 (WCAG-Fundament) und Schicht 2 (Selbstbestimmungs-
Layer inkl. Fokus-Modus, Textvereinfachung, Zusammenfassung) sind komplett
Open Source.** Differenzierung für Pro/Enterprise erfolgt **nicht über
Features**, sondern über:

- Reporting/Dashboard (Verstöße & Nutzung über Zeit, pro Seite/Mandant)
- Multi-Site-Management
- Support/SLA

Auch die LLM-gestützten Funktionen (Vereinfachung, Zusammenfassung) sind Teil
des Open-Source-Kerns — Betreiber bringen ihren eigenen LLM-API-Key mit
(Bring-your-own-key), um Kosten/Latenz selbst zu steuern. Dies stärkt die
Vertrauenswürdigkeit ("volle Transparenz, keine versteckte Paywall bei
Kernfunktionen der Barrierefreiheit").

## Konfigurationsoberfläche (Idee: Umami-artiges Dashboard)

Damit Betreiber:innen ohne tiefes technisches Wissen konkrete Einstellungen
vornehmen können (Regeln an/aus, Schicht-2-Funktionen konfigurieren,
Berichte einsehen) — angelehnt an die einfache, aufgeräumte UX von Umami:

- **Core (Open Source)**: eingebettete Einstellungsseite im jeweiligen
  Framework-Admin (Laravel: Filament/Nova-Panel, WordPress: eigene
  Admin-Seite) — schlank, kein zusätzlicher Service/keine eigene DB nötig
- **Pro/Enterprise**: eigenständiges, Umami-artiges Dashboard mit zentraler
  Übersicht über mehrere Sites, Verlauf/Statistiken zu Verstößen und
  Nutzung der Selbstbestimmungs-Funktionen — das ist das bereits geplante
  "Reporting/Multi-Site-Management"-Feature

## MVP

- **Erster Adapter**: Laravel/Blade (Dogfooding mit pir-projekte)
- **Core**: als eigenständiges PHP-Package konzipiert, aber so generisch
  gehalten (reine HTML/DOM-Transformation, keine Laravel-Abhängigkeiten im Kern),
  dass spätere Adapter (WordPress, Python) den gleichen Core nutzen oder
  portieren können
- **Caching-Strategie**: Hash des gerenderten HTML-Fragments → Ergebnis cachen,
  damit wiederkehrende statische Komponenten (Header, Footer, Cards) nicht bei
  jedem Request neu analysiert werden

## Lizenzmodell (Open Core)

- **Open Source (Core)**: Regelwerk, Basis-Checks, Basis-Korrekturen,
  Laravel-Adapter — frei nutzbar, transparent prüfbar (wichtig für
  Vertrauenswürdigkeit eines Accessibility-Tools)
- **Pro/Enterprise**:
  - Reporting/Dashboard (Verstöße über Zeit, pro Seite/Mandant)
  - Multi-Site-Management
  - KI-gestützte Features als asynchrone Zusatz-Pipeline (z.B. Alt-Text-Generierung
    für Bilder beim Upload, Textvereinfachung für kognitive Beeinträchtigungen)
  - Weitere Framework-Adapter (WordPress, Python) ggf. zuerst in Pro, später Core
  - SLA/Support

## Erste WCAG-2.2-Regelliste (Entwurf)

### Sicher automatisch korrigierbar (Runtime-Fix)

- `lang`-Attribut auf `<html>` setzen, falls fehlend
- Formular-Labels automatisch verknüpfen (`for`/`id`, sonst `aria-labelledby`)
- Fehlende Landmarks ergänzen (`<main>`, `<nav>`, `role="navigation"` etc.),
  wenn semantisches HTML fehlt
- Skip-Link einfügen, falls nicht vorhanden
- `aria-current` für aktive Navigationspunkte setzen
- Leere Links/Buttons mit `aria-label` versehen (Heuristik: Icon-only-Elemente)
- Tabindex-Fallen entfernen (`tabindex > 0` → `0` oder entfernen)

### Nur Lint (Hinweis + Fix-Vorschlag, kein Auto-Fix)

- Heading-Hierarchie-Sprünge (h1→h3) — Vorschlag, aber strukturelle Entscheidung
  bleibt beim Entwickler
- Fehlende `alt`-Attribute bei Bildern — Hinweis, Auto-Generierung nur als
  Pro-KI-Feature (asynchron, nicht im Hot Path)
- Kontrastprobleme (Text/Hintergrund) — Berechnung gegen tatsächlich
  angewendete CSS-Werte, Vorschlag für Farbanpassung
- Linktext-Qualität ("hier klicken", "mehr") — Hinweis mit Kontext
- Zielgrößen < 24x24px (WCAG 2.2 / 2.5.8) — Hinweis auf betroffene Elemente
- Fokus-Erscheinungsbild (2.4.11) — prüfen, ob `:focus`-Styles ausreichend
  sichtbar sind
- Konsistente Hilfe (3.2.6) — strukturelle Prüfung, ob Hilfe-Links/Kontakt
  konsistent positioniert sind
- Accessible Authentication (3.3.8) — Hinweis bei kognitiven Tests in
  Login-Formularen (z.B. CAPTCHA ohne Alternative)

## Offene Fragen / Notizen

*(Platz für weitere Ideen beim Lesen)*
