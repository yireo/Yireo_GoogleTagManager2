import {configureHyvaGtm} from './lib/gtm-objects';
import {defineConsentTests} from './lib/consent-tests';

defineConsentTests({
    title: 'GTM consent banner (Hyva)',
    configure: configureHyvaGtm,
    reopenBanner: async page => {
        await page.evaluate(() => window.dispatchEvent(new CustomEvent('yireo-show-consent-banner')));
    },
});
