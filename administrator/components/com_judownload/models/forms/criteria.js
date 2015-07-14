window.addEvent('domready', function () {
    document.formvalidator.setHandler('positiveInteger',
        function (value) {
            regex = /^[1-9][0-9]*$/;
            if (!regex.test(value)) {
                alert(Joomla.JText._('COM_JUDOWNLOAD_WEIGHT_MUST_BE_GREATER_THAN_ZERO', 'Weight must be greater than zero!'));
            }
            return regex.test(value);
        });
});
