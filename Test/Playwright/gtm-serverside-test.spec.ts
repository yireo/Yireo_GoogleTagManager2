import {test, expect, configureGtm} from './lib/gtm-objects';
import {Page} from '@playwright/test';
import {Consent, consentConfig} from './lib/consent';

/**
 * Tests for the browser side of server-side tracking: the consent cookie that is read by the server and the
 * suppression of the browser purchase event. The order is placed through the guest REST API.
 */
const SKU = 'gtm-serverside-product';

const address = {
    firstname: 'John',
    lastname: 'Doe',
    street: ['Main Street 1'],
    city: 'Amsterdam',
    postcode: '1000AA',
    country_id: 'NL',
    telephone: '0123456789',
    email: 'john.doe@example.com',
};

function serverSideConfig(config: Record<string, any> = {}): Record<string, any> {
    return {
        'carriers/flatrate/active': 1,
        'carriers/flatrate/type': 'O',
        'carriers/flatrate/price': 5,
        'payment/checkmo/active': 1,
        'googletagmanager2/serverside_tracking/enabled': 1,
        'googletagmanager2/serverside_tracking/measurement_id': 'G-PLAYWRIGHT',
        'googletagmanager2/serverside_tracking/events': 'purchase,refund',
        'googletagmanager2/serverside_tracking/purchase_trigger': 'order_place',
        'googletagmanager2/serverside_tracking/dispatch_mode': 'cron',
        'googletagmanager2/serverside_tracking/require_consent': 0,
        ...config,
    };
}

async function configure(page: Page, config: Record<string, any>) {
    await configureGtm(page, {
        product: {sku: SKU, name: 'GTM Server-Side Product', price: 50, tax_class_id: 0},
        config: serverSideConfig(config),
        secure_config: {'googletagmanager2/serverside_tracking/api_secret': 'playwright-secret'},
    });
}

async function rest(page: Page, path: string, data?: Record<string, any>) {
    const response = await page.request.post('/rest/default/V1/' + path, {data: data ?? {}});
    const body = await response.text();
    expect(response.ok(), `REST ${path} failed: ${body.slice(0, 500)}`).toBeTruthy();
    return JSON.parse(body);
}

async function placeOrder(page: Page) {
    await page.goto('/');

    const cartId = await rest(page, 'guest-carts');
    await rest(page, `guest-carts/${cartId}/items`, {cartItem: {sku: SKU, qty: 1, quote_id: cartId}});
    await rest(page, `guest-carts/${cartId}/shipping-information`, {
        addressInformation: {
            shipping_address: address,
            billing_address: address,
            shipping_carrier_code: 'flatrate',
            shipping_method_code: 'flatrate',
        },
    });
    await rest(page, `guest-carts/${cartId}/payment-information`, {
        email: address.email,
        paymentMethod: {method: 'checkmo'},
        billingAddress: address,
    });

    await page.goto('/checkout/onepage/success/');
}

async function getConsentCookie(page: Page): Promise<Record<string, boolean> | null> {
    const cookie = (await page.context().cookies()).find(cookie => cookie.name === 'yireo_gtm2_consent');
    return cookie ? JSON.parse(decodeURIComponent(cookie.value)) : null;
}

test.describe('GTM server-side tracking', function () {
    test('mirrors an accepted consent into the consent cookie', async ({page}) => {
        await configureGtm(page, {config: consentConfig()});
        const consent = new Consent(page);
        await page.goto('/');
        test.skip(!(await consent.isAvailable()), 'Consent banner is not available');

        expect(await getConsentCookie(page)).toBeNull();
        await consent.button('Accept All').click();

        await expect.poll(() => getConsentCookie(page)).toEqual({analytics: true, marketing: true, preferences: true});
    });

    test('mirrors a rejected consent into the consent cookie', async ({page}) => {
        await configureGtm(page, {config: consentConfig()});
        const consent = new Consent(page);
        await page.goto('/');
        test.skip(!(await consent.isAvailable()), 'Consent banner is not available');

        await consent.button('Reject All').click();

        await expect.poll(() => getConsentCookie(page)).toEqual({analytics: false, marketing: false, preferences: false});
    });

    test('suppresses the browser purchase event when it is sent server-side', async ({page, dataLayer}) => {
        await configure(page, {'googletagmanager2/serverside_tracking/suppress_browser_purchase': 1});
        await placeOrder(page);

        await dataLayer.expectState({page_type: expect.any(String)});
        await dataLayer.event('purchase').notToBePushed({wait: 2000});
    });

    test('keeps the browser purchase event when suppression is disabled', async ({page, dataLayer}) => {
        await configure(page, {'googletagmanager2/serverside_tracking/suppress_browser_purchase': 0});
        await placeOrder(page);

        await dataLayer.event('purchase').toBePushed();
    });
});
