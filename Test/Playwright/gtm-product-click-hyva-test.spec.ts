import {test, expect, configureHyvaGtm} from './lib/gtm-objects';
import {Page} from '@playwright/test';

/**
 * select_item on Hyva product tiles (https://github.com/yireo/Yireo_GoogleTagManager2/issues/256)
 *
 * Hyva renders a non-salable product tile as a div without a form and without a product input. The module adds
 * data-entity_id to every tile (via the product_data_attributes of the product_list_item block), so the click
 * tracking still finds the product id. A customized tile is simulated by removing the product input of a tile.
 */

const categoryUrl = '/sample-data.html';
const tileSelector = '.products .product-item';

async function preventNavigation(page: Page) {
    await page.addInitScript(() => {
        document.addEventListener('click', event => event.preventDefault());
    });
}

async function openCategoryPage(page: Page) {
    await page.goto(categoryUrl);
    await page.waitForLoadState('load');

    const tiles = page.locator(tileSelector);
    test.skip(await tiles.count() === 0, `No product tiles found on "${categoryUrl}"`);

    const tile = tiles.first();
    await expect(tile).toHaveAttribute('data-entity_id', /^\d+$/);

    return tile;
}

test.describe('GTM select_item on Hyva product tiles', function () {
    test.beforeEach(async function ({page}) {
        await configureHyvaGtm(page);
        await preventNavigation(page);
    });

    test('adds data-entity_id matching the product input to product tiles', async function ({page}) {
        const tile = await openCategoryPage(page);

        const entityId = await tile.getAttribute('data-entity_id');
        const input = tile.locator('input[name="product"]');
        if (await input.count() > 0) {
            expect(await input.inputValue()).toBe(entityId);
        }
    });

    test('pushes select_item for a tile without product input', async function ({page, dataLayer}) {
        const errors: string[] = [];
        page.on('pageerror', error => errors.push(error.message));

        const tile = await openCategoryPage(page);
        await dataLayer.expectEnabled();

        const entityId = await tile.getAttribute('data-entity_id');
        await tile.evaluate(element => element.querySelectorAll('input[name="product"]').forEach(input => input.remove()));
        expect(await tile.locator('input[name="product"]').count()).toBe(0);

        await tile.locator('a.product-item-link').first().click();

        const event = await dataLayer.event('select_item').toBePushed();
        const item = event.getItem(0);
        expect(item, 'select_item item').toBeTruthy();
        expect(String(item!.item_id)).toBe(entityId);
        expect(errors, 'Page errors').toEqual([]);
    });
});
