# PR #198 "Consent-mode & Popup": test results and impact

PR: https://github.com/yireo/Yireo_GoogleTagManager2/pull/198 (head `b10684f`, base `6fdce6b` = master)
Tests: branch `test/pr-198-consent` (commits `c9cfd14`, `f0fad8a`, `5b6da6d`)

The tests describe the *intended* consent behaviour. On master they skip themselves (no `Config::isConsent()` /
no `#yireo-cookie-consent-banner`). Results below are from running them with PR #198 merged on a throwaway branch.

## Test results with PR #198 merged

| Suite | Pass | Fail |
|---|---|---|
| Unit (`Test/Unit/Config/ConfigTest.php`) | 4/4 consent tests | 0 |
| Integration (`Test/Integration/Block/ScriptConsentTest.php`) | 6/7 | `testConsentDefaultIsOutputBeforeGtmLoader` |
| Playwright Hyvä (`gtm-consent-hyva-test.spec.ts`) | 10/13 | pre-ticked checkboxes, Arguments object, ordering |
| Playwright Luma (`gtm-consent-test.spec.ts`) | 1/13 (only "consent disabled") | 12 |

On master: all new tests skip; full suite (`yireo-extension-builder check:all`) passes.

## What breaks / what is wrong

### Verified by failing tests

| # | Area | Problem | Evidence |
|---|---|---|---|
| 1 | Luma | Stray `},` after `function consentEvent(){}` → `SyntaxError: Unexpected token ','`. Whole script dead, banner stays `display:none` | 12 Luma Playwright tests; page error captured |
| 2 | Luma | Uses global `yireoGoogleTagManager2Pusher`, which only exists on Hyvä/Loki (Luma has RequireJS `yireoGoogleTagManagerPush`) → `ReferenceError` | Verified by temporarily removing the comma: every Luma test then fails with `yireoGoogleTagManager2Pusher is not defined` |
| 5 | Both | Consent pushed via `yireoGoogleTagManager2Pusher(arguments)`, which does `Object.assign({}, arguments)` → plain object `{0:'consent',1:'default',2:{…}}`. GTM only processes gtag commands pushed as a real `Arguments` object, so **Consent Mode never reaches GTM** | Hyvä "pushes consent as a gtag() Arguments object" |
| 8 | Hyvä | `selectedModes` defaults to all `true` → analytics/preferences/marketing pre-ticked (invalid consent under GDPR / CJEU Planet49) | Hyvä "does not pre-tick optional consent checkboxes" |
| 10 | Both | Consent block lives in `before.body.end` (Hyvä: runs on Alpine init), so the `consent default` comes **after** the page-data push (Hyvä dataLayer: `[page data, consent, event, event, gtm.js]`) and, in HTML, after the GTM loader in `<head>` | Integration ordering test (consent script at byte 39837, GTM loader at 4026); Hyvä ordering Playwright test |

### Found by review, not (fully) covered by a failing test

| # | Area | Problem |
|---|---|---|
| 3 | Luma | `yireoGoogleTagManager2BannerToggle()` computes `!(localStorage.getItem(...) ?? true)` on the strings `'true'`/`'false'` → always `false`; documented "Cookie-settings" link cannot reopen the banner (masked by #1/#2) |
| 4 | Luma | "Reject All" sets `necessary:false` (Hyvä keeps it `true`), and the first-visit default denies functionality/security storage; Luma pushes the update events before the `consent` command |
| 6 | Both | Pusher de-duplicates identical pushes per page, so `cookieConsentGranted` & co. fire at most once per page view |
| 11 | Hyvä | No `view/frontend/tailwind` config in the module → its Tailwind classes are only present if the theme happens to use them; `grow-1` is not a Tailwind class |
| 12 | UX | Independent of Magento's own cookie restriction notice (`web/cookie/cookie_restriction`, already used in `generic.js`): two consent mechanisms can be active |
| 13 | Docs | `docs/gtm-consent-example.json` contains a `MEASUREMENT_ID` placeholder not mentioned in `TUTORIAL_CONSENT.md`; the example tag uses `consentStatus: NOT_NEEDED` |
| 14 | BC | `Config` is not `final`; `getModuleConfigValue()` gains a 3rd parameter → subclasses/preferences overriding it with the old signature cause a fatal error |
| 15 | Themes | `loki_theme_default.xml` and `breeze_layout.xml` don't override the template → Loki theme & Breeze get the broken Luma template |
| 16 | Hyvä | Template uses `$hyvaCsp` without null check → fatal on Hyvä versions before CSP support |

### Corrected from the initial review (tests pass on Hyvä)

- `updateSelection()` using `this.$event` works: "Accept Selection" respects unticked boxes.
- "Reject All" update is *not* swallowed by de-duplication (the mode differs from the default push).
- `x-data="initYireoCookieConsentBanner()"` initialises fine on `Hyva/default-csp`.

### When consent is disabled (default)

No visible change: unit/integration/Playwright "consent disabled" tests pass and the existing suite stays green.
Only side effects: one extra (empty) block per page, Hyvä loads `HeroiconsOutline` per page, plus BC item #14.

## Environment notes

- Hersir `app/etc/config.php` contained stale `Loki_MapComponents` (module not installed) → integration install failed; entry removed.
- After `hersir playwright` (production mode + compiled DI), integration tests fail in `setup:install` with
  "Cannot instantiate interface ...UserConfigManagerInterface"; fix: `rm -rf generated/metadata/*` + developer mode.
