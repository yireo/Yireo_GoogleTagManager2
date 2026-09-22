import {test, expect, configureGtm, addProductToCart, getProductUrlFromCart} from './lib/gtm-objects';

test.describe('GTM ecommerce events', function () {
    test.beforeEach(async function ({page}) {
        await configureGtm(page);
        await addProductToCart(page);
    });

    test('pushes a view_cart event on the cart page', async function ({page, dataLayer}) {
        await page.goto('/checkout/cart/');

        await dataLayer.expectState({page_type: 'checkout/cart/index'});

        const viewCart = await dataLayer.event('view_cart').toHaveValidEcommerce({requireQuantity: true});
        viewCart.expectItemCount(1);
        expect(viewCart.getItem(0)!.quantity).toBe(1);
        expect(viewCart.get('meta'), 'Meta data is stripped before pushing').toBeUndefined();

        await dataLayer.event('view_cart').toBePushedTimes(1);
    });

    test('pushes view_item and add_to_cart events on the product page', async function ({page, dataLayer}) {
        const productUrl = await getProductUrlFromCart(page);
        await page.goto(productUrl);

        await dataLayer.expectState({page_type: 'product'});

        const viewItem = await dataLayer.event('view_item').toHaveValidEcommerce();
        viewItem.expectItemCount(1);
        const sku = viewItem.getItem(0)!.item_id;

        const addToCartButton = page.locator('#product-addtocart-button');
        await expect(addToCartButton).toBeEnabled();
        await addToCartButton.click();

        const addToCart = await dataLayer
            .event('add_to_cart')
            .where({ecommerce: {items: [expect.objectContaining({item_id: sku})]}})
            .toHaveValidEcommerce({requireQuantity: true});

        addToCart.expectItem({item_id: sku, quantity: 1});
        expect(addToCart.getEcommerce()!.currency).toBe(viewItem.getEcommerce()!.currency);

        await dataLayer.expectEventOrder(['view_item', 'add_to_cart']);
        await dataLayer.event('add_to_cart').toBePushedTimes(1);
        await dataLayer.expectNoDuplicateEvents();
    });

    test('pushes a remove_from_cart event when removing a cart item', async function ({page, dataLayer}) {
        await page.goto('/checkout/cart/');
        const sku = (await dataLayer.event('view_cart').toBePushed()).getItem(0)!.item_id;

        // Hyva renders a button with the removal payload, Luma an anchor with a class
        const removeButton = page.locator('#shopping-cart-table')
            .locator('[data-cart-item-removed-payload], .action-delete')
            .first();

        await expect(removeButton, 'Remove button in cart').toBeVisible();
        await removeButton.click();
        await page.waitForLoadState('domcontentloaded');

        await dataLayer.event('remove_from_cart').toHaveItem({item_id: sku});
    });
});
