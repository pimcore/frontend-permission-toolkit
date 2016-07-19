
pimcore.registerNS("pimcore.object.tags.permissionResource");
pimcore.object.tags.permissionResource = Class.create(pimcore.object.tags.select, {

    type: "permissionResource",

    initialize: function (data, fieldConfig) {
        this.data = data;
        this.fieldConfig = fieldConfig;
    },

    getLayoutEdit: function () {

        // generate store
        var store = [];
        var validValues = [];

        for (var i = 0; i < this.fieldConfig.options.length; i++) {
            store.push([this.fieldConfig.options[i].value, ts(this.fieldConfig.options[i].key)]);
            validValues.push(this.fieldConfig.options[i].value);
        }

        var options = {
            name: this.fieldConfig.name,
            triggerAction: "all",
            editable: false,
            fieldLabel: this.fieldConfig.title,
            store: store,
            itemCls: "object_field",
            width: 500
        };

        if (typeof this.data == "string" || typeof this.data == "number") {
            if (in_array(this.data, validValues)) {
                options.value = this.data;
            } else {
                options.value = "";
            }
        } else {
            options.value = "";
        }

        this.component = new Ext.form.ComboBox(options);

        return this.component;
    }
});