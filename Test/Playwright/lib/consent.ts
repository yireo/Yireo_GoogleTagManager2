import {expect, Locator, Page} from '@playwright/test';

export const CONSENT_BANNER = '#yireo-cookie-consent-banner';

export const CONSENT_PR_URL = 'https://github.com/yireo/Yireo_GoogleTagManager2/pull/198';

export type ConsentMode = 'default' | 'update';

export type ConsentModes = {
    necessary: boolean;
    analytics: boolean;
    preferences: boolean;
    marketing: boolean;
};

export type ConsentCommand = {
    index: number;
    isArguments: boolean;
    mode: string;
    params: Record<string, string>;
};

export function consentConfig(config: Record<string, any> = {}): Record<string, any> {
    return {
        'googletagmanager2/consent/enabled': 1,
        'googletagmanager2/consent/privacy_statement_url': '',
        ...config,
    };
}

/**
 * The consent parameters that GTM should receive for the given choice
 */
export function expectedConsentParams(modes: ConsentModes): Record<string, string> {
    const state = (granted: boolean) => granted ? 'granted' : 'denied';
    return {
        functionality_storage: 'granted',
        security_storage: 'granted',
        ad_storage: state(modes.marketing),
        ad_user_data: state(modes.marketing),
        ad_personalization: state(modes.marketing),
        analytics_storage: state(modes.analytics),
        personalization_storage: state(modes.preferences),
    };
}

/**
 * Test object for the consent banner and the gtag('consent', ...) commands in the dataLayer
 */
export class Consent {
    readonly page: Page;
    readonly pageErrors: string[] = [];

    constructor(page: Page) {
        this.page = page;
        page.on('pageerror', error => this.pageErrors.push(error.message));
    }

    get banner(): Locator {
        return this.page.locator(CONSENT_BANNER);
    }

    checkbox(mode: keyof ConsentModes): Locator {
        return this.page.locator('#yireo-consent-' + mode);
    }

    button(label: 'Accept All' | 'Accept Selection' | 'Reject All'): Locator {
        return this.banner.getByRole('button', {name: label, exact: true});
    }

    async isAvailable(): Promise<boolean> {
        return (await this.banner.count()) > 0;
    }

    /**
     * All consent commands in the dataLayer. Next to a real gtag() Arguments object, an array or a plain object
     * with numeric keys is recognized too, so that wrongly pushed commands can still be inspected.
     */
    async getCommands(): Promise<ConsentCommand[]> {
        return this.page.evaluate(() => {
            const dataLayer = (window as any).dataLayer;
            if (!Array.isArray(dataLayer)) {
                return [];
            }

            const commands: ConsentCommand[] = [];
            dataLayer.forEach((entry: any, index: number) => {
                if (!entry || typeof entry !== 'object' || entry[0] !== 'consent') {
                    return;
                }

                commands.push({
                    index,
                    isArguments: Object.prototype.toString.call(entry) === '[object Arguments]',
                    mode: String(entry[1]),
                    params: JSON.parse(JSON.stringify(entry[2] ?? {})),
                });
            });

            return commands;
        });
    }

    async waitForCommand(mode: ConsentMode, occurrence: 'first' | 'last' = 'last'): Promise<ConsentCommand> {
        let found: ConsentCommand | undefined;
        await expect.poll(async () => {
            const commands = (await this.getCommands()).filter(command => command.mode === mode);
            found = occurrence === 'first' ? commands[0] : commands[commands.length - 1];
            return found !== undefined;
        }, {message: `dataLayer to contain a consent "${mode}" command`}).toBe(true);

        return found!;
    }

    async expectCommand(mode: ConsentMode, modes: ConsentModes) {
        await expect.poll(async () => {
            const commands = (await this.getCommands()).filter(command => command.mode === mode);
            return commands.length > 0 ? commands[commands.length - 1].params : commands;
        }, {message: `consent "${mode}" command to match the choice ${JSON.stringify(modes)}`})
            .toEqual(expectedConsentParams(modes));
    }

    async getStoredModes(): Promise<ConsentModes | null> {
        return this.page.evaluate(() => {
            const value = localStorage.getItem('yireoGoogleTagManager2ConsentModes');
            return value === null ? null : JSON.parse(value);
        });
    }

    async expectStoredModes(modes: ConsentModes) {
        await expect.poll(() => this.getStoredModes(), {message: 'stored consent choice'}).toEqual(modes);
    }

    async expectNoPageErrors() {
        expect(this.pageErrors, 'JavaScript errors on the page').toEqual([]);
    }
}
