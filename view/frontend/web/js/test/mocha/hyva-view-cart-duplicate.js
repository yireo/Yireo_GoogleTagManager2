const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const moduleRoot = path.resolve(__dirname, '../../../../../..');

function readTemplateScript(relativePath) {
    const template = fs.readFileSync(path.join(moduleRoot, relativePath), 'utf8');
    const match = template.match(/<script>\s*([\s\S]*?)\s*<\/script>/);
    assert.ok(match, `No <script> block found in ${relativePath}`);
    return match[1];
}

function createRuntime(pathname = '/checkout/cart/') {
    const listeners = new Map();

    const window = {
        location: { pathname },
        dataLayer: [],
        localStorage: {
            setItem() {},
            getItem() { return null; }
        },
        addEventListener(type, handler) {
            const handlers = listeners.get(type) || [];
            handlers.push(handler);
            listeners.set(type, handlers);
        },
        dispatchEvent(event) {
            for (const handler of listeners.get(event.type) || []) {
                handler(event);
            }
            return true;
        }
    };

    class CustomEvent {
        constructor(type, init = {}) {
            this.type = type;
            this.detail = init.detail;
        }
    }

    const context = vm.createContext({
        window,
        CustomEvent,
        console,
        encodeURIComponent,
        btoa(value) {
            return Buffer.from(value, 'binary').toString('base64');
        },
        yireoGoogleTagManager2Logger() {}
    });

    vm.runInContext(
        readTemplateScript('view/frontend/templates/hyva/script-pusher.phtml'),
        context,
        { filename: 'script-pusher.phtml' }
    );
    vm.runInContext(
        readTemplateScript('view/frontend/templates/hyva/script-additions.phtml'),
        context,
        { filename: 'script-additions.phtml' }
    );

    return { context, window, CustomEvent };
}

function viewCartEvent({ value, quantity, allowedEvents = [] }) {
    return {
        meta: {
            cacheable: true,
            allowed_pages: [],
            allowed_events: allowedEvents
        },
        event: 'view_cart',
        ecommerce: {
            currency: 'USD',
            value,
            items: [{ item_id: 'sku-1', quantity }]
        }
    };
}

function dispatchPrivateContent(runtime, cartEvent) {
    runtime.window.dispatchEvent(new runtime.CustomEvent('private-content-loaded', {
        detail: {
            data: {
                cart: {
                    gtm_events: {
                        view_cart_event: cartEvent
                    }
                },
                customer: {}
            }
        }
    }));
}

function dispatchToggleCart(runtime, isOpen = true) {
    runtime.window.dispatchEvent(new runtime.CustomEvent('toggle-cart', {
        detail: { isOpen }
    }));
}

function getViewCartPushes(window) {
    return window.dataLayer.filter(entry => entry && entry.event === 'view_cart');
}

describe('Hyva view_cart duplicate handling', function () {
    it('deduplicates the same view_cart payload from page and customer data', function () {
        const runtime = createRuntime();
        const event = viewCartEvent({ value: 10, quantity: 1 });

        runtime.context.yireoGoogleTagManager2Pusher(event, 'push (page-based event) [data-layer.phtml]');
        dispatchPrivateContent(runtime, JSON.parse(JSON.stringify(event)));

        assert.strictEqual(getViewCartPushes(runtime.window).length, 1);
    });

    it('does not replay a stale customer-data view_cart after the cart page already emitted the current view_cart', function () {
        const runtime = createRuntime();
        const currentPageEvent = viewCartEvent({ value: 20, quantity: 2 });
        const staleCustomerDataEvent = viewCartEvent({ value: 10, quantity: 1 });

        runtime.context.yireoGoogleTagManager2Pusher(
            currentPageEvent,
            'push (page-based event) [data-layer.phtml]'
        );
        dispatchPrivateContent(runtime, staleCustomerDataEvent);

        const viewCartPushes = getViewCartPushes(runtime.window);
        assert.strictEqual(
            viewCartPushes.length,
            1,
            `Expected one view_cart for one cart view, received ${viewCartPushes.length}`
        );
        assert.strictEqual(viewCartPushes[0].ecommerce.value, 20);
        assert.strictEqual(viewCartPushes[0].ecommerce.items[0].quantity, 2);
    });

    it('does not emit extra view_cart events when cart customer data reloads after quantity updates', function () {
        const runtime = createRuntime();
        const initialPageEvent = viewCartEvent({ value: 10, quantity: 1 });

        runtime.context.yireoGoogleTagManager2Pusher(
            initialPageEvent,
            'push (page-based event) [data-layer.phtml]'
        );
        dispatchPrivateContent(runtime, viewCartEvent({ value: 20, quantity: 2 }));
        dispatchPrivateContent(runtime, viewCartEvent({ value: 30, quantity: 3 }));

        assert.strictEqual(getViewCartPushes(runtime.window).length, 1);
    });

    it('keeps minicart-triggered view_cart working away from the cart page', function () {
        const runtime = createRuntime('/category/example/');
        const customerEvent = viewCartEvent({
            value: 10,
            quantity: 1,
            allowedEvents: ['minicart_collapse']
        });

        dispatchPrivateContent(runtime, customerEvent);
        assert.strictEqual(getViewCartPushes(runtime.window).length, 0);

        dispatchToggleCart(runtime, true);
        assert.strictEqual(getViewCartPushes(runtime.window).length, 1);
    });

    it('uses the current cart-page payload once when minicart expansion is the configured trigger', function () {
        const runtime = createRuntime();
        const currentPageEvent = viewCartEvent({
            value: 20,
            quantity: 2,
            allowedEvents: ['minicart_collapse']
        });
        const staleCustomerDataEvent = viewCartEvent({
            value: 10,
            quantity: 1,
            allowedEvents: ['minicart_collapse']
        });

        runtime.context.yireoGoogleTagManager2Pusher(
            currentPageEvent,
            'push (page-based event) [data-layer.phtml]'
        );
        dispatchPrivateContent(runtime, staleCustomerDataEvent);
        assert.strictEqual(getViewCartPushes(runtime.window).length, 0);

        dispatchToggleCart(runtime, true);

        const viewCartPushes = getViewCartPushes(runtime.window);
        assert.strictEqual(viewCartPushes.length, 1);
        assert.strictEqual(viewCartPushes[0].ecommerce.value, 20);
        assert.strictEqual(viewCartPushes[0].ecommerce.items[0].quantity, 2);
    });

    it('treats a browser refresh as a new cart view while still emitting once per page load', function () {
        for (let pageLoad = 0; pageLoad < 2; pageLoad += 1) {
            const runtime = createRuntime();
            const currentPageEvent = viewCartEvent({ value: 20, quantity: 2 });
            const cachedCustomerEvent = viewCartEvent({ value: 10, quantity: 1 });

            runtime.context.yireoGoogleTagManager2Pusher(
                currentPageEvent,
                'push (page-based event) [data-layer.phtml]'
            );
            dispatchPrivateContent(runtime, cachedCustomerEvent);

            assert.strictEqual(getViewCartPushes(runtime.window).length, 1);
        }
    });
    it('does not suppress other ecommerce events from the cart customer section', function () {
        const runtime = createRuntime();
        const addToCartEvent = viewCartEvent({ value: 10, quantity: 1 });
        addToCartEvent.event = 'add_to_cart';

        dispatchPrivateContent(runtime, addToCartEvent);

        const addToCartPushes = runtime.window.dataLayer.filter(
            entry => entry && entry.event === 'add_to_cart'
        );
        assert.strictEqual(addToCartPushes.length, 1);
    });

});
