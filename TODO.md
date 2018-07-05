# Scenarios to test for

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

