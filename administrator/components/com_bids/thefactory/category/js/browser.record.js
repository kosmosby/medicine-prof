/*
---

name: CB.Record

description: A subclass of Jx.Record customized for use with CB.Adaptor

require: 
 - base
 - jxlib/Jx.Record

provides: [CB.Record]

...
 */

CB.Record = new Class({
    Extends: Jx.Record,
    Family: 'CB.Record',
    options: {
        primaryKey: 'id'
    },
    drawn: false,
    virtuals: {
        drawn: {
            get: function(){
                return this.drawn;
            },
            set: function(data){
                this.drawn = data;
            }
        }
    }
});