const assert = require('assert');
const fs = require('fs');
const path = require('path');
const vm = require('vm');

const moduleRoot = path.resolve(__dirname, '../../../../../..');

function createRuntime(pathname, cartSectionData) {
    const windowListeners = new Map();
    const pushes = [];
    let cartSubscriber = null;
    let cartData = cartSectionData;

    const window = {location: {pathname}};

    const $ = function (target) {
        assert.strictEqual(target, window);
        return {
            on(type, handler) {
                const handlers = windowListeners.get(type) || [];
                handlers.push(handler);
                windowListeners.set(type, handlers);
            }
        };
    };
    $.extend = Object.assign;

    const customerData = {
        get(sectionName) {
            const observable = function () {
                return sectionName === 'cart' ? cartData : {};
            };
            observable.subscribe = function (callback) {
                if (sectionName === 'cart') {
                    cartSubscriber = callback;
                }
            };
            return observable;
        },
        set() {}
    };

    const pusher = function (eventData, message) {
        pushes.push({eventData, message});
    };

    let factory = null;
    const context = vm.createContext({
        window,
        define(dependencies, callback) {
            factory = callback;
        }
    });

    vm.runInContext(
        fs.readFileSync(path.join(moduleRoot, 'view/frontend/web/js/generic.js'), 'utf8'),
        context,
        {filename: 'generic.js'}
    );

    const Component = {
        extend(definition) {
            return definition;
        }
    };
    const component = factory($, {}, Component, customerData, function () {}, pusher, {});
    component.initialize({id: 'GTM-TEST'});

    return {
        pushes,
        listenerCount(type) {
            return (windowListeners.get(type) || []).length;
        },
        trigger(type) {
            for (const handler of windowListeners.get(type) || []) {
                handler();
            }
        },
        reloadCart(newCartData) {
            cartData = newCartData;
            cartSubscriber();
        }
    };
}

function cartSection(event) {
    return {gtm_events: {view_cart_event: event}};
}

function viewCartEvent({value, allowedEvents = []}) {
    return {
        meta: {cacheable: true, allowed_pages: [], allowed_events: allowedEvents},
        event: 'view_cart',
        ecommerce: {currency: 'USD', value, items: [{item_id: 'sku-1', quantity: 1}]}
    };
}

function viewCartPushes(runtime) {
    return runtime.pushes.filter(push => push.eventData.event === 'view_cart');
}

describe('Luma generic.js allowed_events handling', function () {
    it('pushes a customerData view_cart with empty allowed_events away from the cart page', function () {
        const runtime = createRuntime('/category/example/', cartSection(viewCartEvent({value: 10})));

        assert.strictEqual(viewCartPushes(runtime).length, 1);
        assert.strictEqual(runtime.listenerCount('minicart_collapse'), 0);
    });

    it('skips the customerData view_cart on the cart page', function () {
        const runtime = createRuntime('/checkout/cart/', cartSection(viewCartEvent({value: 10})));

        assert.strictEqual(viewCartPushes(runtime).length, 0);
    });

    it('does not skip other customerData events on the cart page', function () {
        const addToCart = viewCartEvent({value: 10});
        addToCart.event = 'add_to_cart';
        const runtime = createRuntime('/checkout/cart/', cartSection(addToCart));

        assert.strictEqual(runtime.pushes.filter(push => push.eventData.event === 'add_to_cart').length, 1);
    });

    it('registers a single listener and pushes the newest payload after repeated reloads', function () {
        const runtime = createRuntime(
            '/category/example/',
            cartSection(viewCartEvent({value: 10, allowedEvents: ['minicart_collapse']}))
        );
        runtime.reloadCart(cartSection(viewCartEvent({value: 20, allowedEvents: ['minicart_collapse']})));
        runtime.reloadCart(cartSection(viewCartEvent({value: 30, allowedEvents: ['minicart_collapse']})));

        assert.strictEqual(viewCartPushes(runtime).length, 0);
        assert.strictEqual(runtime.listenerCount('minicart_collapse'), 1);

        runtime.trigger('minicart_collapse');

        const pushes = viewCartPushes(runtime);
        assert.strictEqual(pushes.length, 1);
        assert.strictEqual(pushes[0].eventData.ecommerce.value, 30);
    });
});
