# Testing
## Unit testing
This extension ships with PHPUnit tests. The generic PHPUnit configuration in Magento 2 will pick up on these
tests. To only test Yireo extensions, simply run PHPUnit from within this folder. Note that this assumes that
the extension is installed via composer. For instance:

    phpunit

Also note that Mockery (`mockery/mockery`) is used for the integration tests. If you want to test this module, you need to install its dev-dependencies:

    composer require yireo/magento2-googletagmanager2 --dev

The JavaScript code ships with MochaJS unit tests. To install the stuff, simply run (within this extension directory) the following:

    npm install
    npm run mocha

Or just use `yarn`:

    yarn
    npm run mocha



## Playwright testing
The folder `Test/Playwright` contains Playwright tests. They are run with the Playwright setup of the
`loki-checkout/magento2-functional-tests` package (which also provides the endpoints used to configure the shop and
to add a product to the cart):

    cd vendor/loki-checkout/magento2-functional-tests/Test/Playwright/
    npm install
    npx playwright test --project=Yireo_GoogleTagManager2

Tests that put a product in the cart need a catalog. A bare Magento install has none, in which case the
`addtocart` endpoint reports `No product found.`. Seed a single simple product and category with:

    php vendor/yireo/magento2-googletagmanager2/Test/Playwright/seed-catalog.php
    bin/magento indexer:reindex
    bin/magento cache:flush

The folder `Test/Playwright/lib` contains test objects for the GTM `dataLayer`. The `test` exported from
`lib/gtm-objects.ts` adds a `dataLayer` fixture, which stubs all requests to `googletagmanager.com` so that GTM itself
does not modify the `dataLayer`:

```ts
import {test, expect, configureGtm, GTM_ID} from './lib/gtm-objects';

test('product page', async ({page, dataLayer}) => {
    await configureGtm(page, {'googletagmanager2/settings/wait_for_ui': 0});
    await page.goto('/some-product.html');

    await dataLayer.expectContainerLoaded(GTM_ID);
    await dataLayer.expectState({page_type: 'product'});

    const viewItem = await dataLayer.event('view_item').toHaveValidEcommerce();
    await dataLayer.event('add_to_cart').where({ecommerce: {currency: 'USD'}}).notToBePushed();
    await dataLayer.expectEventOrder(['view_item']);
});
```

Assertions on events poll the `dataLayer`, so events that are pushed asynchronously (for instance via customer
sections) are picked up as well.
