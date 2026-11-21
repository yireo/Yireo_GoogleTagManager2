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

function createElement(tagName, { id = '', value = '', parentElement = null } = {}) {
    return { tagName: tagName.toUpperCase(), id, value, parentElement, children: [] };
}

function attach(parent, child) {
    child.parentElement = parent;
    parent.children.push(child);
    return child;
}

function matchesSelector(element, selector) {
    if (selector === 'input[name="product"]') {
        return element.tagName === 'INPUT' && element.name === 'product';
    }

    const prefix = selector.match(/^\[id\^="(.+)"\]$/);
    return Boolean(prefix) && element.id.startsWith(prefix[1]);
}

function decorate(element) {
    element.querySelector = function (selector) {
        for (const child of this.children) {
            if (matchesSelector(child, selector)) {
                return child;
            }
            const nested = child.querySelector(selector);
            if (nested) {
                return nested;
            }
        }
        return null;
    };
    element.closest = function (selector) {
        for (let current = this; current; current = current.parentElement) {
            if (matchesSelector(current, selector)) {
                return current;
            }
        }
        return null;
    };
    return element;
}

function buildTile({ withForm, withInput, tileId = 'product-item-info_42' }) {
    const tile = decorate(createElement('div', { id: tileId }));
    let container = tile;
    if (withForm) {
        container = attach(tile, decorate(createElement('form')));
    }
    if (withInput) {
        const input = attach(container, decorate(createElement('input', { value: '42' })));
        input.name = 'product';
    }
    const link = attach(container, decorate(createElement('a')));
    return { tile, link };
}

function clickProduct(link) {
    let clickHandler;
    const productLink = {
        addEventListener(type, handler) {
            if (type === 'click') {
                clickHandler = handler;
            }
        }
    };
    const pushes = [];
    const context = vm.createContext({
        window: { YIREO_GOOGLETAGMANAGER2_PRODUCT_DATA_ID_42: { item_name: 'Sample' } },
        document: { querySelectorAll: () => [productLink] },
        confirm: () => true,
        yireoGoogleTagManager2Pusher(eventData) {
            pushes.push(eventData);
        }
    });
    const script = readTemplateScript('view/frontend/templates/hyva/script-product-clicks.phtml')
        .replace(/<\?=[\s\S]*?\?>/g, 'a');

    vm.runInContext(script, context, { filename: 'script-product-clicks.phtml' });
    clickHandler({ target: link, preventDefault() {} });

    return pushes;
}

describe('Hyva product click tracking', function () {
    it('pushes select_item with the id from the product input of the form', function () {
        const { link } = buildTile({ withForm: true, withInput: true, tileId: 'unrelated' });
        const pushes = clickProduct(link);

        assert.strictEqual(pushes.length, 1);
        assert.strictEqual(pushes[0].ecommerce.items[0].item_id, '42');
    });

    it('does not throw and still pushes select_item when the form has no product input', function () {
        const { link } = buildTile({ withForm: true, withInput: false });
        const pushes = clickProduct(link);

        assert.strictEqual(pushes.length, 1);
        assert.strictEqual(pushes[0].ecommerce.items[0].item_id, '42');
    });

    it('still pushes select_item when the tile has no form at all', function () {
        const { link } = buildTile({ withForm: false, withInput: false });
        const pushes = clickProduct(link);

        assert.strictEqual(pushes.length, 1);
        assert.strictEqual(pushes[0].ecommerce.items[0].item_id, '42');
    });

    it('pushes nothing when no product id can be found', function () {
        const { link } = buildTile({ withForm: true, withInput: false, tileId: 'unrelated' });

        assert.deepStrictEqual(clickProduct(link), []);
    });
});
