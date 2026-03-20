# Tutor Dashboard Modules

`Tutor Dashboard Modules` is a WordPress plugin that lets you add modular custom tabs/endpoints to the Tutor LMS student dashboard without touching your theme.

Instead of hardcoding custom dashboard pages in `functions.php` or template overrides, each module is managed from WordPress admin and rendered through a dedicated strategy such as Elementor, shortcodes, internal PHP views, or WooCommerce account endpoints.

## What It Does

- Adds custom dashboard tabs/endpoints inside Tutor LMS
- Lets admins manage modules from `Tutor LMS Pro > Tutor Modules`
- Stores each module as an internal CPT (`tdm_module`)
- Renders different content sources from the same module system
- Keeps the integration inside the plugin instead of theme hacks
- Uses Tutor-style empty states and fallbacks when dependencies are missing
- Supports future extension through filters and renderer registries

## Core Capabilities

### Modular dashboard endpoints
Each dashboard tab is a module with its own:

- title
- endpoint slug
- icon
- order
- active/inactive state
- audience
- content type
- type-specific configuration

### Admin-managed configuration
Non-technical admins can create modules from the WordPress dashboard instead of editing code.

### Render-by-strategy architecture
The plugin resolves the correct renderer automatically based on the module type.

### Tutor LMS-native integration
Modules are injected into the Tutor dashboard menu and permalink system using Tutor hooks, so the plugin works as an extension layer instead of a fragile theme customization.

## Supported Content Types

The current version supports these module types:

- `elementor_template`
- `shortcode`
- `php_view`
- `woocommerce_endpoint`

The architecture also exposes extension points for:

- `custom_callback`
- `dynamic_data_view`

These advanced types are intended for developer-registered integrations.

## WooCommerce Support

This plugin no longer limits WooCommerce integration to downloads only.

With `woocommerce_endpoint`, a module can target stable WooCommerce account endpoints such as:

- `orders`
- `downloads`
- `edit-account`
- `edit-address`
- `payment-methods`
- `add-payment-method`

Depending on the selected endpoint, the admin can choose different render modes and layouts.

Examples:

- show customer downloads inside Tutor
- show WooCommerce order history inside Tutor
- embed WooCommerce account forms inside Tutor without leaving the dashboard

## Elementor Support

For `elementor_template` modules, the module renders a selected Elementor template by ID inside the Tutor dashboard endpoint.

This is useful when you want to build rich views using:

- Elementor containers
- listings
- shortcodes
- JetEngine/Crocoblock widgets
- custom dynamic layouts

## PHP View Support

For `php_view` modules, the plugin renders a registered internal PHP view from the plugin itself.

This is useful when you need:

- tightly controlled output
- custom PHP-driven screens
- internal tools
- placeholders while a feature is still being built

The plugin includes two starter placeholder views by default:

- `subscriptions_placeholder`
- `tools_placeholder`

## Audience and Access

The admin UI is intentionally simplified around Tutor-oriented presets:

- `students_only`
- `instructors_only`
- `logged_in_any`

The plugin controls both:

- menu visibility
- direct endpoint access

If a user reaches a module they cannot access, the plugin returns a safe denied/fallback state instead of breaking the dashboard.

## Fallback Behavior

If a dependency is missing or a renderer cannot resolve its content, the module does not fatal error.

Instead, it shows a Tutor-style empty state with:

- a default message based on the module type, or
- a custom fallback message configured in the module

This keeps the student dashboard stable even when Elementor, WooCommerce, or another dependency is unavailable.

## Tutor Admin Experience

Modules are managed under:

`Tutor LMS Pro > Tutor Modules`

The editor UI includes:

- simplified audience selection
- Tutor icon picker
- content-type-specific fields
- fallback message controls
- advanced module settings

## Example Use Cases

### Downloads
Create a `Descargas` tab that shows WooCommerce downloadable products inside the Tutor dashboard.

### Stock Market
Create a `Stock Market` tab that renders an Elementor template with tables, charts, listings, and dynamic content.

### Subscriptions
Create a `Suscripciones` tab that can render:

- a PHP view
- a shortcode
- an Elementor template

## How It Works

1. The admin creates a module in `Tutor Modules`
2. The plugin saves it as a `tdm_module` post with metadata/config
3. Active modules are loaded from the repository
4. Tutor LMS receives the custom nav item and endpoint registration
5. When the endpoint is opened, the plugin resolves the configured renderer
6. The module output is wrapped in a Tutor-compatible shell

## Developer Extension Points

The plugin is structured so developers can extend it without editing core files.

Main filters/actions include:

- `tdm/register_module_types`
- `tdm/register_renderers`
- `tdm/register_php_views`
- `tdm/register_callbacks`
- `tdm/register_dynamic_providers`
- `tdm/module_is_visible`
- `tdm/module_dependencies_status`
- `tdm/module_before_render`
- `tdm/module_render_result`
- `tdm/module_after_render`
- `tdm/module_fallback_message`
- `tdm/woocommerce_downloads_data`
- `tdm/woocommerce_orders_query_args`
- `tdm/elementor_template_output`

This makes the plugin suitable as the base of an internal framework for custom Tutor dashboard apps.

## Main Architecture

- `src/Admin/` admin UI, CPT registration, module editor
- `src/Infrastructure/` repository and rewrite synchronization
- `src/Tutor/` Tutor dashboard integration
- `src/Rendering/` renderer contracts and renderers
- `src/Integration/Elementor/` Elementor bridge
- `src/Integration/WooCommerce/` WooCommerce data providers and endpoint bridge
- `src/Security/` access control
- `src/Support/` dependency detection, icon registry, UI bridge, logging, view loading
- `templates/` module templates and dashboard shell/router

## Current Status

Current plugin version: `0.1.0`

The plugin is already capable of:

- shipping production-ready custom Tutor dashboard tabs
- rendering Elementor-based screens
- rendering WooCommerce account functionality inside Tutor
- showing Tutor-native empty states and fallbacks
- serving as a reusable base for future modules

## Notes

- Default audience is typically `students_only`
- Rewrite rules are synchronized lazily through the plugin rewrite sync flow
- `custom_callback` and `dynamic_data_view` are available as extension architecture, not as end-user-first features
- Tutor LMS is the primary dependency; WooCommerce and Elementor are optional depending on module type

## Repository Structure

- `src/` plugin classes
- `templates/` dashboard and module templates
- `assets/` admin and frontend CSS/JS
- `docs/` implementation notes, ADRs, and manual test plan

