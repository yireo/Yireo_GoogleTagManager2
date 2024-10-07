# Configuration
Login to your [Google Tag Manager](http://www.google.com/tagmanager/) account. Follow the Google instructions to
create a new Google Tag Manager **container**. Extract the **Container Public ID** from your new container. Configure
your new ID in Magento through **Admin Panel > Stores > Configuration > Yireo > Yireo GoogleTagManager**.

# Features
The extension has the following configuration options:

- **Enabled**: When this is set to No, the extension does not work.
- **Container Public ID**: The ID of your Google Tags container (starting with `GTM-`).
- **Debug**: Enable this for additional debugging in a logfile and the browser console.
- **Debug Clicks**: Enable this for additional debugging when clicking on specific links.
- **Maximum Products in Category**: Instead of listing all products on a specific category page, only list the first products.
- **Product EAV Attributes**: Product attributes to include when listing products in a specific event.
- **Category EAV Attributes**: Category attributes to include when listing categories in a specific event.
- **Customer EAV Attributes**: Customer attributes to include when listing customers in a specific event. Currently this is not implemented fully because of privacy regulations.

# Implemented events
The following events are implemented in the current version:

- `add_payment_info`
- `add_shipping_info`
- `add_to_cart`
- `add_to_wishlist`
- `begin_checkout`
- `login`
- `logout`
- `purchase`
- `remove_from_cart`
- `select_item`
- `sign_up`
- `view_cart`
- `view_item_list`
- `view_item`
- `view_search_result`

If you think an event is missing, post a GitHub Issue to kickstart such an initiative.

The following events are not implemented because a lack of inspiration:
- `view_promotion`
- `select_promotion`

The following events should actually be implemented via a server-side implementation:
- `refund`

# Tip: Browser extension
Use the [DataLayer Checker](https://chrome.google.com/webstore/detail/datalayer-checker/ffljdddodmkedhkcjhpmdajhjdbkogke) for Chrome to easily see what kind of data is sent from Magento to Google Tag Manager.
 
# Tip: CheckoutTester2
When you want to track conversions in your Magento checkout, our extension helps out as well: It adds the relevant information to all your checkout and cart pages. Do you want to know which variables are on the success page? Use the [Yireo CheckoutTester](https://github.com/yireo/Yireo_CheckoutTester2) extension to preview that page and view its HTML source.

Bring your towel.
