/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Commercial License (PCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 *  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 *  @license    http://www.pimcore.org/license     GPLv3 and PCL
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

