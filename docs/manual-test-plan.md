# Manual Test Plan

## Baseline
- Activate `Tutor Dashboard Modules`.
- Confirm Tutor LMS, WooCommerce, and Elementor are active in `demo-zoom`.
- Visit `Tutor LMS Pro > Tutor Modules` in WP Admin and ensure the submenu is visible.

## Scenarios
1. Create module `Descargas`
   - Status: publish
   - Type: `woocommerce_endpoint`
   - WooCommerce endpoint: `downloads`
   - Render mode: `native_tutor`
   - Audience: `students_only`
   - Expected: appears in Tutor dashboard navigation for students.

2. Create module `Stock Market`
   - Status: publish
   - Type: `elementor_template`
   - Set valid Elementor template ID.
   - Expected: template renders inside Tutor dashboard.

3. Create module `Suscripciones`
   - Status: publish
   - Type: `php_view`
   - View key: `subscriptions_placeholder`
   - Expected: internal PHP view renders with shell wrapper.

4. Invalid slug protection
   - Set endpoint slug to `settings` or `logout`.
   - Expected: notice explains slug is reserved and valid endpoint is preserved.

5. Dependency fallback
   - Deactivate WooCommerce temporarily.
   - Expected: downloads module remains accessible and shows a Tutor-style empty state fallback.

6. Access control
   - Set module audience to `instructors_only` and use a student account.
   - Expected: module not visible and direct URL access shows fallback/denied state.
