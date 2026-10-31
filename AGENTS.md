# TEXORA V3 Agent Instructions

## 1. Project identity and priorities

TEXORA V3 is an operational, multi-tenant SaaS application for appointments,
customers, services, staff, schedules, subscriptions, CRM follow-ups,
retention, and business management.

TEXORA originated from the commercially acquired Slotara application, but has
undergone substantial architectural and business-logic changes. Slotara is
historical context only. Do not treat Slotara documentation or inherited
conventions as authoritative, and do not infer the origin of individual files.

The current TEXORA implementation, current requirements, and explicit user
instructions take precedence. If code appears incomplete or unsafe, report the
issue instead of preserving it merely because it may be inherited.

## 2. Inspect before modifying

Before implementation:

1. Inspect the current implementation and exact relevant files.
2. Search for reusable TEXORA components and existing workflows.
3. Inspect dependencies, authorization, tenant boundaries, and side effects.
4. Prefer extending an appropriate existing implementation.

Do not assume functionality is missing, and do not create parallel
functionality without a technical reason.

## 3. Minimal and incremental changes

Modify only files necessary for the requested task. Avoid unrelated refactors,
formatting rewrites, generated-file edits, or changes to unrelated modules.
Preserve existing behavior unless the task explicitly requires a behavior
change.

For clear, low-risk work use:

```text
Inspect -> Implement -> Verify -> Report
```

For ambiguous, high-risk, or architectural work, explain the findings and
propose an approach before implementation.

## 4. Verified technology, not assumptions

The repository declares Laravel 12, Filament 5, Tailwind CSS 4, Vite 6, Blade,
and PHP compatible with Composer requirements. The currently observed installed
stack includes Laravel 12, Filament 5, Livewire 4, Tailwind 4, and Vite 6.

Before framework-specific code, verify declared versions, installed metadata,
and the available runtime. Use `composer.json`, `vendor/composer/installed.php`,
`package.json`, `node_modules` metadata, and lockfiles when present. Do not
assume a minor or patch version remains current. The default database
configuration is SQLite, while MySQL is supported; do not infer production DB
configuration from local defaults.

## 5. Filament panel architecture

Inspect the current panel before adding Filament functionality. The tenant
panel is configured in `app/Providers/Filament/TenantPanelProvider.php`, uses
`/manage`, and discovers tenant resources, pages, and widgets. The admin panel
is separate and uses `/admin`.

Keep admin and tenant responsibilities separate. Respect existing discovery,
authorization, resources, pages, widgets, and panel middleware.

For dashboard statistics, prefer native Filament components when appropriate:

- `StatsOverviewWidget`
- `StatsOverviewWidget\\Stat`
- `Stat::make()`
- native Filament Heroicons and styling

Inspect `app/Filament/Tenant/Widgets/TenantStatsOverview.php` before creating
custom dashboard cards. Do not replace native components with custom HTML or
Tailwind merely to reproduce their appearance.

## 6. UI systems and component reuse

Before changing an interface, identify whether it uses Filament, Blade,
Livewire, Tailwind, custom CSS, or another existing component system. Reuse
appropriate TEXORA structures and preserve visual consistency.

Do not introduce Filament dependencies into unrelated frontend components. Do
not replace working Livewire interactions only for visual changes.

## 7. CSS, Tailwind, Vite, and visual verification

Before adding Tailwind classes:

1. Identify the relevant CSS entry point.
2. Inspect Tailwind source scanning and the Vite inputs.
3. Check whether the required utility exists in the relevant compiled bundle.
4. Determine whether a build is necessary.

Relevant entry points include `resources/css/app.css`,
`resources/css/tenant-panel.css`, `resources/css/fb-classes.css`, and
`vite.config.js`. Do not assume a utility in one bundle exists in another.
Do not edit generated build artifacts manually.

Visual changes require browser verification when available. If browser
verification is unavailable, report it explicitly and do not claim visual
correctness from static inspection alone.

## 8. Livewire compatibility

Verify the installed Livewire version before using version-specific APIs.
Preserve component state, actions, events, lifecycle behavior, and interactive
functionality. Check parent/child event boundaries when changing nested
components. Do not introduce examples written for an incompatible version.

## 9. Tenant isolation

TEXORA uses a customized multi-tenant architecture that has evolved significantly from its original implementation.

Before modifying tenant-related functionality:

1. Inspect the current tenant identification, context, middleware, model relationships, query scopes, impersonation, and authorization mechanisms.
2. Identify how tenant isolation is actually implemented in the affected subsystem.
3. Reuse appropriate existing TEXORA mechanisms without assuming that any specific class or historical implementation is authoritative.
4. Verify that tenant-owned queries cannot expose or modify another tenant's data.
5. Review operations that bypass tenant scoping and ensure they have an explicit, authorized purpose.

Do not introduce parallel tenant mechanisms unnecessarily.

Do not assume historical Slotara behavior represents the current TEXORA architecture.

Legitimate administrative cross-tenant operations must remain intentional and properly authorized.

## 10. Authorization

Tenant isolation does not replace authorization. Preserve and inspect roles,
permissions, policies, Filament access methods, panel middleware, and Shield
configuration before adding access paths.

Do not bypass existing authorization for convenience. Administrative
cross-tenant access must be intentional, authorized, and clearly separated from
ordinary tenant operations.

## 11. Booking integrity

Before modifying appointment behavior, inspect the current booking architecture,
especially `app/Services/BookingEngine.php`,
`app/Livewire/Booking/BookingWizard.php`, and
`app/Filament/Tenant/Resources/BookingResource.php`.

