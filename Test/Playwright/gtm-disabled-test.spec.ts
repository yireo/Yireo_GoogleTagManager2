import {test, configureGtm} from './lib/gtm-objects';

test.describe('GTM disabled', function () {
    test('does not load GTM or push page data when disabled', async function ({page, dataLayer}) {
        await configureGtm(page, {'googletagmanager2/settings/enabled': 0});

        await page.goto('/');
        await page.mouse.move(10, 10);

        await dataLayer.expectDisabled();
        await dataLayer.expectContainerNotLoaded();
        await dataLayer.event('view_item_list').notToBePushed({wait: 0});
    });

    test('does not load GTM when the ID is empty', async function ({page, dataLayer}) {
        await configureGtm(page, {'googletagmanager2/settings/id': ''});

        await page.goto('/');
        await dataLayer.expectContainerNotLoaded();
    });
});
