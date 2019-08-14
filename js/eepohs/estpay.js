/**
 * estpay.js
 *
 * PHP version 5
 *
 * @category   Magento
 * @package    Eepohs
 * @subpackage Estpay
 * @author     Eepohs OÜ <info@eepohs.com>
 * @license    http://opensource.org/licenses/bsd-license.php BSDL
 * @link       http://eepohs.com/
 */

/**
 * Estpay custom javascript for backend
 *
 * PLEASE READ THIS SOFTWARE LICENSE AGREEMENT ("LICENSE") CAREFULLY
 * BEFORE USING THE SOFTWARE. BY USING THE SOFTWARE, YOU ARE AGREEING
 * TO BE BOUND BY THE TERMS OF THIS LICENSE.
 * IF YOU DO NOT AGREE TO THE TERMS OF THIS LICENSE, DO NOT USE THE SOFTWARE.
 *
 * Copyright (c) 2012, Eepohs OÜ
 * All rights reserved.
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 * Redistributions of source code must retain the above copyright notice, this
 * list of conditions and the following disclaimer.
 * Redistributions in binary form must reproduce the above copyright notice,
 * this list of conditions and the following disclaimer in the documentation
 * and/or other materials provided with the distribution.
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"
 * AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE
 * IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE
 * ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE
 * LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR
 * CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF
 * SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS
 * INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN
 * CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF
 * THE POSSIBILITY OF SUCH DAMAGE.
 *
 * @category   Community
 * @package    Eepohs
 * @subpackage Estpay
 * @author     Eepohs OÜ <info@eepohs.com>
 * @copyright  2012 Eepohs OÜ
 * @license    http://opensource.org/licenses/bsd-license.php BSDL
 * @version    Release: 1.3.2.3
 * @link       http://eepohs.com/
 */
Event.observe(window, 'load', function() {
    initEepohsPayment();
});

EepohsPayment = Class.create();
EepohsPayment.prototype = {
    initialize: function(){

    },
    /**
     * @elements array of element id-s / payment methods
     */
    addLogoTypes: function(elementIds){
        $A(elementIds).each(
            function(item){
                var img = document.createElement('img');
                Element.extend(img);
                img.src = EEPOHS_SKIN_URL + 'images/eepohs/estpay/eepohs_logo_small.png';
                img.addClassName('eepohs-logo title-logo');
                img.writeAttribute('alt', 'Eepohs');
                img.writeAttribute('title', 'Eepohs');
                $(item).appendChild(img);
            }
            );
    },
    addCss: function(elementClass, classPrefixes){
        $A(classPrefixes).each(function(item){
            $(item).up('div.'+elementClass).addClassName('eepohs-payment-method-head');
        });
    },
    /**
     * Adds blocks with support information
     */
    addSupportInformation: function(elementIds){
        var template = $('supportInfoTemplate');
        $A(elementIds).each(
            function(item){
                $(item).appendChild(template.clone(true).removeClassName('no-display'));
            }
            );

    },
    /**
     * Sets current module version
     */
    setModuleVersion: function(version){
        this.version = version;
    }
}