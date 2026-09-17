import {expect, Page} from '@playwright/test';
import {DataLayerEvent, DataLayerEventAssumption, EventMatcher, matchesPartial} from './data-layer-event';

export type DataLayerEntry = Record<string, any>;

const GTM_URL_PATTERN = /^https?:\/\/(www\.)?googletagmanager\.com\//;

/**
 * Test object for the GTM dataLayer object (window.dataLayer)
 */
export class DataLayer {
    readonly page: Page;
    readonly timeout: number;
    readonly containerRequests: string[] = [];

    constructor(page: Page, options: { timeout?: number } = {}) {
        this.page = page;
        this.timeout = options.timeout ?? 10_000;
    }

    /**
     * Stub the external GTM container, so that tests are deterministic and GTM itself does not modify the dataLayer
     */
    async install() {
        await this.page.route(GTM_URL_PATTERN, async route => {
            const url = route.request().url();
            this.containerRequests.push(url);

            const isScript = new URL(url).pathname.endsWith('.js');
            await route.fulfill({
                status: 200,
                contentType: isScript ? 'application/javascript' : 'text/html',
                body: '',
            });
        });
    }

    async getEntries(): Promise<DataLayerEntry[]> {
        try {
            return await this.page.evaluate(() => {
                const dataLayer = (window as any).dataLayer;
                return Array.isArray(dataLayer) ? JSON.parse(JSON.stringify(dataLayer)) : [];
            });
        } catch (error) {
            // The page might be navigating: Report an empty dataLayer, so polling assertions simply retry
            if (String(error).includes('Execution context was destroyed')) {
                return [];
            }

            throw error;
        }
    }

    /**
     * Get all pushed events (entries with an "event" property), in order of pushing
     */
    async getEvents(options: { includeGtmEvents?: boolean } = {}): Promise<DataLayerEvent[]> {
        const entries = await this.getEntries();
        return entries
            .filter(entry => typeof entry.event === 'string')
            .filter(entry => options.includeGtmEvents || !entry.event.startsWith('gtm.'))
            .map(entry => new DataLayerEvent(entry));
    }

    async getEventNames(options: { includeGtmEvents?: boolean } = {}): Promise<string[]> {
        return (await this.getEvents(options)).map(event => event.name);
    }

    /**
     * Get the data model like GTM computes it: All pushes merged recursively, in order
     */
    async getState(): Promise<DataLayerEntry> {
        return mergeEntries(await this.getEntries());
    }

    async get(path: string): Promise<any> {
        const state = await this.getState();
        return path.split('.').reduce((value, key) => {
            return value === undefined || value === null ? undefined : value[key];
        }, state);
    }

    async isEnabled(): Promise<boolean> {
        return this.page.evaluate(() => (window as any).YIREO_GOOGLETAGMANAGER2_ENABLED === true);
    }

    /**
     * Start an assumption on a specific event, for instance:
     *   await dataLayer.event('view_item').toHaveValidEcommerce();
     *   await dataLayer.event('add_to_cart').where({ecommerce: {currency: 'USD'}}).toBePushedTimes(1);
     */
    event(name: string, matcher?: EventMatcher): DataLayerEventAssumption {
        return new DataLayerEventAssumption(this, name, matcher ? [matcher] : []);
    }

    async waitForEvent(name: string, matcher?: EventMatcher): Promise<DataLayerEvent> {
        return this.event(name, matcher).toBePushed();
    }

    async expectEnabled() {
        await expect.poll(() => this.isEnabled(), {message: 'GTM module to be enabled'}).toBe(true);
    }

    async expectDisabled() {
        expect(await this.isEnabled(), 'GTM module to be disabled').toBe(false);
    }

    /**
     * Assume the merged dataLayer state contains the expected data
     */
    async expectState(expected: DataLayerEntry) {
        await expect.poll(() => this.getState(), {
            message: 'dataLayer state to match the expected data',
            timeout: this.timeout,
        }).toMatchObject(expected);
    }

    /**
     * Assume that at least one single push contains the expected data
     */
    async expectEntry(expected: DataLayerEntry) {
        await expect.poll(async () => {
            const entries = await this.getEntries();
            return entries.find(entry => matchesPartial(entry, expected)) ?? entries;
        }, {
            message: 'dataLayer to contain an entry matching the expected data',
            timeout: this.timeout,
        }).toMatchObject(expected);
    }

    /**
     * Assume that the given events are pushed in this order (other events may be pushed in between)
     */
    async expectEventOrder(names: string[]) {
        await expect.poll(async () => {
            const actualNames = await this.getEventNames();
            let position = 0;
            for (const actualName of actualNames) {
                if (actualName === names[position]) {
                    position++;
                }
            }

            return names.slice(0, position);
        }, {
            message: `Events to be pushed in the order: ${names.join(', ')}`,
            timeout: this.timeout,
        }).toEqual(names);
    }

    /**
     * Assume that no event is pushed twice with exactly the same data
     */
    async expectNoDuplicateEvents() {
        const serialized = (await this.getEvents()).map(event => JSON.stringify(event.data));
        const duplicates = serialized.filter((value, index) => serialized.indexOf(value) !== index);
        expect(duplicates, 'Duplicate dataLayer events').toEqual([]);
    }

    /**
     * Assume that the GTM container script was requested for the given ID
     */
    async expectContainerLoaded(gtmId: string) {
        await expect.poll(() => this.containerRequests.some(url => {
            const parsedUrl = new URL(url);
            return parsedUrl.pathname.endsWith('/gtm.js') && parsedUrl.searchParams.get('id') === gtmId;
        }), {
            message: `GTM container "${gtmId}" to be loaded`,
            timeout: this.timeout,
        }).toBe(true);
    }

    async expectContainerNotLoaded(options: { wait?: number } = {}) {
        await this.page.waitForTimeout(options.wait ?? 1000);
        const scripts = this.containerRequests.filter(url => new URL(url).pathname.endsWith('/gtm.js'));
        expect(scripts, 'GTM container requests').toEqual([]);
    }
}

function isPlainObject(value: any): boolean {
    return value !== null && typeof value === 'object' && !Array.isArray(value);
}

function mergeEntries(entries: DataLayerEntry[]): DataLayerEntry {
    const merge = (target: DataLayerEntry, source: DataLayerEntry) => {
        for (const [key, value] of Object.entries(source)) {
            if (isPlainObject(value) && isPlainObject(target[key])) {
                merge(target[key], value);
                continue;
            }

            target[key] = isPlainObject(value) ? merge({}, value) : value;
        }

        return target;
    };

    return entries.filter(isPlainObject).reduce((state, entry) => merge(state, entry), {});
}
