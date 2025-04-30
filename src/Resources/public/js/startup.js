/**
* This source file is available under the terms of the
* Pimcore Open Core License (POCL)
* Full copyright and license information is available in
* LICENSE.md which is distributed with this source code.
*
*  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.com)
*  @license    Pimcore Open Core License (POCL)
*/


pimcore.registerNS("pimcore.plugin.frontendpermissiontoolkit");

pimcore.plugin.frontendpermissiontoolkit = Class.create({
    getClassName: function() {
        return "pimcore.plugin.frontendpermissiontoolkit";
    },

    initialize: function() {
        document.addEventListener(pimcore.events.pimcoreReady, this.onPimcoreReady.bind(this));
    },

    onPimcoreReady: function (e) {
        // alert("Example Ready!");
    }
});

var frontendpermissiontoolkitPlugin = new pimcore.plugin.frontendpermissiontoolkit();

