/**
 * Cmsbox.fr Magento 2 Payment module (https://www.cmsbox.fr)
 *
 * Copyright (c) 2017 Cmsbox.fr (https://www.cmsbox.fr)
 * Author: David Fiaty | contact@cmsbox.fr
 *
 * License GNU/GPL V3 https://www.gnu.org/licenses/gpl-3.0.en.html
 */

/*
var config = {
    map: {
        '*': {
            'Magento_Checkout/js/model/place-order': 'Cmsbox_Mercanet/js/model/place-order',
            'Magento_Checkout/js/model/error-processor': 'Cmsbox_Mercanet/js/model/error-processor'
        }
    }
};
*/

var config = {
    config: {
            mixins: {
                'Magento_Ui/js/view/messages': {
                    'Cmsbox_Mercanet/js/messages-mixin': true
                }
            }
        }
    };