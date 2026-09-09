---
paths:
  - config/log-viewer.php
---

# Config

## Log viewer needs auth middleware and the viewLogViewer gate
opcodesio/log-viewer ships with middleware ['web', AuthorizeLogViewer] and no auth. Its own AuthorizeLogViewer only aborts when the app is in production, so outside production /log-viewer and its API answer 200 to an anonymous visitor. This was verified here: a guest got 200 on the page and on /log-viewer/api/files.

Two things keep it closed, and both are needed. config/log-viewer.php adds 'auth' to both `middleware` and `api_middleware`; the API is what actually serves log contents, so gating only the page would be cosmetic. AppServiceProvider defines the `viewLogViewer` gate, restricted to the system administrator via isSuperAdmin().

Do not remove either when upgrading the package or re-publishing its config. tests/Feature/Admin/LogViewerAccessTest.php covers guest, every non-admin role, the admin, the API, and the sidebar link.
