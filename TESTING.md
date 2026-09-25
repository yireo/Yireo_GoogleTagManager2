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

    composer require loki/magento2-functional-tests
    bin/magento module:enable Loki_FunctionalTests
    bin/magento loki:modules:dump
    cd vendor/loki/magento2-functional-tests/Test/Playwright/
    npm install
    npx playwright test --project=Yireo_GoogleTagManager2

The folder `Test/Playwright/lib` contains test objects for the GTM `dataLayer`. The `test` exported from
`lib/gtm-objects.ts` adds a `dataLayer` fixture, which stubs all requests to `googletagmanager.com` so that GTM itself
does not modify the `dataLayer`:

```ts
import {test, expect, configureGtm, GTM_ID} from './lib/gtm-objects';

test('product page', async ({page, dataLayer}) => {
    await configureGtm(page, {config: {'googletagmanager2/settings/wait_for_ui': 0}});
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

### Configuring the shop

The second argument of `configureGtm()` is the payload of the `loki_functional_tests/index/configure` endpoint, so it
accepts everything `Loki\FunctionalTests\Service\Configurator` understands. Store settings go under `config`, where
they are merged on top of `defaultConfig`, and every other key is passed on untouched:

```ts
await configureGtm(page, {
    product: true,
    config: {
        'googletagmanager2/settings/view_cart_occurances': 'cart_page',
    },
});
```

| Key | Purpose |
|---|---|
| `config` | Store configuration, as `path => value`. Merged on top of `defaultConfig`. |
| `product` | `true` makes sure a saleable product exists, creating a dummy one if the catalog is empty. Pass an object (`{sku, name, price, qty, ...}`) to control it. |
| `customer` | Customer to create and/or log in. |
| `address` | Fields written to both the billing and shipping address of the current quote. |
| `modules` | Modules to enable. |
| `secure_config` | Configuration written through the encrypted backend model. |

Keys that are left out are not touched at all, so a test only states what it actually needs.
