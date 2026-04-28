# HAJNALHÉJ — TEST MAINTENANCE MODE

## Context

Laravel + Vue 3 + Inertia + PrimeVue project.

Goal:
Audit, run, repair and extend the test suite.

## Tasks

### 1. Discover existing tests

Inspect:

- tests/Feature
- tests/Unit
- resources/js/\*_/_.test.\*
- resources/js/\*_/_.spec.\*
- package.json
- phpunit.xml
- pest.php
- vite.config.\*
- composer.json

Identify:

- existing backend tests
- existing frontend tests
- missing coverage
- broken test setup
- duplicated or obsolete tests

---

### 2. Run backend tests

Run:

```bash
php artisan test
```

or if Pest is used:

./vendor/bin/pest

Fix all failing backend tests.

Do not weaken assertions just to make tests pass.

3. Run frontend tests

Run:

npm test

or inspect package.json and use the correct script, for example:

npm run test
npm run test:unit
npm run vitest

Fix all failing frontend tests.

4. Find missing tests

Compare implemented modules with test coverage.

Focus especially on:

Products
Categories
Ingredients
Recipes / BOM
Weekly Menus
Orders
Admin Sidebar
Policies
FormRequests
Services
Repositories
Inertia pages
PrimeVue modal CRUD flows 5. Add missing backend tests

Add Pest Feature tests for:

index/list endpoints
create/store
update
delete / soft delete
validation errors
authorization / policy denial
important business rules

Use factories and seeders where appropriate.

6. Add missing frontend tests

Add Vitest tests for:

page render
table render
create modal open/close
edit modal open/close
delete confirm flow
form validation display
flash/toast behavior
sidebar group rendering
active menu item state

Mock Inertia router/forms safely.

7. Fix setup problems

If tests fail because of setup/config:

repair test database configuration
repair factories
repair seeders
repair missing mocks
repair component imports
repair PrimeVue test mounting
repair Inertia mocks

Do not remove meaningful tests.

8. Quality rules

Use the existing project conventions:

Controller → Service → Repository
FormRequest validation
Policy authorization
Pest for backend
Vitest for frontend
clear Arrange / Act / Assert structure
meaningful test names
no fragile snapshot tests unless already used
no excessive mocking of domain logic 9. Output required

When finished, report:

Commands executed
Tests that were failing
Root cause of each failure
Files changed
Missing tests added
Final test result
Any remaining known gaps
Acceptance criteria
Backend tests pass
Frontend tests pass
Missing critical tests are added
No assertions weakened
No business rules removed
No production code changed unnecessarily
