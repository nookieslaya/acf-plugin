# Editions and licensing

## Product model

ACF Schema Guard is distributed as one plugin. Its Free edition works without a
license key. A future Pro license adds team-oriented capabilities, but never
removes access to Free safety workflows or data created by those workflows.

The plugin does not contact a licensing service or accept production license
keys. It now has a provider-neutral capability boundary for future Pro workflows.
Without a verified provider state, Pro capabilities stay unavailable and all Free
workflows remain available.

## Local Pro preview

For local product development only, define the following constant in the local
site's `wp-config.php` before WordPress loads:

```php
define( 'ACF_SCHEMA_GUARD_LOCAL_PRO_PREVIEW_TOKEN', 'acf-schema-guard-local-preview' );
```

The token is accepted only when WordPress reports the environment as `local`. It
does not contact a server, persist a key, activate a real license, or grant Pro
access on staging or production. Remove the constant, or use any other value, to
review the Free state. This is a developer preview switch, not a secret and not
a production credential.

## Pro risk policy

Free uses the standard `high` release threshold: `high` and `critical` findings
make `wp acf-schema-guard check --fail-on-breaking` return a non-zero status.
Pro can choose the lowest severity that blocks the check in **ACF Schema Guard →
Settings → Release policy**.

| Threshold | Findings that fail the check | Typical use |
| --- | --- | --- |
| `warning` | Warning, High, Critical | Review every schema change before release. |
| `high` | High, Critical | Default balanced policy. |
| `critical` | Critical | Block only destructive schema removal. |

The policy never prevents saving ACF fields, modifies content, or hides a
finding. It changes only the pass or fail decision of release checks. Switch
between Free and Pro preview on a local site from **Settings → Edition preview**
to review both states safely.

### Team policy file

A Pro administrator can use **Download team policy JSON** in Settings and commit
the downloaded `acf-schema-guard-policy.json` to the WordPress project root.
After a normal Git pull, every local installation reads that file before its own
local setting. Free users can read and apply the committed policy but cannot edit
or export it. The plugin never writes into the repository automatically.

CI uses the same file when it runs either `wp acf-schema-guard check` or
`wp acf-schema-guard baseline check` with `--fail-on-breaking`. A policy failure
only returns a non-zero command status; branch protection in GitHub or GitLab is
what can prevent a merge.

## Approved exceptions / Zatwierdzone wyjątki

An approved exception is a temporary, auditable Pro decision about one exact
schema finding. It does not fix, hide, downgrade, or remove that finding. The
original severity, rationale, code references, and data-impact evidence remain
visible in **Changes**.

Zatwierdzony wyjątek jest tymczasową, audytowalną decyzją Pro dotyczącą jednego,
konkretnego problemu ze schematem. Nie naprawia, nie ukrywa, nie obniża severity
i nie usuwa problemu. W **Changes** nadal widoczne są pierwotna severity,
uzasadnienie, referencje w kodzie oraz wpływ na dane.

In a local Pro preview, open **Changes**, expand **Approve temporary exception**
next to the finding, provide a required reason and, when appropriate, an expiry
date. The record stores the current WordPress user, approval time, reason, and
an exact fingerprint of the finding. Revoke it from the same location when the
planned repair is complete.

W lokalnym podglądzie Pro otwórz **Changes**, rozwiń **Approve temporary
exception** przy danym problemie, wpisz wymagane uzasadnienie i, gdy ma to sens,
datę wygaśnięcia. Rekord zapisuje bieżącego użytkownika WordPressa, czas decyzji,
powód oraz dokładny fingerprint problemu. Po wykonaniu naprawy cofnij wyjątek w
tym samym miejscu.

An active exact-match exception lets `check` and `baseline check` pass that one
finding under `--fail-on-breaking`. A missing, expired, revoked, or different
finding still fails the configured release policy. It never blocks ACF editing,
Git commits, pushes, or merges by itself.

Aktywny wyjątek pasujący dokładnie do problemu pozwala komendom `check` oraz
`baseline check` pominąć ten jeden finding przy `--fail-on-breaking`. Brakujący,
wygasły, cofnięty lub dotyczący innego problemu wyjątek nadal powoduje wynik
zgodny z polityką release. Wyjątek sam w sobie nigdy nie blokuje edycji ACF,
commitów, pushów ani merge w Git.

## Initial Pro plans

## Pro release reports / Raporty release Pro

Pro can export a Markdown or JSON review artifact from the approved baseline:

```sh
wp acf-schema-guard release-report export release-report.md --format=markdown
wp acf-schema-guard release-report export release-report.json --format=json
```

Markdown includes the effective policy, original finding severity, and active
exception state. It can be pasted into GitHub Pull Requests or GitLab Merge
Requests. The command never posts remotely and does not overwrite a named file
without `--force`.

Wersja Pro może wyeksportować artefakt review w Markdown lub JSON z zatwierdzonego
baseline. Markdown zawiera aktywną politykę, pierwotną severity problemów i stan
wyjątków. Można go wkleić do GitHub Pull Request lub GitLab Merge Request.
Komenda niczego nie publikuje zdalnie i nie nadpisuje wskazanego pliku bez
`--force`.

For team branding, commit an optional Markdown template and use `--template`.
Keep both markers exactly once; all other content, including your own header,
footer, links, and checklist, is preserved:

