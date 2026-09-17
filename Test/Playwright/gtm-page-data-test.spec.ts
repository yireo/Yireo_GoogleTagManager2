import {test, expect, configureGtm, GTM_ID} from './lib/gtm-objects';

test.describe('GTM page data', function () {
    test.beforeEach(async function ({page}) {
        await configureGtm(page);
    });

    test('loads the GTM container and pushes page data on the homepage', async function ({page, dataLayer}) {
        await page.goto('/');

        await dataLayer.expectEnabled();
        await dataLayer.expectContainerLoaded(GTM_ID);
        await dataLayer.expectEntry({event: 'gtm.js'});
        await dataLayer.expectState({
            page_type: 'cms/index/index',
            version: expect.any(String),
            page_title: expect.any(String),
        });

        await dataLayer.expectNoDuplicateEvents();
    });

    test('waits for user interaction before loading GTM when configured', async function ({page, dataLayer}) {
        await configureGtm(page, {'googletagmanager2/settings/wait_for_ui': 1});

        await page.goto('/');
        await dataLayer.expectContainerNotLoaded();

        await page.mouse.move(100, 100);
        await page.mouse.wheel(0, 100);
        await dataLayer.expectContainerLoaded(GTM_ID);
    });
});
