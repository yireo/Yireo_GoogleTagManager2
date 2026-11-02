import {test, expect, configureGtm, reloadCustomerSections} from './lib/gtm-objects';
import {Page} from '@playwright/test';

/**
 * Regression tests for the customer data added by Plugin/AddDataToCustomerSection (Luma)
 *
 * See https://github.com/yireo/Yireo_GoogleTagManager2/pull/312: when this plugin throws, the entire
 * customer section fails to load, so these tests also check that customer/section/load keeps working.
 *
 * In a fresh browser session, Luma does not fetch the customer section by itself: It only reloads sections
 * once a cookie (section_data_ids, section_data_clean) or the localStorage invalidation asks for it. The
 * login via the configure endpoint bypasses the frontend login that would normally trigger this. So the
 * tests reload the section in the browser, which then triggers generic.js to push the GTM data.
 *
 * See gtm-customer-data-hyva-test.spec.ts for Hyva, which loads the sections on every page load.
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
        await reloadCustomerSections(page, ['customer']);

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
        await reloadCustomerSections(page, ['customer']);

        await dataLayer.expectState({
            customerLoggedIn: 1,
            customerGroupCode: 'GENERAL',
        });
        await dataLayer.expectNoDuplicateEvents();
    });
});
