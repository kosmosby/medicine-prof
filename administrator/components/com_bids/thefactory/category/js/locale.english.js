/*
---

name: CB.Locale.English.US

description: Default translations of text strings used in JX for US english (en-US)

license: MIT-style license.

requires:
 - More/Locale

provides: [CB.Locale.English.US]

...
 */
Locale.define('en-US', 'CB', {
	
	'dialog': {
		label: {
            edit: 'Edit Category Dialog',
            'new': 'New Category Dialog'
		},
        form: {
            label: {
                description: 'Description',
                category: 'Name'
            }
        },
        button: {
            save: 'Save',
            cancel: 'Cancel'
        }
            
	},
    tree: {
        tool: {
            edit: 'Edit this Category',
            'delete': 'Delete this Category'  ,
            'add': 'Add a Subcategory'
        }
    },
    remove: {
        question: {
            part1: 'Are you sure you want to delete ',
            part2: ' and all of its children?'
        },
        dialog: {
            label: 'Delete Category'
        }
    }
	
});