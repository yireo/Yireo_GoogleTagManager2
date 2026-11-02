import {test, expect, configureHyvaGtm, GTM_ID} from './lib/gtm-objects';

/**
 * Hyva variant of gtm-page-data-test.spec.ts: Every configure call goes through configureHyvaGtm(), because the
 * configure endpoint falls back to Magento/luma when no theme is given.
 */

test.describe('GTM page data (Hyva)', function () {
    test.beforeEach(async function ({page}) {
        await configureHyvaGtm(page);
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
        await configureHyvaGtm(page, {config: {'googletagmanager2/settings/wait_for_ui': 1}});

        await page.goto('/');
        await dataLayer.expectContainerNotLoaded();

        await page.mouse.move(100, 100);
        await page.mouse.wheel(0, 100);
        await dataLayer.expectContainerLoaded(GTM_ID);
    });
});
