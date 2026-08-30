# Feature: ACF development test theme

**From build-plan:** feature 1
**Status:** verified

## Goal

Create `acf-schema-guard-dev`, a deliberately small classic WordPress theme that
provides real ACF field definitions, Local JSON fixtures, and readable PHP call
sites for the future ACF Schema Guard plugin. It must remain safe and render a
useful fallback page when ACF or ACF PRO is unavailable.

## In scope

- A classic development-only theme at `wp-content/themes/acf-schema-guard-dev/`.
- Standard theme templates, minimal plain CSS, and protected PHP entry files.
- An `acf-json/` directory with an anti-directory-listing `index.php` and valid
  ACF Local JSON fixtures for hero, card, repeater, nested field, and flexible
  content scenarios.
- Conditional ACF PRO options-page registration, with an associated fixture only
  when the ACF runtime supports it.
- Realistic, literal ACF calls in templates: `get_field()`, `the_field()`,
  `get_sub_field()`, `the_sub_field()`, `have_rows()`, and
  `get_field_object()`.
- `docs/acf-test-scenarios.md` with the five requested breaking-change scenarios
  and concise manual setup and verification notes.

## Out of scope

- Any ACF Schema Guard plugin code, schema snapshots, diffing, risk logic,
  scanning implementation, Admin screens, WP-CLI commands, CI, or tests.
- Installing, licensing, activating, or configuring ACF or ACF PRO.
- A production theme, block theme, page builder, JavaScript framework, custom
  endpoint, form, or frontend build tool.
- A custom Local JSON path filter. Current ACF documentation confirms that an
  `acf-json` directory in the active theme is the default save and load point,
  so adding a redundant filter would obscure the behavior under test.

## Build loop

Build one step at a time. Each step must leave a valid, readable theme, show a
reviewable diff, and receive approval before the next step begins.

## Build steps

- [x] **Step 1 - Theme shell and safe fallback rendering** - create theme
  metadata, classic templates, theme setup, asset enqueueing, and minimal CSS.
  Use `ABSPATH` guards and escape all output. *Done when:* WordPress recognizes
  the theme, its templates render with no PHP syntax errors, and the site stays
  readable without ACF.
- [x] **Step 2 - Local JSON schema fixtures** - add the protected `acf-json/`
  directory and versioned field-group JSON fixtures for hero and cards, a
  repeater with `feature_title`, `feature_text`, and `feature_icon`, nested
  fields, flexible-content layouts (`hero`, `text_section`, `cards`), and an
  ACF PRO options-page fixture where supported. *Done when:* every fixture is
  valid JSON with stable ACF keys and the documented field names, and no custom
  Local JSON filter has been added.
- [x] **Step 3 - Scanner-oriented ACF template parts** - add focused template
  parts for hero, card, feature repeater, and flexible content. Use each named
  ACF API in normal template flow, guard unavailable ACF functions, and escape
  field output appropriately. *Done when:* source contains literal, realistic
  references to all required ACF APIs and field names without intentionally
  tangled control flow.
- [x] **Step 4 - Test scenarios and static verification** - document the
  requested breaking-change cases and manual ACF JSON sync checks. Run
  `php -l` for every theme PHP file and validate every JSON fixture with PHP's
  JSON parser. *Done when:* the scenario document names expected risk levels
  for renames, type changes, additions, repeater subfield removal, and image
  return-format changes, and all static checks pass.

## Files / areas

- `wp-content/themes/acf-schema-guard-dev/style.css`
- `wp-content/themes/acf-schema-guard-dev/functions.php`
- `wp-content/themes/acf-schema-guard-dev/{index,front-page,page,single,header,footer}.php`
- `wp-content/themes/acf-schema-guard-dev/template-parts/`
- `wp-content/themes/acf-schema-guard-dev/inc/`
- `wp-content/themes/acf-schema-guard-dev/assets/`
- `wp-content/themes/acf-schema-guard-dev/acf-json/`
- `docs/acf-test-scenarios.md`

## Data / contracts

- ACF fixture field names are a load-bearing contract for later snapshot and
  scanner features. They include `hero_title`, `hero_text`, `hero_image`,
  `hero_cta`, `card_title`, `card_text`, `card_image`, `card_link`, `features`,
  `feature_title`, `feature_text`, and `feature_icon`.
- Flexible-content layout names are `hero`, `text_section`, and `cards`.
- JSON uses stable `group_` and `field_` keys, versioned in the theme directory.
- ACF Local JSON relies on ACF's default active-theme `acf-json` save/load path.

## Testing

No unit or browser-test command is configured. Verification used `php -l` for
all theme PHP files and JSON parsing for all ACF fixtures. A manual WordPress
Admin path is documented in `docs/acf-test-scenarios.md`.

## Notes for the AI

- Follow `blueprint/context/coding-standards.md`, especially WordPress escaping,
  `ABSPATH` guards, and the `acf_schema_guard_dev_` function prefix.
- ACF documents the active theme's `acf-json/` directory as the default Local
  JSON save and load location. Do not add configuration solely to repeat that
  default. Add only the directory and its security placeholder.
- Keep field access explicit and searchable. Do not introduce a scanner,
  wrappers, regexes, AST tooling, or Schema Guard plugin code.
- Do not make assumptions about the minimum supported PHP version. Use broadly
  compatible WordPress PHP syntax.
