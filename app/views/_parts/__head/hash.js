String.prototype.hash = function () {
    return this.split('').reduce(function (a, b) {
        a = ((a << 5) - a) + b.charCodeAt(0);
        return a & a
    }, 0)
};
