define([
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, alert) {
    'use strict';

    return function (config) {
        $(config.buttonId).on('click', function () {
            $.ajax({
                url: config.ajaxUrl,
                type: 'GET',
                dataType: 'json',
                showLoader: true,
                success: function (response) {
                    if (response.success) {
                        // Convert base64 to blob
                        const byteCharacters = atob(response.content);
                        const byteNumbers = new Array(byteCharacters.length);
                        for (let i = 0; i < byteCharacters.length; i++) {
                            byteNumbers[i] = byteCharacters.charCodeAt(i);
                        }
                        const byteArray = new Uint8Array(byteNumbers);
                        const blob = new Blob([byteArray], { type: response.mimeType });

                        // Create download link
                        const url = window.URL.createObjectURL(blob);
                        const link = document.createElement('a');
                        link.href = url;
                        link.download = response.filename;
                        document.body.appendChild(link);
                        link.click();
                        
                        // Cleanup
                        document.body.removeChild(link);
                        window.URL.revokeObjectURL(url);
                    } else {
                        alert({
                            title: $.mage.__('Error'),
                            content: response.message || $.mage.__('An error occurred while downloading the debug data.')
                        });
                    }
                },
                error: function () {
                    alert({
                        title: $.mage.__('Error'),
                        content: $.mage.__('An error occurred while downloading the debug data.')
                    });
                }
            });
        });
    };
}); 