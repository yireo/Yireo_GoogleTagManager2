# Setting up Google Analytics and Google Tag Manager for GA4 and this extension with consent
Read the [Tutorial](TUTORIAL.md) to get yourself familiar with it. This will only substitute the `Alternatively` and `Magento` parts.

Have a look at the docs for [Tag Manager](https://support.google.com/tagmanager/answer/10718549) to see how to enable & configure consent.

## Alternatively
Download [this JSON file](https://raw.githubusercontent.com/yireo/Yireo_GoogleTagManager2/master/docs/gtm-consent-example.json). Edit it manually and replace the following strings with your own:

- `ACCOUNT_ID` should be replaced with your own numeric account ID (visible in GTM URL)
- `CONTAINER_ID` should be replaced with your own numeric container ID (visible in GTM URL)
- `MESUREMENT_ID` should be replaced with your own numeric container ID (visible in GTM URL)
- `CONTAINER_NAME` should be replaced with your own container name
- `GTM_PUBLIC_ID` should be replaced with your own container public ID (starting with `GTM-`)

After importing the adjusted JSON file make sure to check all tags and variables. Publish the new configuration.

## Magento
The following steps assume that the Yireo module was already installed and enabled properly. 
Within the Magento Admin Panel, navigate to the **Store Configuration** and open up the **Yireo GoogleTagManager** options (under the section **Yireo**). 
Make sure the option **Enabled** is set to **Yes** within the consent group.