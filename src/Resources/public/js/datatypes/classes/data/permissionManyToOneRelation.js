/**
* This source file is available under the terms of the
* Pimcore Open Core License (POCL)
* Full copyright and license information is available in
* LICENSE.md which is distributed with this source code.
*
*  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.com)
*  @license    Pimcore Open Core License (POCL)
*/


pimcore.registerNS("pimcore.object.classes.data.permissionManyToOneRelation");
pimcore.object.classes.data.permissionManyToOneRelation = Class.create(pimcore.object.classes.data.manyToOneRelation, {

    type: "permissionManyToOneRelation",
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
        this.type = "permissionManyToOneRelation";

        this.initData(initData);

        pimcore.helpers.sanitizeAllowedTypes(this.datax, "classes");
        pimcore.helpers.sanitizeAllowedTypes(this.datax, "assetTypes");
        pimcore.helpers.sanitizeAllowedTypes(this.datax, "documentTypes");

        // overwrite default settings
        this.availableSettingsFields = ["name","title","tooltip","mandatory","noteditable","invisible",
            "visibleGridView","visibleSearch","style"];

        this.treeNode = treeNode;
    },

    getTypeName: function () {
        return t("permissionManyToOneRelation");
    },

    getGroup: function () {
        return "permissionToolkit";
    },

    getIconClass: function () {
        return "pimcore_icon_permission_manyToOneRelation";
    }
});