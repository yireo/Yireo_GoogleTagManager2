import {configureGtm} from './lib/gtm-objects';
import {defineConsentTests} from './lib/consent-tests';

defineConsentTests({
    title: 'GTM consent banner',
    configure: configureGtm,
    reopenBanner: async page => {
        await page.evaluate(() => (window as any).yireoGoogleTagManager2BannerToggle());
    },
});
