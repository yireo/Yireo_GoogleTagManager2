# Configuration
Before you can start you need a [AdPage (Tagging)](https://trytagging.com/auth/register) account. Configure
your settings in Magento through **Admin Panel > Stores > Configuration > AdPage > AdPage GTM**.

# Features
The extension has the following configuration options:

- **Enabled**: When this is set to No, the extension does not work.
- **Container HEAD code**: The HEAD code found in the trytagging dashboard. Excluding script tags only what is inside the script tag.
- **Container URL**: The URL you connected in the trytagging dashboard.
- **Debug**: Enable this for additional debugging in a logfile and the browser console.
- **Choose script placement**: Setting this option to YES will remove the tracking script from the page. Only use this option if you want to choose where the script is placed in the page. This option is not recommended for most users.

# Tip: Browser extension
Use the [DataLayer
Checker](https://chrome.google.com/webstore/detail/datalayer-checker/ffljdddodmkedhkcjhpmdajhjdbkogke) for Chrome to
easily see what kind of data is sent from Magento to Google Tag Manager.
 
# Tip: CheckoutTester2
When you want to track conversions in your Magento checkout, you can check this extension: It adds the relevant information to all your checkout and cart pages. Do you want to know which variables are on the success page? Use the [Yireo CheckoutTester](https://github.com/yireo/Yireo_CheckoutTester2) extension to preview that page and view its HTML source.
