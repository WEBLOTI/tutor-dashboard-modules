# Tutor Dashboard Modules

Custom plugin for building modular student dashboard endpoints inside Tutor LMS, managed from WordPress admin.

## Included in v0.1.0
- Internal CPT `tdm_module`
- Tutor dashboard menu and routing integration
- Admin UI under `Tutor LMS Pro > Tutor Modules`
- Renderers for Elementor templates, shortcodes, PHP views, and WooCommerce endpoints
- Tutor-style empty states and fallback messages
- Icon picker based on Tutor icons

## Structure
- `src/`: plugin classes
- `templates/`: frontend and dashboard templates
- `assets/`: admin and frontend CSS/JS
- `docs/`: implementation notes, ADRs, and manual test plan

## Notes
- Default audience is students only.
- `custom_callback` and `dynamic_data_view` are extension points enabled through filters.
- Rewrite rules are flushed lazily after relevant changes through the rewrite sync flag.
