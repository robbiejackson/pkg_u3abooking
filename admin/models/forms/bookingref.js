jQuery(function() {
    document.formvalidator.setHandler('bookingref',
        function (value) {
            regex=/^\/.+$/;
            return regex.test(value);
        });
});