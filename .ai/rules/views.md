---
paths:
  - '{resources/css/app.css,resources/views/**}'
---

# Views

## Form controls are styled in the app.css base layer, not per control
Tailwind v4 Preflight sets `border: 0` on every element and this project does not use @tailwindcss/forms. A control carrying only `border-slate-300 focus:ring-indigo-500` therefore renders as borderless text with no focus ring, which is how every admin form looked before it was fixed.

The frame, padding, chevron and focus state for input/select/textarea now live in one `@layer base` block in resources/css/app.css. Views should only add layout and state utilities such as `block w-full shadow-sm` or `border-rose-400` on error. Do not reintroduce `border-slate-300 focus:border-* focus:ring-*` on individual controls, and do not add the forms plugin.

Focus in that block uses `outline`, not `box-shadow`: the `shadow-sm` utilities the views carry sit in the utilities layer and would paint over a base-layer box-shadow the moment a control is focused.

## Form partials carry @csrf, not the pages that include them
Every `_form.blade.php` under resources/views/admin emits `@csrf` as its first line; the create/edit pages only supply the `<form>` tag and `@method('PUT')`. Follow that split for new modules.

Forgetting it in the partial ships a create and edit screen that both return 419 Page Expired on submit. The feature suite will not catch it: Laravel skips CSRF validation while running tests, so the store and update tests pass against a form the browser cannot submit. This happened to admin/rooms.

An assertion that the page merely contains `name="_token"` is also useless, because the layout renders its own logout form. Assert inside the form that posts to the route under test, as `RoomManagementTest::test_the_room_forms_carry_a_csrf_token` does.
