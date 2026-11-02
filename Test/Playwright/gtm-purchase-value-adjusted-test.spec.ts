import {test, expect, configureGtm} from './lib/gtm-objects';
import {Page} from '@playwright/test';

/**
 * Tests for the `value_adjusted` field of the `purchase` event (see PR #306)
 *
 * The order is placed through the guest REST API with the cookies of the browser, so this works with any
 * checkout (Luma, Hyva, Loki). QuoteManagement::placeOrder() stores the order in the checkout session, so
 * that the success page afterwards renders the purchase event for this order.
 *
 * Order: 1 x product (100.00), flat rate shipping (5.00), check / money order, no tax
 */
const SKU = 'gtm-value-adjusted-product';
const PRODUCT_PRICE = 100;
const SHIPPING_PRICE = 5;

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

async function configure(page: Page, config: Record<string, any>) {
    await configureGtm(page, {
        product: {sku: SKU, name: 'GTM Value Adjusted Product', price: PRODUCT_PRICE, tax_class_id: 0},
        config: {
            'carriers/flatrate/active': 1,
            'carriers/flatrate/type': 'O',
            'carriers/flatrate/price': SHIPPING_PRICE,
            'carriers/flatrate/handling_fee': 0,
            'payment/checkmo/active': 1,
            'googletagmanager2/settings/use_base_currency': 0,
            'googletagmanager2/settings/order_states_for_purchase_event': 'new,pending_payment,processing,complete',
            ...config,
        },
    });
}

async function rest(page: Page, path: string, data?: Record<string, any>) {
    const response = await page.request.post('/rest/default/V1/' + path, {data: data ?? {}});
    const body = await response.text();
    expect(response.ok(), `REST ${path} failed: ${body.slice(0, 500)}`).toBeTruthy();
    return JSON.parse(body);
}

async function placeOrder(page: Page): Promise<string> {
    await page.goto('/');

    const cartId = await rest(page, 'guest-carts');
    await rest(page, `guest-carts/${cartId}/items`, {
        cartItem: {sku: SKU, qty: 1, quote_id: cartId},
    });
    await rest(page, `guest-carts/${cartId}/shipping-information`, {
        addressInformation: {
            shipping_address: address,
            billing_address: address,
            shipping_carrier_code: 'flatrate',
            shipping_method_code: 'flatrate',
        },
    });
    const orderId = await rest(page, `guest-carts/${cartId}/payment-information`, {
        email: address.email,
        paymentMethod: {method: 'checkmo'},
        billingAddress: address,
    });

    await page.goto('/checkout/onepage/success/');
    return String(orderId);
}

test.describe('GTM purchase value_adjusted', function () {
    test('value_adjusted equals value when no maximum is set', async ({page, dataLayer}) => {
        await configure(page, {
            'googletagmanager2/settings/max_transaction_value': 0,
            'googletagmanager2/settings/include_shipping_in_adjusted_value': 0,
        });
        await placeOrder(page);

        const purchase = await dataLayer.event('purchase').toBePushed();
        const ecommerce = purchase.getEcommerce()!;
        expect(ecommerce.value).toBe(PRODUCT_PRICE);
        expect(ecommerce.shipping).toBe(SHIPPING_PRICE);
        expect(ecommerce.value_adjusted).toBe(PRODUCT_PRICE);
    });

    test('value_adjusted is capped by the maximum, value is not', async ({page, dataLayer}) => {
        await configure(page, {
            'googletagmanager2/settings/max_transaction_value': 50,
            'googletagmanager2/settings/include_shipping_in_adjusted_value': 0,
        });
        await placeOrder(page);

        const purchase = await dataLayer.event('purchase').toBePushed();
        const ecommerce = purchase.getEcommerce()!;
        expect(ecommerce.value).toBe(PRODUCT_PRICE);
        expect(ecommerce.value_adjusted).toBe(50);
    });

    test('value_adjusted includes shipping when enabled', async ({page, dataLayer}) => {
        await configure(page, {
            'googletagmanager2/settings/max_transaction_value': 0,
            'googletagmanager2/settings/include_shipping_in_adjusted_value': 1,
        });
        await placeOrder(page);

        const purchase = await dataLayer.event('purchase').toBePushed();
        const ecommerce = purchase.getEcommerce()!;
        expect(ecommerce.value).toBe(PRODUCT_PRICE);
        expect(ecommerce.value_adjusted).toBe(PRODUCT_PRICE + SHIPPING_PRICE);
    });

    test('value_adjusted_currency contains the order currency', async ({page, dataLayer}) => {
        await configure(page, {
            'googletagmanager2/settings/max_transaction_value': 0,
            'googletagmanager2/settings/include_shipping_in_adjusted_value': 0,
        });
        await placeOrder(page);

        const purchase = await dataLayer.event('purchase').toBePushed();
        const ecommerce = purchase.getEcommerce()!;
        expect(ecommerce.value_adjusted_currency).toBeTruthy();
        expect(ecommerce.value_adjusted_currency).toBe(ecommerce.currency);
    });
});
