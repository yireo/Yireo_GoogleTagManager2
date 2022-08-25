# TODO

```js
dataLayer.push({ ecommerce: null });
dataLayer.push({
    'event': 'productClick',
    'ecommerce': {
      'click': {
        'actionField': {'list': 'Search Results'},      // Optional list property.
        'products': [{
          'name': productObj.name,                      // Name or ID is required.
          'id': productObj.id,
          'price': productObj.price,
          'brand': productObj.brand,
          'category': productObj.cat,
          'variant': productObj.variant,
          'position': productObj.position
         }]
       }
     },
     'eventCallback': function() {
       document.location = productObj.url
     }
  });
```

```js
dataLayer.push({ ecommerce: null });
dataLayer.push({
  'event': 'removeFromCart',
  'ecommerce': {
    'remove': {                               // 'remove' actionFieldObject measures.
      'products': [{                          //  removing a product to a shopping cart.
          'name': 'Triblend Android T-Shirt',
          'id': '12345',
          'price': '15.25',
          'brand': 'Google',
          'category': 'Apparel',
          'variant': 'Gray',
          'quantity': 1
      }]
    }
  }
});
```

```js
dataLayer.push({ ecommerce: null });
dataLayer.push({
  'ecommerce': {
    'promoView': {
      'promotions': [
       {
         'id': 'JUNE_PROMO13',
         'name': 'June Sale',
         'creative': 'banner1',
         'position': 'slot1'
       },
       {
         'id': 'FREE_SHIP13',
         'name': 'Free Shipping Promo',
         'creative': 'skyscraper1',
         'position': 'slot2'
       }]
    }
  }
});
```

## Other triggers
- `checkout`
- `checkoutOption`
- `gtm.dom`

## Container export and import
See https://support.google.com/tagmanager/answer/6106997?hl=en
Possibly export the right container settings from the XML layout to upload into the GTM dashboard

## Scenarios to test for

## Duplicate gtm.js calls
- Load the page
- Monitor outgoing requests
- There should be only 1 call to Google Tag Manager URL

## Unneeded section reloads
- Load the homepage
- Reload the homepage
- Monitor outgoing requests
- There should be zero calls to customer/section/load URL

## Document should contain dataLayer
- Load the homepage
- Check window.dataLayer
- It should not be empty

## Add a product to cart
- Check if window.dataLayer is updated

## Login
Generate an event like the following only once:
```html
<script>
window.dataLayer = window.dataLayer || [];
window.dataLayer.push({
 'event' : 'login',
 'loginMethod' : 'email' // this should be replaced by your developer
});
</script>
```
