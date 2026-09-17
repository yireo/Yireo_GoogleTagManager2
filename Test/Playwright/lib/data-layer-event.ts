import {expect} from '@playwright/test';
import type {DataLayer, DataLayerEntry} from './data-layer';

export type EventMatcher = Record<string, any> | ((entry: DataLayerEntry) => boolean);

/**
 * Checks whether actual contains everything from expected (like toMatchObject)
 * Supports Playwright asymmetric matchers like expect.any(String)
 */
export function matchesPartial(actual: any, expected: any): boolean {
    if (expected && typeof expected.asymmetricMatch === 'function') {
        return expected.asymmetricMatch(actual);
    }

    if (Array.isArray(expected)) {
        return Array.isArray(actual)
            && actual.length === expected.length
            && expected.every((value, index) => matchesPartial(actual[index], value));
    }

    if (expected !== null && typeof expected === 'object') {
        if (actual === null || typeof actual !== 'object') {
            return false;
        }

        return Object.keys(expected).every(key => matchesPartial(actual[key], expected[key]));
    }

    return Object.is(actual, expected);
}

export function matchesEvent(entry: DataLayerEntry, name: string, matchers: EventMatcher[] = []): boolean {
    if (!entry || entry.event !== name) {
        return false;
    }

    return matchers.every(matcher => {
        if (typeof matcher === 'function') {
            return matcher(entry);
        }

        return matchesPartial(entry, matcher);
    });
}

/**
 * Read-only wrapper around a single event that was pushed to the dataLayer
 */
export class DataLayerEvent {
    readonly name: string;
    readonly data: DataLayerEntry;

    constructor(data: DataLayerEntry) {
        this.name = data.event;
        this.data = data;
    }

    get(path: string): any {
        return path.split('.').reduce((value, key) => {
            return value === undefined || value === null ? undefined : value[key];
        }, this.data);
    }

    getEcommerce(): Record<string, any> | undefined {
        return this.data.ecommerce;
    }

    getItems(): Array<Record<string, any>> {
        const items = this.data.ecommerce?.items ?? [];
        return Array.isArray(items) ? items : Object.values(items);
    }

    getItem(index: number = 0): Record<string, any> | undefined {
        return this.getItems()[index];
    }

    expectToMatch(expected: Record<string, any>) {
        expect(this.data, `Data of event "${this.name}"`).toMatchObject(expected);
    }

    expectItemCount(count: number) {
        expect(this.getItems(), `Items of event "${this.name}"`).toHaveLength(count);
    }

    expectItem(expected: Record<string, any>) {
        expect(this.getItems(), `Items of event "${this.name}"`).toContainEqual(expect.objectContaining(expected));
    }

    /**
     * Validate the event against the GA4 ecommerce event structure
     */
    expectValidEcommerce(options: { requireValue?: boolean, requireQuantity?: boolean, minItems?: number } = {}) {
        const {requireValue = true, requireQuantity = false, minItems = 1} = options;
        const label = `Event "${this.name}"`;
        const ecommerce = this.getEcommerce();

        expect(ecommerce, `${label} has an ecommerce object`).toBeTruthy();
        expect(ecommerce!.currency, `${label} has an ISO currency`).toMatch(/^[A-Z]{3}$/);

        if (requireValue) {
            expect(typeof ecommerce!.value, `${label} has a numeric value`).toBe('number');
            expect(ecommerce!.value, `${label} has a positive value`).toBeGreaterThanOrEqual(0);
        }

        const items = this.getItems();
        expect(items.length, `${label} has at least ${minItems} item(s)`).toBeGreaterThanOrEqual(minItems);

        items.forEach((item, index) => {
            const itemLabel = `${label} item #${index}`;
            expect(typeof item.item_id, `${itemLabel} has an item_id`).toBe('string');
            expect(item.item_id.length, `${itemLabel} has a non-empty item_id`).toBeGreaterThan(0);
            expect(typeof item.item_name, `${itemLabel} has an item_name`).toBe('string');

            if (item.price !== undefined) {
                expect(typeof item.price, `${itemLabel} has a numeric price`).toBe('number');
            }

            if (requireQuantity || item.quantity !== undefined) {
                expect(typeof item.quantity, `${itemLabel} has a numeric quantity`).toBe('number');
                expect(item.quantity, `${itemLabel} has a positive quantity`).toBeGreaterThan(0);
            }
        });
    }
}

