/**
* This source file is available under the terms of the
* Pimcore Open Core License (POCL)
* Full copyright and license information is available in
* LICENSE.md which is distributed with this source code.
*
*  @copyright  Copyright (c) Pimcore GmbH (http://www.pimcore.com)
*  @license    Pimcore Open Core License (POCL)
*/


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
            store.push([this.fieldConfig.options[i].value, t(this.fieldConfig.options[i].key)]);
            validValues.push(this.fieldConfig.options[i].value);
        }

        var options = {
            name: this.fieldConfig.name,
            triggerAction: "all",
            editable: false,
            fieldLabel: this.fieldConfig.title,
            store: store,
            componentCls: this.getWrapperClassNames(),
            width: this.fieldConfig.labelWidth + 200
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