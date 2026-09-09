---
paths:
  - 'database/seeders/**'
---

# Seeders

## ROLE_PERMISSIONS mirrors the module/role matrix, with one recorded exception
RolesAndPermissionsSeeder::ROLE_PERMISSIONS is the single source of truth for the module/role matrix in docs-claude/01-modules.md §3. Every grant must match that matrix cell for cell.

One deliberate exception: the "M03 Lokasi" row gives Pentadbir Fasiliti and Pegawai Aset CRU across all of M03, but M03 covers both the physical location hierarchy and the organisation chart. Only pentadbir-sistem may write organisation units; all roles may read them. Do not "correct" this back to CRU without asking. It is documented on the constant itself.

When adding a permission, add a row to EXPECTED_GRANTS in tests/Feature/Admin/PermissionSeedingTest.php. That grid is written out independently of the seeder on purpose. Deriving it from the constant would make the test tautological, and pentadbir-sistem takes self::PERMISSIONS, so a permission dropped from the catalogue is otherwise caught by nothing.
