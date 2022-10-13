# Configuration
Login to your [Google Tag Manager](http://www.google.com/tagmanager/) account. Follow the Google instructions to
create a new Google Tag Manager **container**. Extract the **Container Public ID** from your new container. Configure
your new ID in Magento through **Store > Configuration > [Sales] Google Tag Manager**.

# Features
The extension has the following configuration options:

- **Enabled**: When this is set to No, the extension does not work.
- **Container Public ID**: The ID of your Google Tags container
- **Insertion Method**: A technical thing which is either set to *Observer* or to *XML Layout*. If the one does not work for you, try the other one instead.
- **Debug**: For developers.

# Tip
When you want to track conversions in your Magento checkout, our extension helps out as well: It adds the relevant information to all your checkout and cart pages. Do you want to know which variables are on the success page? Use the [Yireo CheckoutTester](https://github.com/yireo/Yireo_CheckoutTester2) extension to preview that page and view its HTML source.

Bring your towel.
