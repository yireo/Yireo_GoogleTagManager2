import {test as baseTest, expect, Page} from '@playwright/test';
import {DataLayer} from './data-layer';

export const GTM_ID = 'GTM-PLAYWRIGHT';

export const HYVA_THEME = 'Hyva/default-csp';

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
 * Payload accepted by the Loki_FunctionalTests configure endpoint
 *
 * Every key is handled by Loki\FunctionalTests\Service\Configurator, except `product`, which comes from
 * Loki\FunctionalTests\Service\ConfigureAction\Product. Keys that are left out are not touched at all.
 */
export type ConfigurePayload = {
    config?: Record<string, any>;
    product?: boolean | Record<string, any>;
    customer?: Record<string, any>;
    address?: Record<string, any>;
    modules?: Record<string, any>;
    secure_config?: Record<string, any>;
    [key: string]: any;
};

/**
 * Configure the store via the Loki_FunctionalTests endpoint
 *
 * The payload is passed on as-is, so anything the endpoint understands can be used. Only `config` is
 * treated specially: `defaultConfig` is merged underneath it, so tests only spell out what differs.
 */
export async function configureGtm(page: Page, payload: ConfigurePayload = {}) {
    const requestData = {
        ...payload,
        config: {...defaultConfig, ...(payload.config ?? {})},
    };

    const response = await page.request.post('/loki_functional_tests/index/configure?token=' + token(), {
        form: {
            config: JSON.stringify(requestData),
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

    const themeErrors = data.result?.theme?.errors ?? [];
    if (themeErrors.length > 0) {
        throw new Error('Configure theme error: ' + themeErrors.join(', '));
    }
}

/**
 * Configure the store via the Loki_FunctionalTests endpoint, with Hyva as frontend theme
 *
 * The endpoint falls back to Magento/luma whenever `theme` is missing, so every configure call in a
 * Hyva test must go through this function: A plain configureGtm() call silently switches back to Luma.
 */
export async function configureHyvaGtm(page: Page, payload: ConfigurePayload = {}) {
    await configureGtm(page, {...payload, theme: HYVA_THEME});
}

/**
 * Reload customer sections in the browser (Luma only)
 *
 * Luma only fetches customer sections when a cookie or the localStorage invalidation tells it to. Changes
 * made server-side (by the configure or addtocart endpoint) do not do that, so the browser needs a nudge,
 * like a regular action in the browser (login, add to cart) would give it.
 */
export async function reloadCustomerSections(page: Page, sections: string[]) {
    await page.evaluate(sectionNames => new Promise(resolve => {
        (window as any).require(['Magento_Customer/js/customer-data'], (customerData: any) => {
            customerData.reload(sectionNames, true).always(() => resolve(null));
        });
    }), sections);
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
