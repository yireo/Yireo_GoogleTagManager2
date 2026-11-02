import {test, expect, configureGtm, addProductToCart, reloadCustomerSections, DataLayer} from './lib/gtm-objects';
import {Page} from '@playwright/test';

/**
 * Regression tests for https://github.com/yireo/Yireo_GoogleTagManager2/issues/309 (Luma)
 *
 * On the cart page, `view_cart` is generated twice:
 * - page-based, through view/frontend/layout/checkout_cart_index.xml, pushed by luma/data-layer.phtml
 * - customer-section based, through Plugin/AddDataToCartSection, pushed by web/js/generic.js
 *
 * push.js deduplicates on a hash of the payload, so the two are collapsed into one as long as both
 * describe the same cart. Every cart page view should end up with exactly one `view_cart`.
 *
 * See gtm-view-cart-duplicate-hyva-test.spec.ts for Hyva.
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
    product: true,
    config: {
        'googletagmanager2/settings/view_cart_occurances': 'cart_page',
        'googletagmanager2/settings/view_cart_on_mini_cart_expand_only': 0,
    },
};

const everywhereConfig = {
    product: true,
    config: {
        'googletagmanager2/settings/view_cart_occurances': 'everywhere',
        'googletagmanager2/settings/view_cart_on_mini_cart_expand_only': 0,
    },
};

const minicartExpandConfig = {
    product: true,
    config: {
        'googletagmanager2/settings/view_cart_occurances': 'everywhere',
        'googletagmanager2/settings/view_cart_on_mini_cart_expand_only': 1,
    },
};

test.describe('GTM duplicate view_cart', function () {
    /**
     * Luma submits the cart form (checkout/cart/updatePost) and then reloads the cart page entirely, so
     * the dataLayer starts from scratch. The reloaded cart page is a new cart page view, which should get
     * exactly one `view_cart` (with the new quantity) - not zero and not two.
     *
     * The reload has to be awaited explicitly: Otherwise the dataLayer is read in the middle of the
     * navigation, which reports an empty dataLayer.
     */
    for (const [configName, payload] of Object.entries({
        'view_cart_occurances=cart_page': cartPageConfig,
        'view_cart_occurances=everywhere': everywhereConfig,
    })) {
        test(`pushes view_cart once when the quantity is changed on the cart page (${configName})`, async function ({page, dataLayer}) {
            await configureGtm(page, payload);
            await addProductToCart(page);

            await page.goto('/checkout/cart/');
            await settle(page);
            await expectViewCartPushedOnce(dataLayer, 'view_cart pushes after opening the cart page');

            const quantity = page.locator('[data-role="cart-item-qty"]').first();
            await expect(quantity, 'Quantity field in the cart').toBeVisible();
            await quantity.fill('3');

            const cartReloaded = page.waitForResponse(response => {
                const request = response.request();
                return request.isNavigationRequest()
                    && request.method() === 'GET'
                    && new URL(response.url()).pathname === '/checkout/cart/'
                    && response.status() === 200;
            });
            await quantity.press('Enter');
            await cartReloaded;
            await page.waitForLoadState('load');
            await settle(page);

            await expect(page.locator('[data-role="cart-item-qty"]').first(), 'Updated quantity').toHaveValue('3');
            await expectViewCartPushedOnce(dataLayer, 'view_cart pushes after reloading the cart page');
        });
    }

    /**
     * Whenever the cart changed without the browser knowing (here: server-side), the cached cart section
     * describes an older cart than the page itself. The page-based `view_cart` and the cached
     * customer-section `view_cart` then disagree, and both could be pushed.
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
     * On Luma, opening the minicart (`.action.showcart`) fires `minicart_collapse` through the
     * `mage/dropdown` mixin, which releases the pending `view_cart`.
     */
    test('still pushes view_cart when the minicart is opened away from the cart page', async function ({page, dataLayer}) {
        await configureGtm(page, minicartExpandConfig);
        await addProductToCart(page);

        await page.goto('/');
        await settle(page);
        await dataLayer.event('view_cart').notToBePushed({wait: 0});

        // addProductToCart() adds the product server-side, so the browser does not know that the cart
        // section is outdated. Reload it, like a regular add-to-cart in the browser would.
        await reloadCustomerSections(page, ['cart']);
        await settle(page);

        await page.locator('.action.showcart').click();
        await settle(page);

        await expectViewCartPushedOnce(dataLayer, 'view_cart pushes after expanding the minicart');
    });
});
