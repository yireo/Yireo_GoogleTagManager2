import {Page} from '@playwright/test';
import {test, expect, ConfigurePayload} from './gtm';
import {Consent, CONSENT_PR_URL, consentConfig} from './consent';

type Configure = (page: Page, payload?: ConfigurePayload) => Promise<void>;

/**
 * Consent banner tests, shared between Luma and Hyva. They describe the intended behaviour of the consent
 * feature of PR #198, so that bugs show up as failures. Without the feature, the tests are skipped.
 */
export function defineConsentTests(options: {
    title: string;
    configure: Configure;
    reopenBanner: (page: Page) => Promise<void>;
}) {
    const {title, configure, reopenBanner} = options;

    test.describe(title, function () {
        let consent: Consent;

        const open = async (page: Page, config: Record<string, any> = {}) => {
            await configure(page, {config: consentConfig(config)});
            await page.goto('/');
        };

        test.beforeEach(async function ({page}) {
            await configure(page, {config: consentConfig()});
            const html = await (await page.request.get('/')).text();
            test.skip(!html.includes('yireo-cookie-consent-banner'), `Consent banner (${CONSENT_PR_URL}) is not available`);
            consent = new Consent(page);
        });

        test.afterEach(async function () {
            await consent?.expectNoPageErrors();
        });

        test('does not show a banner or push consent when consent is disabled', async function ({page}) {
            await open(page, {'googletagmanager2/consent/enabled': 0});

            await expect(consent.banner).toHaveCount(0);
            await page.waitForTimeout(1000);
            expect(await consent.getCommands()).toEqual([]);
        });

        test('shows the banner on the first visit', async function ({page}) {
            await open(page);

            await expect(consent.banner).toBeVisible();
            await expect(consent.button('Accept All')).toBeVisible();
            await expect(consent.button('Accept Selection')).toBeVisible();
            await expect(consent.button('Reject All')).toBeVisible();
        });

        test('does not pre-tick optional consent checkboxes', async function ({page}) {
            await open(page);
            await expect(consent.banner).toBeVisible();

            await expect(consent.checkbox('necessary')).toBeChecked();
            await expect(consent.checkbox('necessary')).toBeDisabled();
            await expect(consent.checkbox('analytics')).not.toBeChecked();
            await expect(consent.checkbox('preferences')).not.toBeChecked();
            await expect(consent.checkbox('marketing')).not.toBeChecked();
        });

        test('pushes a consent default that denies everything optional', async function ({page}) {
            await open(page);

            await consent.expectCommand('default', {necessary: true, analytics: false, preferences: false, marketing: false});
        });

        test('pushes consent as a gtag() Arguments object', async function ({page}) {
            await open(page);

            const command = await consent.waitForCommand('default');
            expect(command.isArguments, 'GTM only handles consent commands pushed as Arguments object').toBe(true);
        });

        test('pushes the consent default before gtm.js and page data', async function ({page, dataLayer}) {
            await open(page);

            await dataLayer.expectEntry({event: 'gtm.js'});
            await dataLayer.expectState({page_type: expect.any(String)});
            const command = await consent.waitForCommand('default', 'first');

            const entries = await dataLayer.getEntries();
            const summary = JSON.stringify(entries.map(entry => Object.keys(entry ?? {}).join(',')));
            const gtmIndex = entries.findIndex(entry => entry?.event === 'gtm.js');
            const pageDataIndex = entries.findIndex(entry => entry?.page_type !== undefined);
            expect(command.index, 'consent default before gtm.js: ' + summary).toBeLessThan(gtmIndex);
            expect(command.index, 'consent default before page data: ' + summary).toBeLessThan(pageDataIndex);
        });

        test('pushes cookieConsentInitial on page load', async function ({page, dataLayer}) {
            await open(page);

            await dataLayer.event('cookieConsentInitial').toBePushed();
            await dataLayer.event('cookieConsentMarketingGranted').notToBePushed({wait: 0});
        });

        test('grants everything with "Accept All"', async function ({page, dataLayer}) {
            const all = {necessary: true, analytics: true, preferences: true, marketing: true};
            await open(page);
            await consent.button('Accept All').click();

            await consent.expectCommand('update', all);
            await expect(consent.banner).toBeHidden();
            await consent.expectStoredModes(all);
            await dataLayer.event('cookieConsentUpdate').toBePushed();
            await dataLayer.event('cookieConsentAnalyticsGranted').toBePushed();
            await dataLayer.event('cookieConsentPreferencesGranted').toBePushed();
            await dataLayer.event('cookieConsentMarketingGranted').toBePushed();
        });

        test('only grants the selected options with "Accept Selection"', async function ({page, dataLayer}) {
            const selection = {necessary: true, analytics: true, preferences: false, marketing: false};
            await open(page);
            await expect(consent.banner).toBeVisible();

            await consent.checkbox('analytics').check();
            await consent.checkbox('preferences').uncheck();
            await consent.checkbox('marketing').uncheck();
            await consent.button('Accept Selection').click();

            await consent.expectCommand('update', selection);
            await expect(consent.banner).toBeHidden();
            await consent.expectStoredModes(selection);
            await dataLayer.event('cookieConsentAnalyticsGranted').toBePushed();
            await dataLayer.event('cookieConsentMarketingGranted').notToBePushed();
        });

        test('denies everything optional with "Reject All"', async function ({page, dataLayer}) {
            const rejected = {necessary: true, analytics: false, preferences: false, marketing: false};
            await open(page);
            await consent.button('Reject All').click();

            await consent.expectCommand('update', rejected);
            await expect(consent.banner).toBeHidden();
            await consent.expectStoredModes(rejected);
            await dataLayer.event('cookieConsentUpdate').toBePushed();
            await dataLayer.event('cookieConsentMarketingGranted').notToBePushed();
        });

        test('remembers the choice on the next page view', async function ({page}) {
            const selection = {necessary: true, analytics: true, preferences: false, marketing: false};
            await open(page);
            await consent.checkbox('analytics').check();
            await consent.checkbox('preferences').uncheck();
            await consent.checkbox('marketing').uncheck();
            await consent.button('Accept Selection').click();
            await expect(consent.banner).toBeHidden();

            await page.reload();

            await consent.expectCommand('default', selection);
            await page.waitForTimeout(500);
            await expect(consent.banner).toBeHidden();
        });

        test('reopens the banner via the cookie-settings trigger', async function ({page}) {
            await open(page);
            await consent.button('Accept All').click();
            await expect(consent.banner).toBeHidden();

            await reopenBanner(page);

            await expect(consent.banner).toBeVisible();
        });

        test('links to the privacy statement when configured', async function ({page}) {
            await open(page, {'googletagmanager2/consent/privacy_statement_url': '/privacy-policy'});

            await expect(consent.banner.getByRole('link', {name: 'Learn more'})).toHaveAttribute('href', '/privacy-policy');
        });
    });
}