```md
<!-- acf-schema-guard:summary -->
<!-- acf-schema-guard:findings -->
```

Do własnego układu zespołu zapisz opcjonalny szablon Markdown w Git i podaj
`--template`. Oba markery muszą wystąpić dokładnie raz. Pozostała treść, w tym
nagłówek, stopka, linki i checklista, pozostaje niezmieniona.

Pro licenses are annual subscriptions that include Pro updates and support for
the subscription term.

| Plan | Sites activated |
| --- | --- |
| Single site | 1 production or staging site |
| Agency | Up to 5 sites |
| Unlimited | No site-count limit, subject to fair-use terms |

The final sales provider may define pricing, billing currency, tax handling,
renewal emails, invoices, refunds, and customer accounts. Those commercial
concerns are outside the plugin.

## License lifecycle

1. A buyer receives a license key from the sales and licensing provider.
2. In a future plugin screen, an administrator enters the key and activates it
   for the current site.
3. The licensing service validates the key, plan, site limit, activation, and
   expiry date. Its response is authoritative.
4. The plugin caches the resulting status and checks it periodically, not on
   every Admin page load.
5. A successful check refreshes the local status. A temporary network failure
   allows Pro capabilities to continue for 30 days from the last successful
   validation.
6. When the license expires, is deactivated, exceeds its site limit, or cannot
   be verified after the grace period, only Pro-only actions become unavailable.

The 30-day grace period is an initial product decision. The future capability
service must make it configurable in one place rather than scattering it across
Admin screens or feature code.

## Authority and local cache

The licensing service is the only authority for license ID, plan, allowed site
count, active installations, status, and expiry timestamp. The WordPress site
must not treat a locally editable option as proof that a license is valid.

A future plugin may cache only the information required to display and apply
the last verified status:

- license ID;
- plan identifier;
- status;
- expiry timestamp;
- last successful check timestamp;
- site identity used for activation.

The cache is a convenience for uptime, not the source of truth. It must contain
no payment details, checkout credentials, customer passwords, or provider API
secrets.

## Data and expiry guarantees

A licensing check is never allowed to delete, alter, hide, encrypt, or make
unreadable:

- schema snapshots and approved baselines;
- schema comparison findings and risk explanations;
- code-usage and data-impact evidence;
- scanner configuration and report exports;
- ACF definitions, post meta, or WordPress content.

After expiry, existing records and reports remain readable. The user can still
use all Free capabilities and export or review existing evidence. Renewing or
reactivating a valid license restores only the additional Pro actions.

## Feature catalogue

The initial edition boundary protects individual developers and small teams from
schema breakage without requiring a purchase. Pro is reserved for configurable,
repeatable team workflows that make a shared release process easier to enforce.

| Capability | Edition | Product rationale |
| --- | --- | --- |
| ACF discovery, normalization, snapshots, History, and approved baseline | Free | This is the core safety record. A license must not make a user lose access to their own schema history. |
| Live baseline comparison, change explanations, severity guide, and data-impact evidence | Free | Developers need to understand a breaking change before they can decide whether Pro is useful. |
| Local JSON source health and sync guidance | Free | Git safety is a basic requirement for reliable ACF development. |
| Current-file PHP scan, Code Usage, dynamic-call warnings, and Changes code impact | Free | Concrete code locations are essential safety evidence, not an enterprise-only convenience. |
| WP-CLI scan, diff, baseline check, report export, and CI examples | Free | The command-line workflow should remain usable in any project and CI provider. |
| Planned nested-data impact and potentially unused-field diagnostics | Free | These extend core evidence and should not pressure a developer to purchase merely to assess risk. |
| Configurable risk policies and approved exceptions | Pro | Teams can align classifications and release gates with their own deployment policy. |
| Pull-request and merge-request reports, annotations, and review-ready summaries | Pro | These provide collaboration and review automation beyond the local safety workflow. |
| Shared team policy distribution and centrally managed project rules | Pro | This is a multi-site, multi-user governance workflow rather than a local analysis requirement. |
| Future advanced integrations | Pro, evaluated individually | An integration is Pro only when it adds team automation and does not hide raw safety evidence or block Free exports. |

The current release contains no Pro-only runtime capability. The table is a
product promise for future work, not a claim that a license can currently be
entered or validated.

## Capability rules for future implementation

- One central capability service decides whether a Pro action is available.
- Feature code must ask that service for a named capability; it must not inspect
  license options, expiry dates, or provider responses directly.
- A missing, expired, invalid, or unverifiable license returns a clear Pro-only
  unavailable state. It must not change a Free result into an error.
- Exports and read-only review of already generated safety evidence remain
  available even if a future Pro feature created supplementary presentation.
- No Free workflow may become Pro-only without an explicit product-plan change
  and a migration and communication decision.

## Provider-neutral integration requirements

When a provider is selected, its integration must offer HTTPS endpoints or a
supported SDK for activation, deactivation, and status validation. Responses
must include a stable license ID, plan, status, expiry timestamp, and an
activation outcome that distinguishes invalid keys, expired licenses, and a
reached site limit.

The integration must fail safely: unavailable or malformed remote responses may
not affect Free capabilities or stored data. Provider-specific API requests
belong behind one capability and license-client boundary, so changing sales
platforms does not require changes to risk analysis, snapshots, or reports.
