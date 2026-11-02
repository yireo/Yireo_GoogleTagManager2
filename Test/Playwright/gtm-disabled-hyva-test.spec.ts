import {test, configureHyvaGtm} from './lib/gtm-objects';

/**
 * Hyva variant of gtm-disabled-test.spec.ts: Every configure call goes through configureHyvaGtm(), because the
 * configure endpoint falls back to Magento/luma when no theme is given.
 */

test.describe('GTM disabled (Hyva)', function () {
    test('does not load GTM or push page data when disabled', async function ({page, dataLayer}) {
        await configureHyvaGtm(page, {config: {'googletagmanager2/settings/enabled': 0}});

        await page.goto('/');
        await page.mouse.move(10, 10);

        await dataLayer.expectDisabled();
        await dataLayer.expectContainerNotLoaded();
        await dataLayer.event('view_item_list').notToBePushed({wait: 0});
    });

    test('does not load GTM when the ID is empty', async function ({page, dataLayer}) {
        await configureHyvaGtm(page, {config: {'googletagmanager2/settings/id': ''}});

        await page.goto('/');
        await dataLayer.expectContainerNotLoaded();
    });
});
