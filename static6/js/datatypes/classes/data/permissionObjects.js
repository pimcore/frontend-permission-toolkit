
pimcore.registerNS("pimcore.object.classes.data.permissionObjects");
pimcore.object.classes.data.permissionObjects = Class.create(pimcore.object.classes.data.objects, {

    type: "permissionObjects",
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
        this.type = "permissionObjects";

        this.initData(initData);

        // overwrite default settings
        this.availableSettingsFields = ["name","title","tooltip","mandatory","noteditable","invisible",
            "visibleGridView","visibleSearch","style"];

        this.treeNode = treeNode;
    },

    getTypeName: function () {
        return t("permissionObjects");
    },

    getGroup: function () {
        return "permissionToolkit";
    },

    getIconClass: function () {
        return "pimcore_icon_permission_objects";
    }
});
