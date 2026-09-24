import {test, expect, configureGtm, addProductToCart, DataLayer} from './lib/gtm-objects';
import {Page} from '@playwright/test';

/**
 * Regression tests for https://github.com/yireo/Yireo_GoogleTagManager2/issues/309
 *
 * On the cart page, `view_cart` is generated twice:
 * - page-based, through view/frontend/layout/checkout_cart_index.xml, pushed by hyva/data-layer.phtml
 * - customer-section based, through Plugin/AddDataToCartSection, pushed by hyva/script-additions.phtml
 *
 * hyva/script-pusher.phtml deduplicates on a hash of the payload, so the two are collapsed into one as
 * long as both describe the same cart. As soon as the cart customer section describes a different cart
 * than the one already pushed, the hash differs and a second `view_cart` reaches the dataLayer, while
 * the shopper viewed the cart only once.
 */

/**
 * Wait until the page has finished loading customer section data and the pushers have run.
 *
 * This matters: DataLayerEventAssumption.toBePushedTimes() polls until the count matches and then
 * returns, so asserting straight away would pass on the first push and never observe the duplicate.
 */
async function settle(page: Page) {
    await page.waitForLoadState('networkidle').catch(() => undefined);
    await page.waitForTimeout(1500);
}

async function expectViewCartPushedOnce(dataLayer: DataLayer, message: string) {
    const payloads = (await dataLayer.event('view_cart').all()).map(event => event.getEcommerce());
    expect(payloads, message).toHaveLength(1);
}

const cartPageConfig = {
    'googletagmanager2/settings/view_cart_occurances': 'cart_page',
    'googletagmanager2/settings/view_cart_on_mini_cart_expand_only': 0,
};

const everywhereConfig = {
    'googletagmanager2/settings/view_cart_occurances': 'everywhere',
    'googletagmanager2/settings/view_cart_on_mini_cart_expand_only': 0,
};

const minicartExpandConfig = {
    'googletagmanager2/settings/view_cart_occurances': 'everywhere',
    'googletagmanager2/settings/view_cart_on_mini_cart_expand_only': 1,
};

test.describe('GTM duplicate view_cart', function () {
    /**
     * Hyva submits the cart form in the background: the page is never reloaded, so window.dataLayer and
     * window.YIREO_GOOGLETAGMANAGER2_PAST_EVENTS survive. The refreshed cart customer section then carries
     * a different quantity than the `view_cart` that was already pushed, so the hash check does not
     * recognise it and the shopper gets a second `view_cart` for the same cart view.
     */
    for (const [configName, config] of Object.entries({
        'view_cart_occurances=cart_page': cartPageConfig,
        'view_cart_occurances=everywhere': everywhereConfig,
    })) {
        test(`pushes view_cart once when the quantity is changed on the cart page (${configName})`, async function ({page, dataLayer}) {
            await configureGtm(page, config);
            await addProductToCart(page);

            await page.goto('/checkout/cart/');
            await settle(page);
            await expectViewCartPushedOnce(dataLayer, 'view_cart pushes after opening the cart page');

            const quantity = page.locator('[data-role="cart-item-qty"]').first();
            await expect(quantity, 'Quantity field in the cart').toBeVisible();
            await quantity.fill('3');
            await quantity.press('Enter');
            await settle(page);

            await expectViewCartPushedOnce(
                dataLayer,
                'view_cart pushes after changing the quantity during a single cart page view'
            );
        });
    }

    /**
     * Hyva only refetches the customer sections once the private_content_version cookie changes. Whenever
     * the cart changed without that cookie being renewed, the cached cart section describes an older cart
     * than the page itself. The page-based `view_cart` and the cached customer-section `view_cart` then
     * disagree, and both are pushed.
     */
    test('does not push a stale view_cart from cached customer data on the cart page', async function ({page, dataLayer}) {
        await configureGtm(page, minicartExpandConfig);
        await addProductToCart(page);

        await page.goto('/checkout/cart/');
        await settle(page);

        await addProductToCart(page);

        await page.goto('/checkout/cart/');
        await settle(page);

        await expectViewCartPushedOnce(dataLayer, 'view_cart pushes for a single cart page view');
    });

    /**
     * Guard rail: any fix for the duplicates above must keep the customer-section `view_cart` working
     * away from the cart page, because that is the only source of `view_cart` when the minicart expands.
     */
    test('still pushes view_cart when the minicart is opened away from the cart page', async function ({page, dataLayer}) {
        await configureGtm(page, minicartExpandConfig);
        await addProductToCart(page);

        await page.goto('/');
        await settle(page);
        await dataLayer.event('view_cart').notToBePushed({wait: 0});

        await page.locator('#menu-cart-icon').click();
        await settle(page);

        await expectViewCartPushedOnce(dataLayer, 'view_cart pushes after expanding the minicart');
    });
});
