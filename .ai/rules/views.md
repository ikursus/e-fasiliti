---
paths:
  - '{resources/css/app.css,resources/views/**}'
---

# Views

## Form controls are styled in the app.css base layer, not per control
Tailwind v4 Preflight sets `border: 0` on every element and this project does not use @tailwindcss/forms. A control carrying only `border-slate-300 focus:ring-indigo-500` therefore renders as borderless text with no focus ring, which is how every admin form looked before it was fixed.

The frame, padding, chevron and focus state for input/select/textarea now live in one `@layer base` block in resources/css/app.css. Views should only add layout and state utilities such as `block w-full shadow-sm` or `border-rose-400` on error. Do not reintroduce `border-slate-300 focus:border-* focus:ring-*` on individual controls, and do not add the forms plugin.

Focus in that block uses `outline`, not `box-shadow`: the `shadow-sm` utilities the views carry sit in the utilities layer and would paint over a base-layer box-shadow the moment a control is focused.
