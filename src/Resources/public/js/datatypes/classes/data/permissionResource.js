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


pimcore.registerNS("pimcore.object.classes.data.permissionResource");
pimcore.object.classes.data.permissionResource = Class.create(pimcore.object.classes.data.data, {

    type: "permissionResource",
    allowIndex: true,

    /**
     * define where this datatype is allowed
     */
    allowIn: {
        object: true,
        objectbrick: true,
        fieldcollection: false,
        localizedfield: false
    },

    initialize: function (treeNode, initData) {
        this.type = "permissionResource";

        this.initData(initData);

        this.treeNode = treeNode;
    },

    getTypeName: function () {
        return t("permissionResource");
    },

    getGroup: function () {
        return "permissionToolkit";
    },

    getIconClass: function () {
        return "pimcore_icon_permission_resource";
    },

    getLayout: function ($super) {
        $super();
        this.specificPanel.removeAll();
        return this.layout;
    }
});
