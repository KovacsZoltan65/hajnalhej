# AGENTS.md
## Hajnalhéj Bakery – AI Development Operating Manual

> This file defines how all AI coding agents (Codex, Kiro, ChatGPT, Cursor, etc.) must work inside this repository.

---

# 1. Project Identity

## Project Name
**Hajnalhéj Bakery**

## Brand Direction
Premium artisan bakery ordering platform.

## Public Slogan
**Ropogós reggelek.**

## Internal Philosophy
**Kovász. Idő. Türelem.**

## Primary Goal

Build a production-grade bakery ordering platform with:

- elegant public storefront
- efficient bakery operations admin
- scalable Laravel architecture
- modern Vue frontend
- maintainable codebase

---

# 2. Technology Stack

## Backend

- Laravel 13
- PHP 8.4+
- MySQL

## Frontend

- Vue 3
- Inertia.js
- PrimeVue
- Tailwind CSS
- Vite

## Quality

- Pest
- Vitest
- Larastan compatible code where practical

---

# 3. Absolute Rules (Non-Negotiable)

## Architecture

Backend must use:

- Controller
- Service
- Repository
- FormRequest
- Policy

## Forbidden

- Fat controllers
- Business logic in Vue components
- Random helper chaos
- Copy-paste duplication
- Unstructured routes
- Hidden side effects
- Magic values everywhere

## Required

- Clear naming
- Typed return values
- Clean responsibilities
- Reusable patterns
- Production mindset

---

# 4. Backend Responsibilities

## Controllers

Allowed:

- authorize()
- validate through FormRequest
- call Service
- return response

Forbidden:

- business logic
- raw query construction
- multi-step workflows

---

## Services

Contain:

- business rules
- transactions
- workflows
- stock validation
- order placement logic
- reporting logic

---

## Repositories

Contain:

- query logic
- filters
- sorting
- pagination
- reusable data access

---

## FormRequests

Contain:

- validation
- authorize if needed

Never duplicate validation elsewhere.

---

# 5. Frontend Rules

## Vue Pages

Responsible for:

- page composition
- data binding
- user interactions

## Components

Responsible for:

- reusable UI

## Forbidden

- backend business logic
- duplicated form logic
- random state scattered everywhere

## Use

- script setup
- composables where useful
- reusable modal patterns
- PrimeVue patterns

---

# 6. UI Design System

## Brand Feel

Warm artisan premium minimal.

## Palette

- Primary Brown
- Cream Background
- Dark Rye Text
- Gold Accent

## UX Principles

- fast ordering
- mobile first
- clear CTAs
- elegant whitespace
- low friction checkout

---

# 7. Domain Rules

## Orders

Must enforce:

- only published menu can order
- order deadline respected
- slot capacity respected
- stock respected
- DB transaction used
- snapshots stored

## Snapshot Fields

Order items must save:

- product_name_snapshot
- unit_price
- line_total

---

# 8. Module Priorities

Build in this order:

## Phase 1

- Foundation
- Auth
- Layouts
- Theme
- Navigation

## Phase 2

- Categories
- Products

## Phase 3

- Weekly Menus
- Pickup Slots

## Phase 4

- Cart
- Checkout
- Orders

## Phase 5

- Reports
- Dashboard
- Notifications

---

# 9. Testing Rules

## Every meaningful feature must be tested.

## Backend

Use Pest.

Minimum:

- CRUD tests
- policy tests
- validation tests
- order workflow tests
- report tests

## Frontend

Use Vitest.

Minimum:

- component render tests
- form interaction tests
- cart state tests
- admin table behavior

## Forbidden

- fake placeholder tests
- zero-assertion tests
- meaningless coverage inflation

---

# 10. Database Rules

## Migrations must be:

- reversible
- clean
- indexed where needed
- constrained properly

## Use:

- foreign keys
- enums or controlled statuses
- decimals for money
- timestamps

---

# 11. Naming Rules

## Prefer explicit names:

Good:

- OrderPlacementService
- WeeklyMenuRepository
- UpdateProductRequest

Bad:

- HelperService
- DataManager
- StuffController

---

# 12. Security Rules

Always validate:

- authorization
- mass assignment
- request data
- admin-only routes
- sensitive actions

Never trust frontend limits.

---

# 13. Performance Rules

Prefer:

- eager loading
- indexes
- pagination
- aggregate queries
- caching when justified

Avoid:

- N+1
- loading huge datasets blindly
- repeated identical queries

---

# 14. Git Rules

## Commit Style

```text
[AUTO] module: short summary
[AUTO] products: CRUD foundation
[AUTO] orders: checkout transaction flow
[AUTO] reports: shopping list aggregation
```

Before Commit

Always:

1. run tests
2. inspect changed files
3. remove dead debug code
4. confirm migrations sane

# 15  When Agent Is Uncertain

Do NOT invent architecture randomly.

Instead:

1. inspect existing patterns
2. follow repository conventions
3. choose maintainability
4. if conflict exists, note assumption clearly

# 16. Output Expectations

When implementing features, agent should provide:

1. Summary
2. Files changed
3. Why decisions were made
4. Tests added
5. Risks / follow-up items

# 17. Priority Hierarchy

If multiple instructions exist:

1. AGENTS.md
2. master_prompt.md
3. module spec files
4. user task prompt

# 18. Bakery-Specific UX Philosophy

Customer should feel:

- simple
- premium
- warm
- trustworthy
- deliciously easy

Admin should feel:

- fast
- clear
- efficient
- low-stress

# 19. Future Expansion Readiness

Design now so later support is easy:

- online payments
- coupons
- subscriptions
- delivery zones
- loyalty points
- multiple pickup locations
- multiple bakery branches

# 20. Final Instruction to All Agents

Do not build a demo.

Build a real business system.

Every file should move Hajnalhéj closer to launch.

Ropogós reggelek.