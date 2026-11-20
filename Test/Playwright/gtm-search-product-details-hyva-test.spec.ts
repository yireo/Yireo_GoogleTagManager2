import {test, expect, configureHyvaGtm} from './lib/gtm-objects';
import {Page} from '@playwright/test';

/**
 * Hyva variant of gtm-search-product-details-test.spec.ts: Every configure call goes through configureHyvaGtm(),
 * because the configure endpoint falls back to Magento/luma when no theme is given.
 */

/**
 * Product details on search result pages (quick search and advanced search)
 *
 * The Plugin\AddProductDetails plugin only adds product data to a product list when the layout of the page
 * declares the "yireo_googletagmanager2.product-details" block. Without it, select_item is pushed without
 * product data. These tests rely on the Luma sample data product "Didi Sport Watch" (24-WG02).
 */

const product = {
    id: 44,
    sku: '24-WG02',
    url: '/didi-sport-watch.html',
};

const firstCategoryName = 'Gear';

const firstCategoryConfig = {
    'googletagmanager2/settings/product_list_value_on_category': 'product_first_category',
};

const searchPages = [
    {name: 'quick search', url: '/catalogsearch/result/?q=Didi'},
    {name: 'advanced search', url: '/catalogsearch/advanced/result/?name=Didi'},
];

const productLinkSelector = `form:has(input[name="product"][value="${product.id}"]) a.product-item-link`;

async function getProductDetails(page: Page): Promise<Record<string, Record<string, any>>> {
    return page.evaluate(() => {
        const prefix = 'YIREO_GOOGLETAGMANAGER2_PRODUCT_DATA_ID_';
        const details: Record<string, any> = {};
        for (const key of Object.keys(window)) {
            if (key.startsWith(prefix)) {
                details[key.substring(prefix.length)] = JSON.parse(JSON.stringify((window as any)[key]));
            }
        }

        return details;
    });
}

async function preventNavigation(page: Page) {
    await page.addInitScript(() => {
        document.addEventListener('click', event => event.preventDefault());
    });
}

async function skipWithoutSampleData(page: Page) {
    const response = await page.request.get(product.url);
    test.skip(response.status() !== 200, `Luma sample data product "${product.url}" is not available`);
}

test.describe('GTM product details on search result pages (Hyva)', function () {
    test.beforeEach(async function ({page}) {
        await configureHyvaGtm(page, {config: firstCategoryConfig});
        await skipWithoutSampleData(page);
    });

    for (const searchPage of searchPages) {
        test(`adds product details to the listing on the ${searchPage.name} page`, async function ({page, dataLayer}) {
            await page.goto(searchPage.url);
            await dataLayer.expectEnabled();

            const productDetails = (await getProductDetails(page))[String(product.id)];
            expect(productDetails, `Product details of ${product.sku}`).toBeTruthy();
            expect(productDetails.item_sku).toBe(product.sku);
            expect(productDetails.item_name).toBeTruthy();
            expect(productDetails.price).toBeDefined();
            expect(productDetails.item_list_name).toBe(firstCategoryName);
            expect(productDetails.item_category).toBeTruthy();
        });

        test(`pushes select_item with product data on the ${searchPage.name} page`, async function ({page, dataLayer}) {
            await preventNavigation(page);

            await page.goto(searchPage.url);
            await dataLayer.expectEnabled();
            await page.waitForLoadState('load');

            await page.locator(productLinkSelector).first().click();

            const event = await dataLayer.event('select_item').toBePushed();
            const item = event.getItem(0);
            expect(item, 'select_item item').toBeTruthy();
            expect(String(item!.item_id)).toBe(String(product.id));
            expect(item!.item_sku).toBe(product.sku);
            expect(item!.item_name).toBeTruthy();
            expect(item!.price).toBeDefined();
            expect(item!.item_category).toBeTruthy();
        });
    }
});