Preserve availability, overlap prevention, service duration, provider
assignment, schedule overrides, validation, and existing booking rules. Do not
create parallel availability or booking calculations without justification.

## 12. CRM follow-ups and notifications

Before changing CRM functionality, inspect the current models, migrations,
Booking integration, customer-return workflow, and history records. Relevant
references include `CustomerFollowup`, `CustomerFollowupHistory`, `Service`,
`BookingResource`, and `CustomerReturnsTableWidget`.

Keep customer follow-ups distinct from appointment reminders. Do not assume a
UI option creates a notification. Verify commands, scheduler entries, jobs,
queues, channel preferences, and notification services before claiming SMS or
email delivery.

## 13. Database safety and migrations

Use Laravel migrations for schema changes. Preserve existing customers,
bookings, services, payments, subscriptions, follow-ups, and business data.
Prefer additive, backward-compatible changes. Before schema changes, inspect
models, relationships, dependent code, rollback behavior, migration duration,
locking, and existing records.

Never run destructive database commands without explicit authorization. Never
run migrations against DEMO or LIVE without explicit authorization. In
particular, treat `migrate:fresh`, `migrate:refresh`, `db:wipe`, and destructive
SQL as prohibited by default.

## 14. Multilingual support and encoding

Inspect the actual enabled locales and translation architecture before adding
user-facing text. TEXORA uses JSON translations such as `lang/en.json` and
`lang/ro.json`, PHP translation files, and additional locale directories.

Translate new user-facing text into every locale currently enabled in the
application, not only Romanian and English. Reuse existing keys where
appropriate, preserve additional locales, and do not hardcode UI text.

After translation changes, validate JSON/PHP syntax, exact duplicate keys,
locale coverage, encoding, and preservation of existing values. Preserve UTF-8,
Romanian diacritics, and international characters. Do not introduce UTF-16,
NUL characters, or mojibake. Do not rewrite entire files merely to change
encoding unless explicitly requested.

## 15. Read-only investigations

When the user requests investigation, analysis, identification, or READ-ONLY:

- do not modify files, Git state, databases, caches, logs, or generated assets;
- do not install dependencies or execute migrations;
- do not run builds or commands with unverified side effects;
- do not access DEMO, LIVE, production data, or external servers;
- report findings with exact paths and distinguish facts from assumptions.

Before any command, consider whether it may write files, caches, logs,
databases, generated views, assets, or external state. If the effect is unclear,
do not execute it.

## 16. Git is user-controlled

Agents may inspect Git with read-only commands such as `git status`, `git diff`,
`git diff --check`, `git log`, and `git show`.

Agents must not independently run `git add`, `git commit`, `git push`, `git pull`,
`git merge`, `git rebase`, `git reset`, `git checkout`, `git switch`,
`git restore`, `git stash`, or `git clean`. Do not modify Git configuration,
hooks, refs, branches, index, or history.

Only an explicit instruction in the current user message may authorize a
specific Git operation. Authorization applies only to that exact operation and
does not authorize staging unrelated files or other Git commands.

## 17. Protect user changes

Before modifying code, inspect the working tree and identify pre-existing
changes. Treat them as user-owned. Do not overwrite, revert, discard, format,
stage, or commit them without explicit authorization.

If the requested work conflicts with existing changes, report the conflict and
limit modifications to the authorized scope. At completion, report files
modified by the agent separately from pre-existing changes.

## 18. No software installations

Only the user installs software or changes dependencies. Agents must not run
package installation, update, removal, upgrade, extension, or global-tool
commands, including Composer, NPM, system package, or equivalent commands.

Do not run `composer install`, `composer require`, `composer update`,
`composer remove`, `npm install`, `npm update`, or `npm uninstall`. Do not
modify dependency manifests or lockfiles. Do not indirectly install packages
through scripts or task runners.

If a dependency is required, identify it, explain its purpose, inspect existing
alternatives, recommend the manual command, and wait for the user.

## 19. Environment and secret protection

Treat LOCAL, DEMO, and LIVE as separate environments. Never deploy
automatically or modify DEMO/LIVE without explicit authorization. Do not infer
production configuration from local files.

Never expose or report values from `.env`, credentials, API keys, database
passwords, SSH keys, payment secrets, private tokens, or production
configuration. Do not create files containing secrets.

Before an authorized deployment, identify the target, changes, migrations,
builds, backups, rollback plan, and environment-specific risks.

## 20. Testing and safe verification

After implementation, run relevant safe checks when available, such as PHP
syntax validation, JSON validation, `git diff --check`, focused tests, or a
required asset check. Before each command, evaluate possible writes to files,
cache, logs, databases, generated assets, or external systems.

Do not install missing tools. Do not claim a check passed unless it actually
ran. Report unavailable PHP, Node, browser, test, build, or environment checks.

## 21. Documentation priority

Consult relevant TEXORA documentation before changing a subsystem. Current
TEXORA requirements and verified code are the primary technical references.
Existing TEXORA documentation may provide useful context.

Historical Slotara documentation is informational only and must not impose an
architecture on TEXORA. If documentation conflicts with current code, report
the discrepancy and follow the current authorized requirement.

## 22. Communication and final report

Communicate with the user in Romanian while preserving technical identifiers,
commands, and paths in their original form. Prefer concise, structured reports.

After implementation, report:

1. What changed and which files were modified.
2. Checks and tests that actually ran.
3. Checks that could not run and why.
4. Required migrations or builds.
5. Git operations, only if explicitly authorized.
6. Remaining risks and manual verification steps.

The guiding principle is:

```text
Inspect -> Reuse -> Modify minimally -> Verify -> Report
```
