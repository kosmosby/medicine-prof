Ext.define('MyApp.field.HolderSelect', {
	extend: 'Ext.field.Select',
	alias: 'widget.holderselectfield',
	constructor: function (config) {
        this.callParent(arguments);
    },
    
    onStoreDataChanged: function(store) {
        var initialConfig = this.getInitialConfig(),
            value = this.getValue();

        if (Ext.isDefined(value)) {
            this.updateValue(this.applyValue(value));
        }
        if (this.getValue() === null && !this.getPlaceHolder()) {
            if (initialConfig.hasOwnProperty('value')) {
                this.setValue(initialConfig.value);
            }

            if (this.getValue() === null) {
                if (store.getCount() > 0) {
                    this.setValue(store.getAt(0));
                }
            }
        }
    },

    showPicker: function() {
          var store = this.getStore();
          //check if the store is empty, if it is, return
          if (!store || store.getCount() === 0) {
              return;
          }

          if (this.getReadOnly()) {
              return;
          }

          this.isFocused = true;

          if (this.getUsePicker()) {
              var picker = this.getPhonePicker(),
                  name   = this.getName(),
                  value  = {};

              if (!this.getPlaceHolder()) {
                  value[name] = this.record.get(this.getValueField());
                  picker.setValue(value);
              }
              if (!picker.getParent()) {
                  Ext.Viewport.add(picker);
              }
              picker.show();
          } else {
              var listPanel = this.getTabletPicker(),
                  list = listPanel.down('list'),
                  store = list.getStore(),
                  index = store.find(this.getValueField(), this.getValue(), null, null, null, true),
                  record = store.getAt((index == -1) ? 0 : index);

              if (!listPanel.getParent()) {
                  Ext.Viewport.add(listPanel);
              }

              listPanel.showBy(this.getComponent());
              if (!this.getPlaceHolder()) {
                  list.select(record, null, true);
              }
          }
      }
});