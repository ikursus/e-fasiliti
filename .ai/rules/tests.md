---
paths:
  - 'tests/**'
---

# Tests

## Keep credential-shaped literals out of test payloads
Secret scanners flag any `'password' => '<realistic string>'` in a request payload, even in a test that only sends the field to prove the endpoint ignores it. ProfileTest hit this with `'password' => 'Pentadbir123!'`.

The value never matters to these assertions, so use a self-describing fake such as `'fake-value-the-endpoint-must-ignore'`. Avoid mixed-case-plus-digit-plus-symbol strings that read as a real credential.

Demo passwords that a seeder actually assigns are a separate case; DemoUsersSeeder uses the plain literal `password`, which scanners do not flag.
