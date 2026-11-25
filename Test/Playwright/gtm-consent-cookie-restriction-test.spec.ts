import {configureGtm} from './lib/gtm-objects';
import {defineCookieRestrictionTests} from './lib/cookie-restriction-tests';

defineCookieRestrictionTests({
    title: 'GTM with Magento cookie restriction mode',
    configure: configureGtm,
});
