/*
 * File: app/model/billing.js'MyApp.model.'+item.xtype
 *
 * This file was generated by Sencha Architect version 2.1.0.
 * http://www.sencha.com/products/architect/
 *
 * This file requires use of the Sencha Touch 2.0.x library, under independent license.
 * License of Sencha Architect does not include license for Sencha Touch 2.0.x. For more
 * details see http://www.sencha.com/license or contact license@sencha.com.
 *
 * This file will be auto-generated each and everytime you save your project.
 *
 * Do NOT hand edit this file.
 */

Ext.define('MyApp.model.payment', {
    extend: 'Ext.data.Model',

    config: {
        fields: [
            {
            	name: 'payment_payment_method',
            	type: 'string'
            },
            {
                name: 'creditcard_type',
                type: 'string'
            },
            {
                name: 'creditcard_number',
                type: 'int'
            },
            {
                name: 'creditcard_expirationdate',
                type: 'string'
            },
            {
                name: 'creditcard_cvv',
                type: 'string'
            }
            
        ],
        validations: [
            {
            	type: 'presence',
            	field: 'payment_payment_method'
            },
            {
            	type: 'presence',
            	field: 'creditcard_number'
            }
        ]
    },

    validate: function() {

        var errors      = Ext.create('Ext.data.Errors'),
            validations = this.getValidations().items,
            validators  = Ext.data.Validations,
            length, validation, field, valid, type, i;

        if (validations) {
            length = validations.length;
            var f = Ext.getCmp('oseRegForm');
            for (i = 0; i < length; i++) {
                validation = validations[i];
                field = validation.field || validation.name;
                type  = validation.type;
                var valid = true;
                if(field == 'payment_payment_method') {
                    valid = validators[type](validation, this.get(field));
                } else if(f.down('#payment_method').getValue() != null && f.down('#payment_method').getRecord().get('id') == 4) {
                    valid = validators[type]( validation, this.get(field) );
                }

                if (!valid) {
                    errors.add(Ext.create('Ext.data.Error', {
                        field  : field,
                        message: validation.message || validators.getMessage(type)
                    }));
                }
            }
        }

        return errors;

    }
});