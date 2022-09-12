# TODO

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

```js
dataLayer.push({ ecommerce: null });  // Clear the previous ecommerce object.
  dataLayer.push({
    'event': 'checkout',
    'ecommerce': {
      'checkout': {
        'actionField': {'step': 1, 'option': 'Visa'},
        'products': [{
          'name': 'Triblend Android T-Shirt',
          'id': '12345',
          'price': '15.25',
          'brand': 'Google',
          'category': 'Apparel',
          'variant': 'Gray',
          'quantity': 1
       }]
     }
   },
```

```js
dataLayer.push({
  'ecommerce': {
    'purchase': {
      'actionField': {
        'id': 'T12345',                         // Transaction ID. Required for purchases and refunds.
        'affiliation': 'Online Store',
        'revenue': '35.43',                     // Total transaction value (incl. tax and shipping)
        'tax':'4.90',
        'shipping': '5.99',
        'coupon': 'SUMMER_SALE'
      },
      'products': [{                            // List of productFieldObjects.
        'name': 'Triblend Android T-Shirt',     // Name or ID is required.
        'id': '12345',
        'price': '15.25',
        'brand': 'Google',
        'category': 'Apparel',
        'variant': 'Gray',
        'quantity': 1,
        'coupon': ''                            // Optional fields may be omitted or set to empty string.
       },
       {
        'name': 'Donut Friday Scented T-Shirt',
        'id': '67890',
        'price': '33.75',
        'brand': 'Google',
        'category': 'Apparel',
        'variant': 'Black',
        'quantity': 1
       }]
    }
  }
});
```

## Other things
- `checkoutOption`
- `gtm.dom`
- Add logout event
- Add GA4 support
- MaxServ_DataLayer, Dmg_DataLayer

## Container export and import
See https://support.google.com/tagmanager/answer/6106997?hl=en
Possibly export the right container settings from the XML layout to upload into the GTM dashboard
