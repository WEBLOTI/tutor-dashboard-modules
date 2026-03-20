# ADR 0002 - Use Tutor Dashboard Hooks for Endpoint Routing

## Status
Accepted

## Context
The plugin must extend Tutor's student dashboard without theme overrides or manual rewrite hacks.

## Decision
Use Tutor's existing extension points:
- `tutor_dashboard/nav_items`
- `tutor_dashboard/permalinks`
- `load_dashboard_template_part_from_other_location`

## Consequences
- Endpoint routing stays aligned with Tutor core behavior.
- Custom modules behave like native dashboard sections.
- Future Tutor changes are isolated inside one integration service.
