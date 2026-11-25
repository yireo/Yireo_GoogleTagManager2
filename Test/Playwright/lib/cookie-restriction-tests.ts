import {Page} from '@playwright/test';
import {test, expect, GTM_ID, ConfigurePayload} from './gtm';

type Configure = (page: Page, payload?: ConfigurePayload) => Promise<void>;

const COOKIE_NAME = 'user_allowed_save_cookie';

async function allowCookies(page: Page, websiteId: number = 1) {
    await page.evaluate(({name, websiteId}) => {
        document.cookie = name + '=' + encodeURIComponent(JSON.stringify({[websiteId]: 1})) + '; path=/';
    }, {name: COOKIE_NAME, websiteId});
}

/**
 * Magento cookie restriction mode without the consent banner of this extension: The GTM container is only
 * loaded once the Magento cookie user_allowed_save_cookie allows cookies for the current website.
 */
export function defineCookieRestrictionTests(options: { title: string; configure: Configure }) {
    const {title, configure} = options;

    test.describe(title, function () {
        test.beforeEach(async function ({page}) {
            await configure(page, {
                config: {
                    'web/cookie/cookie_restriction': 1,
                    'googletagmanager2/consent/enabled': 0,
                },
            });
        });

        test('does not load gtm.js or the noscript iframe without the cookie', async function ({page, dataLayer}) {
            const response = await page.goto('/');
            const html = await response!.text();

            expect(html, 'noscript iframe').not.toContain('/ns.html?id=');
            await dataLayer.expectContainerNotLoaded({wait: 2000});
        });

        test('does not load gtm.js when the cookie is for another website', async function ({page, dataLayer}) {
            await page.goto('/');
            await allowCookies(page, 999);
            await page.reload();

            await dataLayer.expectContainerNotLoaded({wait: 2000});
        });

        test('loads gtm.js when the cookie allows the website', async function ({page, dataLayer}) {
            await page.goto('/');
            await allowCookies(page);
            await page.reload();

            await dataLayer.expectContainerLoaded(GTM_ID);
        });

        test('loads gtm.js on interaction once cookies are allowed', async function ({page, dataLayer}) {
            await page.goto('/');
            await dataLayer.expectContainerNotLoaded({wait: 1000});

            await allowCookies(page);
            await page.mouse.move(10, 10);
            await page.mouse.move(100, 100);

            await dataLayer.expectContainerLoaded(GTM_ID);
        });
    });
}
