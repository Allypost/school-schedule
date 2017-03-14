$.fn.serializeObject = function() {
    var o = {};
    var a = this.serializeArray();

    for (var i = 0; i < a.length; i++) {
        var item = a[ i ];

        if (!o[ item.name ]) {
            o[ item.name ] = item.value;
        } else {
            if (!o[ item.name ].push)
                o[ item.name ] = [ o[ item.name ] ];
            o[ item.name ].push(item.value || null);
        }
    }

    return o;
};
