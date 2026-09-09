---
paths:
  - 'app/Http/Controllers/Admin/**'
---

# Admin

## Audit snapshots must hold Eloquent-cast values, not raw request input
AuditRecorder compares before and after fields strictly, so both snapshots must carry values that have been through Eloquent casts. Take the "after" snapshot from a refreshed model: `$model->refresh()->only([...])`.

Reading it straight off the in-memory model right after `update()` or `create()` gives you back the raw form values. A column with no cast, such as `locations.parent_id`, then reads as the string "3" on one side and the integer 3 on the other, and every save logs a fabricated change.

Use the `context` parameter, not `after`, for facts about the operation that are not fields of the target record, such as the ids a cascade also touched. Context is merged into the metadata at top level and stays out of the field comparison.
