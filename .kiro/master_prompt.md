# HAJNALHÉJ BAKERY – MAIN CODEX PROMPT
## Alap projekt felépítése Laravel 13 + Vue 3 + Inertia + PrimeVue stacken

```text
You are working on a greenfield project called "Hajnalhéj Bakery".

GOAL
Build the foundation of a production-ready artisan bakery ordering system with a public storefront and an admin panel.

TECH STACK
- Laravel 13
- PHP 8.4+
- MySQL
- Vue 3
- Inertia.js
- PrimeVue
- Tailwind CSS
- Vite
- Pest
- Vitest

BRAND CONTEXT
- Brand name: Hajnalhéj
- Domain: hajnalhej.hu
- Primary slogan: "Ropogós reggelek."
- Secondary artisan philosophy: "Kovász. Idő. Türelem."
- Style: premium artisan bakery, warm, modern Budapest mood
- Visual direction: beige / cream / dark brown / soft gold palette, elegant and clean, minimal but warm

IMPORTANT HIGH-LEVEL OBJECTIVE
This task is NOT to build every advanced feature in full depth immediately.
This task IS to create a clean, extensible, professional BASE PROJECT that already includes:
- stable architecture
- initial modules
- public pages
- admin shell
- database structure
- first CRUD flows
- ordering domain foundation
- seed data
- automated tests
- installation documentation

==================================================
1. MANDATORY ARCHITECTURE
==================================================

Backend must follow STRICT layering:
- Controller
- Service
- Repository
- FormRequest
- Policy
- Resource / DTO where useful

Rules:
- Controllers handle request/response only
- Services contain business logic
- Repositories contain data access logic
- No fat controllers
- No business logic in Vue components
- No unscoped raw query chaos unless absolutely necessary
- Prefer Eloquent relationships and repository methods
- Use transactions for order placement and all stock-sensitive operations
- Use enum-like constants or PHP enums for statuses where appropriate

Frontend must follow:
- Vue 3 with script setup
- reusable composables where justified
- reusable admin components
- consistent page structure
- clean state handling
- mobile-first public UI
- responsive admin UI

==================================================
2. PROJECT OBJECTIVE
==================================================

Create a bakery ordering system for a mini artisan bakery with these two major areas:

A) PUBLIC SITE
- Landing page
- Weekly menu page
- Product listing
- Cart
- Checkout
- Order success page

B) ADMIN PANEL
- Dashboard
- Products CRUD
- Weekly Menus CRUD
- Orders management
- Pickup Slots management
- Production Report
- Shopping List

The result should feel like a real business foundation, not a demo toy app.

==================================================
3. DOMAIN RULES
==================================================

These business rules are mandatory:

- Only PUBLISHED weekly menus can be ordered
- Orders are forbidden after order_deadline_at
- Pickup slot capacity must be checked in backend
- Weekly menu item max quantity must be checked in backend
- Order items must store snapshots:
  - product_name_snapshot
  - unit_price
  - line_total
- Order placement must happen inside DB transaction
- Historical orders must remain unchanged even if products later change
- Public ordering must work for guest customers
- Admin users manage all bakery operations
- Shopping list must be generated from ingredient templates and ordered quantities
- Production report must aggregate product quantities from active orders

==================================================
4. REQUIRED MODULES
==================================================

PUBLIC
1. Landing Page
2. Weekly Menu Page
3. Product Listing Section
4. Cart
5. Checkout
6. Order Success Page

ADMIN
1. Dashboard
2. Categories CRUD
3. Products CRUD
4. Weekly Menus CRUD
5. Weekly Menu Items management
6. Pickup Slots CRUD
7. Orders list + details + status management
8. Production Report
9. Shopping List

FOUNDATION / SUPPORT MODULES
- Customers
- Order Items
- Ingredient Templates
- Authentication for admin users
- Role/permission-ready structure

==================================================
5. DATABASE DESIGN REQUIREMENTS
==================================================

Create migrations, models, relationships, factories, and seeders for at least:

- users
- categories
- products
- weekly_menus
- weekly_menu_items
- pickup_slots
- customers
- orders
- order_items
- ingredient_templates

Suggested fields:

categories
- id
- name
- slug
- description nullable
- sort_order default 0
- is_active boolean
- timestamps

products
- id
- category_id
- name
- slug
- short_description nullable
- description nullable
- price decimal
- image_path nullable
- production_notes nullable
- is_active boolean
- sort_order default 0
- timestamps

weekly_menus
- id
- title
- week_start_date
- week_end_date
- order_deadline_at
- pickup_start_at
- pickup_end_at
- status (draft, published, closed)
- notes nullable
- timestamps

weekly_menu_items
- id
- weekly_menu_id
- product_id
- price_override nullable
- max_quantity
- per_customer_limit nullable
- sold_quantity_cache default 0
- is_visible boolean
- timestamps

pickup_slots
- id
- weekly_menu_id
- title
- pickup_date
- start_time
- end_time
- max_orders
- current_orders_cache default 0
- is_active boolean
- timestamps

customers
- id
- name
- email
- phone
- notes nullable
- marketing_consent boolean default false
- timestamps

orders
- id
- order_number unique
- customer_id nullable
- weekly_menu_id
- pickup_slot_id
- customer_name
- customer_email
- customer_phone
- subtotal decimal
- total decimal
- notes nullable
- status (pending, confirmed, prepared, completed, cancelled)
- placed_at nullable
- timestamps

order_items
- id
- order_id
- product_id nullable
- weekly_menu_item_id nullable
- product_name_snapshot
- unit_price decimal
- quantity
- line_total decimal
- timestamps

ingredient_templates
- id
- product_id
- ingredient_name
- quantity_per_unit decimal
- unit
- timestamps

==================================================
6. INITIAL IMPLEMENTATION SCOPE
==================================================

Implement the project foundation in this order:

PHASE 1 – FOUNDATION
- install dependencies
- configure Laravel + Inertia + Vue + PrimeVue + Tailwind
- set up app layout
- set up public layout
- set up admin layout
- create shared UI tokens aligned to Hajnalhéj brand
- create base navigation
- create seed admin user
- create basic auth flow for admin

PHASE 2 – CORE DATA
- categories
- products
- weekly menus
- weekly menu items
- pickup slots

PHASE 3 – ORDERING FOUNDATION
- cart flow
- checkout form
- order placement service
- order success page

PHASE 4 – ADMIN OPERATIONS
- orders list
- order details
- order status update
- dashboard summary cards

PHASE 5 – REPORTS
- production report
- shopping list report

==================================================
7. UI / UX REQUIREMENTS
==================================================

PUBLIC UI
- elegant artisan bakery look
- mobile-first
- premium but simple
- strong hero section
- warm imagery placeholders or blocks
- visible order deadlines
- clear pickup flow
- product cards with price and CTA
- cart summary easy to use
- polished empty states
- polished success states

ADMIN UI
- PrimeVue DataTable where appropriate
- search / filter / pagination
- clean dashboard cards
- toast messages
- confirm dialogs
- status badges
- modal-first CRUD where useful
- consistent toolbar patterns

COLOR / THEME DIRECTION
Use a Hajnalhéj-inspired palette similar to:
- primary brown
- cream background
- dark rye text
- subtle gold accent

TYPOGRAPHY DIRECTION
- elegant serif headings feel
- clean sans-serif body feel

==================================================
8. TESTING REQUIREMENTS
==================================================

Create meaningful tests, not placeholder noise.

BACKEND TESTS (Pest)
At minimum include feature tests for:
- Categories CRUD basics
- Products CRUD basics
- Weekly menu publish rules
- Order placement success
- Order placement blocked after deadline
- Order placement blocked when menu not published
- Order placement blocked on slot overflow
- Order placement blocked on max quantity overflow
- Order snapshot fields saved correctly
- Production report aggregation basic coverage
- Shopping list aggregation basic coverage

FRONTEND TESTS (Vitest)
At minimum include:
- Product card renders correctly
- Cart summary updates totals
- Checkout form validation behavior
- Orders admin table basic rendering
- Weekly menu admin form basic rendering

==================================================
9. CODE QUALITY RULES
==================================================

- Use clear naming
- Keep files focused
- Add PHPDoc where helpful
- Make code Larastan-friendly where reasonable
- Prefer typed properties and return types
- Keep validation centralized in FormRequests
- Keep reusable query logic in repositories
- Avoid duplication
- Build for maintainability, not shortcuts

==================================================
10. DELIVERABLE FORMAT
==================================================

Implement and output the work in a structured way.

I want the result to include:

1. Project structure overview
2. Installed packages list
3. Migrations
4. Models + relationships
5. Repositories
6. Services
7. FormRequests
8. Policies
9. Controllers
10. Routes
11. Vue pages
12. Vue components
13. Seeders / factories
14. Pest tests
15. Vitest tests
16. Setup instructions
17. Short explanation of architectural decisions

==================================================
11. IMPORTANT IMPLEMENTATION NOTES
==================================================

- Build the REAL starting project, not just a specification
- Generate actual code, not only prose
- If a section is too large, proceed module by module, but keep coherence
- Prefer production-minded defaults
- Assume the app is single-bakery for now
- Leave room for future upgrades like payments, coupons, delivery, subscriptions
- Use clean sample seed data matching the Hajnalhéj brand
- Make the first seeded weekly menu realistic
- Make seeded products realistic artisan bakery products

==================================================
12. SEEDED EXAMPLE CONTENT
==================================================

Seed example categories like:
- Kenyerek
- Édes pékáru
- Sós pékáru
- Pizza

Seed example products like:
- Klasszikus kovászos kenyér
- Magvas vekni
- Focaccia rozmaringgal
- Kakaós csiga
- Fahéjas tekercs
- Pizza dough pack

Seed one published weekly menu and one draft weekly menu.

==================================================
13. EXPECTED OUTPUT STYLE
==================================================

Be systematic and implementation-focused.
Do not hand-wave.
Do not skip core files.
Do not collapse important parts into vague summaries.

If needed, generate the project in coherent batches:
- foundation
- backend domain
- public frontend
- admin frontend
- tests
- docs

Start by building the full foundation of the project.