---
paths:
  - 'routes/**'
---

# Routes

## Add a controller import and its route in a single edit
A PostToolUse hook runs Pint after every edit and strips imports that are unused at that moment. Adding `use App\Http\Controllers\...\FooController;` in one edit and the routes that reference it in a second edit loses the import: the first edit leaves it unused, so the hook deletes it.

The failure is confusing. `FooController::class` then resolves against the global namespace and every request 500s with `ReflectionException: Class "FooController" does not exist`, pointing at the container rather than at the missing import.

Add the import and at least one usage in the same write, or re-read the top of the file afterwards to confirm the import survived. This actually happened while wiring up the location routes.
