import {test, expect, configureGtm, addProductToCart} from './lib/gtm-objects';
import {Page} from '@playwright/test';

/**
 * Category data of products in the dataLayer (item_list_id, item_list_name, item_category...item_category5)
 *
 * Covers every page that gets its category data from Yireo\GoogleTagManager2\Util\CategoryProvider (see
 * https://github.com/yireo/Yireo_GoogleTagManager2/pull/310). These tests rely on the Luma sample data:
 * "Didi Sport Watch" (24-WG02) is assigned to Gear, Watches, the inactive Collections and New Luma Yoga Collection.
 */

const product = {
    id: 44,
    sku: '24-WG02',
    url: '/didi-sport-watch.html',
};

const categoryUrl = '/gear/watches.html';
const firstCategory = {id: '3', name: 'Gear'};
const currentCategoryName = 'Watches';
const expectedCategories = ['Gear', 'Watches', 'New Luma Yoga Collection'];
const inactiveCategory = 'Collections';

const firstCategoryConfig = {
    'googletagmanager2/settings/product_list_value_on_category': 'product_first_category',
};

function getItemCategories(item: Record<string, any>): string[] {
    return Object.keys(item)
        .filter(key => /^item_category\d*$/.test(key))
        .sort((a, b) => Number(a.replace('item_category', '') || 1) - Number(b.replace('item_category', '') || 1))
        .map(key => item[key]);
}

function expectProductCategories(item: Record<string, any> | undefined, label: string) {
    expect(item, `${label}: item of ${product.sku}`).toBeTruthy();
    expect(Object.keys(item!).filter(key => /^item_category\d*$/.test(key)).sort(), `${label}: category keys`)
        .toEqual(['item_category', 'item_category2', 'item_category3']);
    expect([...getItemCategories(item!)].sort(), `${label}: categories`).toEqual([...expectedCategories].sort());
}

function expectNoInactiveCategory(items: Array<Record<string, any>>, label: string) {
    for (const item of items) {
        expect(getItemCategories(item), `${label}: categories of ${item.item_sku}`).not.toContain(inactiveCategory);
        expect(item.item_list_name, `${label}: item_list_name of ${item.item_sku}`).not.toBe(inactiveCategory);
    }
}

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

async function skipWithoutSampleData(page: Page) {
    const response = await page.request.get(product.url);
    test.skip(response.status() !== 200, `Luma sample data product "${product.url}" is not available`);
}

test.describe('GTM category data of products', function () {
    test.beforeEach(async function ({page}) {
        await skipWithoutSampleData(page);
        await configureGtm(page, {config: firstCategoryConfig});
    });

    test('adds categories to view_item_list on a category page', async function ({page, dataLayer}) {
        await page.goto(categoryUrl);

        const event = await dataLayer.event('view_item_list').toBePushed();
        const items = event.getItems();
        expect(items.length).toBeGreaterThan(1);

        for (const item of items) {
            expect(item.item_list_name, `item_list_name of ${item.item_sku}`).toBeTruthy();
            expect(item.item_category, `item_category of ${item.item_sku}`).toBeTruthy();
        }

        expectNoInactiveCategory(items, 'view_item_list');

        const item = items.find(item => item.item_sku === product.sku);
        expect(String(item?.item_list_id)).toBe(firstCategory.id);
        expect(item?.item_list_name).toBe(firstCategory.name);
        expectProductCategories(item, 'view_item_list');
    });

    test('uses the current category as list on a category page when configured', async function ({page, dataLayer}) {
        await configureGtm(page, {
            config: {'googletagmanager2/settings/product_list_value_on_category': 'current_category'},
        });

        await page.goto(categoryUrl);

        const event = await dataLayer.event('view_item_list').toBePushed();
        const items = event.getItems();
        expect(items.length).toBeGreaterThan(1);
        for (const item of items) {
            expect(item.item_list_name, `item_list_name of ${item.item_sku}`).toBe(currentCategoryName);
        }

        expectProductCategories(items.find(item => item.item_sku === product.sku), 'view_item_list');
    });

    test('adds categories to the product details in a product listing', async function ({page, dataLayer}) {
        await page.goto(categoryUrl);
        await dataLayer.event('view_item_list').toBePushed();

        const details = await getProductDetails(page);
        expect(Object.keys(details).length, 'Product details in listing').toBeGreaterThan(1);

        const productDetails = details[String(product.id)];
        expect(productDetails?.item_sku).toBe(product.sku);
        expect(productDetails?.item_list_name).toBe(firstCategory.name);
        expectProductCategories(productDetails, 'product details');
        expectNoInactiveCategory(Object.values(details), 'product details');
    });

    test('adds categories to view_item on a product page', async function ({page, dataLayer}) {
        await page.goto(product.url);

        const event = await dataLayer.event('view_item').toHaveValidEcommerce({requireValue: false});
        event.expectItemCount(1);

        const item = event.getItem(0);
        expect(item?.item_id).toBe(product.sku);
        expect(String(item?.item_list_id)).toBe(firstCategory.id);
        expect(item?.item_list_name).toBe(firstCategory.name);
        expectProductCategories(item, 'view_item');
    });

    test('adds categories to add_to_cart and view_cart', async function ({page, dataLayer}) {
        await page.goto(product.url);
        await dataLayer.event('view_item').toBePushed();

        await page.locator('#product-addtocart-button').click();

        const addToCart = await dataLayer.event('add_to_cart').toBePushed({timeout: 20_000});
        const addToCartItem = addToCart.getItems().find(item => item.item_sku === product.sku);
        expect(addToCartItem?.item_list_name).toBe(firstCategory.name);
        expectProductCategories(addToCartItem, 'add_to_cart');

        await page.goto('/checkout/cart/');
        const viewCart = await dataLayer.event('view_cart').toBePushed({timeout: 20_000});
        const viewCartItem = viewCart.getItems().find(item => item.item_sku === product.sku);
        expect(viewCartItem?.item_list_name).toBe(firstCategory.name);
        expectProductCategories(viewCartItem, 'view_cart');
    });

    test('adds categories to view_cart for a product added in the background', async function ({page, dataLayer}) {
        await addProductToCart(page, 1, {sku: product.sku});

        await page.goto('/checkout/cart/');
        const viewCart = await dataLayer.event('view_cart').toBePushed({timeout: 20_000});
        const viewCartItem = viewCart.getItems().find(item => item.item_sku === product.sku);
        expectProductCategories(viewCartItem, 'view_cart');
        expectNoInactiveCategory(viewCart.getItems(), 'view_cart');
    });
});
