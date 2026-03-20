# ADR 0001 - Store Dashboard Modules as a CPT

## Status
Accepted

## Context
The plugin needs an admin-friendly way to create, edit, order, activate, and deactivate dashboard modules without custom SQL tables in v1.

## Decision
Use an internal CPT named `tdm_module` with structured meta fields and a JSON config blob for renderer-specific settings.

## Consequences
- Reuses native WP admin listing, status, title, ordering, and revision-safe post lifecycle.
- Keeps migration overhead low for v1.
- Allows future migration to custom tables if module volume or query complexity grows.
