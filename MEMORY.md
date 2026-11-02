# Project memory

- Luma has no global `yireoGoogleTagManager2Pusher`; it uses the RequireJS module `yireoGoogleTagManagerPush`. The global pusher only exists on Hyvä / Loki theme (`hyva/script-pusher.phtml`).
- The Hyvä pusher copies event data with `Object.assign({}, ...)`, so gtag-style commands (`arguments`) lose their `Arguments` type and are ignored by GTM. Push gtag commands directly to `window.dataLayer`.
- Playwright: `hersir playwright <spec-prefix>` runs a subset (e.g. `gtm-consent`); `hersir playwright_only` skips build steps. Hyvä specs must use `configureHyvaGtm()`.
- After `hersir playwright` (switches to production mode), `hersir integration-tests` fails in setup:install; run `rm -rf generated/metadata/*` and `bin/magento deploy:mode:set developer -s` in php-fpm first.
