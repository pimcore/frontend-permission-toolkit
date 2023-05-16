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


pimcore.registerNS("pimcore.object.tags.dynamicPermissionResource");
pimcore.object.tags.dynamicPermissionResource = Class.create(pimcore.object.tags.abstract, {

    type: "dynamicPermissionResource",

    initialize: function (data, fieldConfig) {

        this.data = "";

        if (data) {
            this.data = data;
        }
        this.fieldConfig = fieldConfig;
    },


    createCombo: function(config) {
        // generate store
        var store = [];
        var validValues = [];

        for (var i = 0; i < this.fieldConfig.permissionOptions.length; i++) {
            store.push([this.fieldConfig.permissionOptions[i].value, t(this.fieldConfig.permissionOptions[i].key)]);
            validValues.push(this.fieldConfig.permissionOptions[i].value);
        }

        var name = config['value'];

        var options = {
            name: name, //this.fieldConfig.name + '[' + name + ']',
            triggerAction: "all",
            editable: false,
            fieldLabel: t(config['label']),
            store: store,
            componentCls: this.getWrapperClassNames(),
            width: (this.fieldConfig.labelWidth || 0) + 200
        };

        if (typeof this.data[name] == "string" || typeof this.data[name] == "number") {
            if (in_array(this.data[name], validValues)) {
                options.value = this.data[name];
            } else {
                options.value = "";
            }
        } else {
            options.value = "";
        }

        var comboBox = new Ext.form.ComboBox(options);
        return comboBox;
    },


    getLayoutEdit: function () {

        var permissionItems = [];

        for (var i = 0; i < this.fieldConfig.permissionResources.length; i++) {
            permissionItems.push(this.createCombo(this.fieldConfig.permissionResources[i]));
        }

        this.component = Ext.create("Ext.FormPanel", {
            cls: "object_field",
            style: "margin-bottom: 10px",
            fieldDefaults: {
                labelWidth: this.fieldConfig.labelWidth,
            },
            layout: {
                type: 'vbox',
                align: 'left'
            },
            items: permissionItems
        });

        return this.component;
    },


    getLayoutShow: function () {
        var layout = this.getLayoutEdit();

        if(this.component.items && this.component.items.items) {

            for(var i = 0; i < this.component.items.items.length; i++) {

                var component = this.component.items.items[i];
                component.setReadOnly(true);

            }

        }

        return layout;
    },

    getValue: function () {
        return this.component.getValues();
    },

    getName: function () {
        return this.fieldConfig.name;
    },

    isDirty: function() {
        if (this.component["__pimcore_dirty"]) {
            return true;
        }

        if(this.component.items && this.component.items.items) {

            for(var i = 0; i < this.component.items.items.length; i++) {

                var component = this.component.items.items[i];
                if(component.isDirty()) {

                    // once a field is dirty it should be always dirty (not an ExtJS behavior)
                    this.component["__pimcore_dirty"] = true;
                    return true;
                }

            }

        }

        return false;
    },


    getGridColumnConfig: function (field) {
        return {
            text: t(field.label), width: 150, sortable: false, dataIndex: field.key,
            getEditor: this.getWindowCellEditor.bind(this, field),
            renderer: function (field, value, metaData, record) {
                var key = field.key;
                this.applyPermissionStyle(key, value, metaData, record);


                if (record.data.inheritedFields[key] && record.data.inheritedFields[key].inherited == true) {
                    metaData.tdCls += " grid_value_inherited";
                }

                if (value) {
                    var table = '<table cellpadding="2" cellspacing="0" border="1">';

                    for(var key in value) {

                        var permissionDefinition = field.layout.permissionResources.find(function(key, element) {
                            return element.value == key;
                        }.bind(this, key));

                        table += '<tr>';
                        table += '<td>' + Ext.util.Format.htmlEncode(permissionDefinition.label) + '</td>';
                        table += '<td>' + Ext.util.Format.htmlEncode(value[key]) + '</td>';
                        table += '</tr>';
                    }
                    table += '</table>';
                    return table;
                }
                return "";
            }.bind(this, field)
        };
    },

    getCellEditValue: function () {
        return this.getValue();
    },


});
