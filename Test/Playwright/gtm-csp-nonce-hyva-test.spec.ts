import {test, configureHyvaGtm, GTM_ID} from './lib/gtm-objects';
import {expectGtmScriptToInheritNonce} from './lib/csp';

/**
 * Hyva variant of gtm-csp-nonce-test.spec.ts
 */

test.describe('GTM CSP nonce (Hyva)', function () {
    test('passes the nonce of the GTM loader on to gtm.js', async function ({page, dataLayer}) {
        await configureHyvaGtm(page);

        await page.goto('/');
        await dataLayer.expectContainerLoaded(GTM_ID);

        await expectGtmScriptToInheritNonce(page);
    });
});
