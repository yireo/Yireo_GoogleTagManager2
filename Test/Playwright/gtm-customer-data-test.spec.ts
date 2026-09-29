import {test, expect, configureGtm} from './lib/gtm-objects';
import {Page} from '@playwright/test';

/**
 * Regression tests for the customer data added by Plugin/AddDataToCustomerSection
 *
 * See https://github.com/yireo/Yireo_GoogleTagManager2/pull/312: when this plugin throws, the entire
 * customer section fails to load, so these tests also check that customer/section/load keeps working.
 */

async function loginCustomer(page: Page) {
    await configureGtm(page, {
        customer: {
            email: 'gtm-customer-data@example.com',
            password: 'Playwright123!',
            firstname: 'Playwright',
            lastname: 'Tester',
            login: true,
        },
    });
}

async function loadCustomerSection(page: Page): Promise<any> {
    const response = await page.request.get('/customer/section/load/?sections=customer&force_new_section_timestamp=true');
    expect(response.status(), 'customer/section/load status').toBe(200);

    const data = await response.json();
    expect(data.customer, 'customer section').toBeDefined();
    return data.customer;
}

test.describe('GTM customer data', function () {
    test('pushes guest customer data', async function ({page, dataLayer}) {
        await configureGtm(page);

        const customerSection = await loadCustomerSection(page);
        expect(customerSection.gtm).toMatchObject({
            customerLoggedIn: 0,
            customerId: 0,
            customerGroupId: 0,
            customerGroupCode: 'GUEST',
        });

        await page.goto('/');

        await dataLayer.expectContainerLoaded('GTM-PLAYWRIGHT');
        await dataLayer.expectState({
            customerLoggedIn: 0,
            customerGroupCode: 'GUEST',
        });
    });

    test('pushes logged in customer data', async function ({page, dataLayer}) {
        await configureGtm(page);
        await loginCustomer(page);

        const customerSection = await loadCustomerSection(page);
        expect(customerSection.firstname, 'customer firstname').toBe('Playwright');
        expect(customerSection.gtm).toMatchObject({
            customerLoggedIn: 1,
            customerId: expect.anything(),
            customerGroupId: expect.anything(),
            customerGroupCode: 'GENERAL',
        });
        expect(Number(customerSection.gtm.customerId), 'customerId').toBeGreaterThan(0);

        await page.goto('/');

        await dataLayer.expectState({
            customerLoggedIn: 1,
            customerGroupCode: 'GENERAL',
        });
        await dataLayer.expectNoDuplicateEvents();
    });
});
