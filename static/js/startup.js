pimcore.registerNS("pimcore.plugin.frontendpermissiontoolkit");

pimcore.plugin.frontendpermissiontoolkit = Class.create(pimcore.plugin.admin, {
    getClassName: function() {
        return "pimcore.plugin.frontendpermissiontoolkit";
    },

    initialize: function() {
        pimcore.plugin.broker.registerPlugin(this);
    },
 
    pimcoreReady: function (params,broker){
        // alert("Example Ready!");
    }
});

var frontendpermissiontoolkitPlugin = new pimcore.plugin.frontendpermissiontoolkit();

