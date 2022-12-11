# Setting up Google Analytics and Google Tag Manager for GA4 and this extension

This guide helps you setup with Google Analytics and Google Tag Manager. Note that this guide was written without being a Google expert and only to make the Magento extension work as quickly as possible. If you get stuck while reading this tutorial, it could be that the wording of this tutorial could be improved: Feel free to make a Pull Request. It could also be that you are lacking knowledge for using the Google consoles. Unfortunately, we are not providing support for either Google Tag Manager and Google Analytics, we are only providing technical support for this Magento extension.

First of all, make sure you have existing accounts for both [Google Analytics](https://analytics.google.com/) and [Google Tag Manager](https://tagmanager.google.com/). The idea is to make sure this Magento extension sends data to Google Tag Manager, which then forwards this traffic to Google Analytics. Best is to start with configuring Google Analytics.

## Google Analytics
Login into Google Analytics. Create a new **App** for your site. Within that **App**, under **Admin**, navigate to **Data Streams**. Create a new **Web** stream. Under the **Web** stream details, write down the **Measurement ID** (starting with `G-`) for later use.

## Google Tag Manager
Login into Google Tag Manager. Create a new **Container** for your site. Write down the **Container ID** (starting with `GTM-`) to configure later in the Magento extension. In the **Container Workspace**, create a new **Tag**. As **Tag Configuration** type, choose **Google Analytics > GA4 Configuration**. Fill in the Google Analytics **Measurement ID** (starting with `G-`). Make sure to trigger this tag on all pages.

Next, create a second **Tag** in the same **Container Workspace**, this time of type **Google Analytics > GA4 Events**. Set `{{Event}}` to be the **Event Name**. Next, enter the following **Event Parameters**:

- Parameter name `items` with value `{{Ecommerce Items}}`
- Parameter name `transaction_id` with value `{{Ecommerce Transaction ID}}`
- Parameter name `affiliation` with value `{{Ecommerce Affiliation}}`
- Parameter name `value` with value `{{Ecommerce Value}}`
- Parameter name `tax` with value `{{Ecommerce Tax}}`
- Parameter name `shipping` with value `{{Ecommerce Shipping}}`
- Parameter name `currency` with value `{{Ecommerce Currency}}`
- Parameter name `coupon` with value `{{Ecommerce Coupon}}`

Alternatively, import [this JSON file](docs/gtm-example.json) with all relevant settings. Under **More Settings**, enable the flag **Send Ecommerce data** and use as the **Data Source** the option **Data Layer**.

Once the two tags are configured in Google Tag Manager, use **Publish** to publish your new configuration as a new version.

## Magento
The following steps assume that the Yireo module was already installed and enabled properly. Within the Magento Admin Panel, navigate to the **Store Configuration** and open up the **Yireo GoogleTagManager** options (under the section **Yireo**). Make sure the option **Enabled** is set to **Yes**. Configure the Google Tag Manager key starting with `GTM-` under **Container Public ID**. Enable **Debug** to make sure everything is working fine.

## Previewing & debugging
If all steps were followed correctly (and debugging is enabled in the Magento extension), you should be able to see debugging messages in the **Error Console** of your browser, once you navigate through your frontend. If no error messages are mentioned and the Google Tag Manager key seems to be correct as well, this should mean that data from Magento should be sent correctly to Google Tag Manager.

In Google Analytics, go to **Reports**: There should be 1 user reported there. Next, under **Reports > Realtime**, you should be seeing real-life traffic coming in. Likewise, under **Engagement > Events**, there should be multiple events. Note that these reports (except for the **Realtime** report) could be taking 24 hours to be detected. Use the **Realtime** page to get direct results.

In the Google Tag Manager, use **Preview** in your workspace to open up a new browser window to your Magento shop in debugging mode. Next, within Google Analytics, use the **DebugView**. Besides generic events like `pageview`, there should also be more specific e-commerce events like `view_item_list` and `view_item`. 
