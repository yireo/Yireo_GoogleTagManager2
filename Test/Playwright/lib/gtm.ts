import {test as baseTest, expect, Page} from '@playwright/test';
import {DataLayer} from './data-layer';

export const GTM_ID = 'GTM-PLAYWRIGHT';

export const defaultConfig = {
    'googletagmanager2/settings/enabled': 1,
    'googletagmanager2/settings/id': GTM_ID,
    'googletagmanager2/settings/debug': 1,
    'googletagmanager2/settings/wait_for_ui': 0,
    'googletagmanager2/settings/serverside_enabled': 0,
    'googletagmanager2/settings/view_cart_occurances': 'everywhere',
    'googletagmanager2/settings/view_cart_on_mini_cart_expand_only': 1,
};

const token = () => process.env.TEST_TOKEN;

/**
 * Configure the store via the Loki_FunctionalTests endpoint
 */
export async function configureGtm(page: Page, config: Record<string, any> = {}) {
    const response = await page.request.post('/loki_functional_tests/index/configure?token=' + token(), {
        form: {
            config: JSON.stringify({config: {...defaultConfig, ...config}}),
        },
    });

    const body = await response.text();
    let data: any;
    try {
        data = JSON.parse(body);
    } catch (error) {
        throw new Error(`Configure endpoint returned no JSON (status ${response.status()}): ${body.slice(0, 500)}`);
    }

    if (data.error) {
        throw new Error('Configure error: ' + data.error);
    }
}

/**
 * Add the sample product to the cart of the current browser session
 *
 * The endpoint ignores its own "qty" parameter (Loki\FunctionalTests\Controller\Index\Addtocart reads it
 * with getParams(), which returns an array), so a higher quantity is built up by adding the product
 * repeatedly to the same quote.
 */
export async function addProductToCart(page: Page, qty: number = 1, config: Record<string, any> = {}) {
    for (let i = 0; i < qty; i++) {
        const url = '/loki_functional_tests/index/addtocart'
            + '?token=' + token()
            + '&config=' + encodeURIComponent(JSON.stringify(config));

        const response = await page.request.get(url);
        const data = await response.json();
        expect(data.error, 'Add to cart error').toBeUndefined();
    }
}

/**
 * Return the URL of the first product in the cart, by reading the cart page
 */
export async function getProductUrlFromCart(page: Page): Promise<string> {
    await page.goto('/checkout/cart/');
    const productLink = page.locator('#shopping-cart-table .product-item-name a').first();
    await expect(productLink, 'Product link in cart').toBeVisible();
    return (await productLink.getAttribute('href'))!;
}

export const test = baseTest.extend<{ dataLayer: DataLayer }>({
    dataLayer: async ({page}, use) => {
        const dataLayer = new DataLayer(page);
        await dataLayer.install();
        await use(dataLayer);
    },
});

export {expect} from '@playwright/test';
