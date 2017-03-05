function toSpinalCase(str) {
    str = str || this;
    return str.replace(/(?!^)([A-Z])/g, ' $1')
              .replace(/[_\s]+(?=[a-zA-Z])/g, '-').toLowerCase();
}

function toCamelCase(str) {
    str = str || this;
    return str.replace(/^([A-Z])|\s(\w)/g, function (match, p1, p2) {
        if (p2) return p2.toUpperCase();
        return p1.toLowerCase();
    });
}

String.prototype.toSpinalCase = toSpinalCase;
String.prototype.toCamelCase  = toCamelCase;
