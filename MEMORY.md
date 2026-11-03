# Project memory

- Luma has no global `yireoGoogleTagManager2Pusher`; it uses the RequireJS module `yireoGoogleTagManagerPush`. The global pusher only exists on Hyvä / Loki theme (`hyva/script-pusher.phtml`).
- The Hyvä pusher copies event data with `Object.assign({}, ...)`, so gtag-style commands (`arguments`) lose their `Arguments` type and are ignored by GTM. Push gtag commands directly to `window.dataLayer`.
- Playwright: `hersir playwright <spec-prefix>` runs a subset (e.g. `gtm-consent`); `hersir playwright_only` skips build steps. Hyvä specs must use `configureHyvaGtm()`.
- After `hersir playwright` (switches to production mode), `hersir integration-tests` fails in setup:install; run `rm -rf generated/metadata/*` and `bin/magento deploy:mode:set developer -s` in php-fpm first.
- Consent Mode: the `consent default` command is pushed from `<head>` by `script-consent-default.phtml` (before the GTM loader) and it defines the globals `yireoGoogleTagManager2Gtag/GetConsent/SetConsent`. The Luma/Hyvä banner templates must not push a `default` themselves; they call `yireoGoogleTagManager2SetConsent()`.
- `yireo-extension-builder check:extension_checker` flags Hyvä class names even inside strings; Hyvä templates must not reference `Hyva\` classes (no `Hyva_Theme` dependency).
- Local hersir env: if category pages show no products, `catalog/search/opensearch_server_hostname` may point to the unreachable `host.docker.internal`; set it to `opensearch` and reindex `catalogsearch_fulltext`.
- `ListProduct::getLoadedProductCollection()` does not load the collection; toolbar pagination is applied later in `_beforeToHtml()`. Plugins on it must never iterate/count an unloaded collection, or pagination breaks when a third party calls it early (#311).
- Integration tests: the OpenSearch-based layer collection returns no results for fixture products (even after reindexAll); inject `$category->getProductCollection()` via `ListProduct::setCollection()` instead. An untouched collection's `isLoaded()` returns `null`, not `false`.
