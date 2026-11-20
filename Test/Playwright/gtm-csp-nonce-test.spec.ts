import {test, configureGtm, GTM_ID} from './lib/gtm-objects';
import {expectGtmScriptToInheritNonce} from './lib/csp';

test.describe('GTM CSP nonce', function () {
    test('passes the nonce of the GTM loader on to gtm.js', async function ({page, dataLayer}) {
        await configureGtm(page);

        await page.goto('/');
        await dataLayer.expectContainerLoaded(GTM_ID);

        await expectGtmScriptToInheritNonce(page);
    });
});
