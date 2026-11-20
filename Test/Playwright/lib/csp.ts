import {expect, Page} from '@playwright/test';

export async function expectGtmScriptToInheritNonce(page: Page) {
    const nonces = await page.evaluate(() => {
        const loader = Array.from(document.querySelectorAll('script'))
            .find(script => (script.textContent ?? '').includes('YIREO_GOOGLETAGMANAGER2_ENABLED'));
        const gtm = Array.from(document.querySelectorAll('script'))
            .find(script => script.src.includes('/gtm.js'));

        return {
            loader: loader ? loader.nonce : null,
            gtm: gtm ? gtm.nonce : null,
        };
    });

    expect(nonces.loader, 'GTM loader script to have a nonce').toBeTruthy();
    expect(nonces.gtm, 'gtm.js script to inherit the nonce of the GTM loader').toBe(nonces.loader);
}
