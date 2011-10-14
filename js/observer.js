var o = new Observer;

function Observer() {
    this.fns = [];
}
Observer.prototype = {
    listen : function(fn) {
        this.fns.push(fn);
    },
    remove : function(fn) {
        this.fns = this.fns.filter(
            function(el) {
                if ( el !== fn ) {
                    return el;
                }
            }
        );
    },
    notify : function(o, thisObj) {
        var scope = thisObj || window;
		for (var key in this.fns) {
		    var el = this.fns[key];
            el.call(scope, o);
		}
    }
};
