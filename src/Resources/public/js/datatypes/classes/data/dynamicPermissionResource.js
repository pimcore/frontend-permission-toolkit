/**
 * Pimcore
 *
 * This source file is available under two different licenses:
 * - GNU General Public License version 3 (GPLv3)
 * - Pimcore Enterprise License (PEL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.org)
 * @license    http://www.pimcore.org/license     GPLv3 and PEL
 */


pimcore.registerNS("pimcore.object.classes.data.dynamicPermissionResource");
pimcore.object.classes.data.dynamicPermissionResource = Class.create(pimcore.object.classes.data.data, {

    type: "dynamicPermissionResource",
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
        this.type = "dynamicPermissionResource";

        this.initData(initData);

        this.treeNode = treeNode;
    },

    getTypeName: function () {
        return t("dynamicPermissionResource");
    },

    getGroup: function () {
        return "permissionToolkit";
    },

    getIconClass: function () {
        return "pimcore_icon_permission_dynamic_resource";
    },

    getLayout: function ($super) {
        $super();
        this.specificPanel.removeAll();
        this.specificPanel.add({
            xtype: "textfield",
            fieldLabel: t("permissionResourceProvider"),
            width: 600,
            name: "dataProvider",
            value: this.datax.dataProvider
        });

        return this.layout;
    }
});
