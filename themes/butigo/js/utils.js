(function(window) {

    'use strict';

    window.isValidDate = function(year, month, day) {
        var d = new Date(year + '-' + month + '-' + day);

        if (Object.prototype.toString.call(d) !== "[object Date]") {
            return false;
        }

        if (d.getMonth() + 1 != month) {
            return false;
        }

        return ! isNaN(d.getTime());
    };

    window.validateCitizenId = function(value) {
        var t1 = (function() { return typeof value === 'string'; })();
        var t2 = (function() { return value.length === 11; })();
        var t3 = (function() {
            var c1 = value.substr(0, value.length - 1),
                c2 = parseInt(value.charAt(10), 10),
                sum = 0;

            for (var i = 0; i < c1.length; i++) {
                sum += parseInt(c1.charAt(i), 10);
            }

            return sum % 10 === c2;
        })();
        var t4 = (function() {
            var c1 = (
                    parseInt(value[0], 10) +
                    parseInt(value[2], 10) +
                    parseInt(value[4], 10) +
                    parseInt(value[6], 10) +
                    parseInt(value[8], 10)),
                c2 = (
                    parseInt(value[1], 10) +
                    parseInt(value[3], 10) +
                    parseInt(value[5], 10) +
                    parseInt(value[7], 10)) * 9,
                c3 = ((c1 * 7) + c2) % 10 === parseInt(value.charAt(9), 10),
                c4 = ((c1 * 8) % 10 === parseInt(value.charAt(10), 10));

            return c3 && c4;
        })();

        return t1 && t2 && t3 && t4;
    };

})(this);