/**
 * Assumption about an event that is expected (or not expected) in the dataLayer.
 * All checks are polling, so events that are pushed asynchronously are picked up as well.
 */
export class DataLayerEventAssumption {
    constructor(
        private readonly dataLayer: DataLayer,
        readonly name: string,
        private readonly matchers: EventMatcher[] = []
    ) {
    }

    /**
     * Narrow down the assumption to events matching a partial object or a callback
     */
    where(matcher: EventMatcher): DataLayerEventAssumption {
        return new DataLayerEventAssumption(this.dataLayer, this.name, [...this.matchers, matcher]);
    }

    async all(): Promise<DataLayerEvent[]> {
        const entries = await this.dataLayer.getEntries();
        return entries
            .filter(entry => matchesEvent(entry, this.name, this.matchers))
            .map(entry => new DataLayerEvent(entry));
    }

    async count(): Promise<number> {
        return (await this.all()).length;
    }

    async toBePushed(options: { timeout?: number } = {}): Promise<DataLayerEvent> {
        await expect.poll(() => this.count(), {
            message: `Event "${this.name}" to be pushed to the dataLayer${this.describeMatchers()}`,
            timeout: options.timeout ?? this.dataLayer.timeout,
        }).toBeGreaterThan(0);

        return (await this.all()).at(-1)!;
    }

    async toBePushedTimes(times: number, options: { timeout?: number } = {}): Promise<DataLayerEvent[]> {
        await expect.poll(() => this.count(), {
            message: `Event "${this.name}" to be pushed ${times} time(s)${this.describeMatchers()}`,
            timeout: options.timeout ?? this.dataLayer.timeout,
        }).toBe(times);

        return this.all();
    }

    /**
     * Assume the event is never pushed: Wait for a while and then confirm it is still absent
     */
    async notToBePushed(options: { wait?: number } = {}) {
        await this.dataLayer.page.waitForTimeout(options.wait ?? 1000);
        expect(await this.count(), `Event "${this.name}" not to be pushed${this.describeMatchers()}`).toBe(0);
    }

    /**
     * Assume at least one event of this name matches the expected data
     */
    async toMatch(expected: Record<string, any>, options: { timeout?: number } = {}): Promise<DataLayerEvent> {
        await expect.poll(async () => {
            const events = await this.all();
            const match = events.find(event => matchesPartial(event.data, expected));
            return (match ?? events.at(-1))?.data ?? null;
        }, {
            message: `Event "${this.name}" to match the expected data`,
            timeout: options.timeout ?? this.dataLayer.timeout,
        }).toMatchObject(expected);

        return this.where(expected).toBePushed();
    }

    async toHaveItemCount(count: number): Promise<DataLayerEvent> {
        const event = await this.toBePushed();
        event.expectItemCount(count);
        return event;
    }

    async toHaveItem(expected: Record<string, any>): Promise<DataLayerEvent> {
        const event = await this.toBePushed();
        event.expectItem(expected);
        return event;
    }

    async toHaveValidEcommerce(options: Parameters<DataLayerEvent['expectValidEcommerce']>[0] = {}): Promise<DataLayerEvent> {
        const event = await this.toBePushed();
        event.expectValidEcommerce(options);
        return event;
    }

    private describeMatchers(): string {
        return this.matchers.length > 0 ? ` (with ${this.matchers.length} additional condition(s))` : '';
    }
}
