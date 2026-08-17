jQuery(document).ready(function () {

    function addExtraPostParameters() {

        // Create your scalar dictionary
        var postData = {
            name: 'myName',
            age: 21,
            cat: 'user'
        };

        // Return it to be injected with File upload requests
        return postData;
    }


    jQuery("#mas").droply({
        multi: true,
        logoColor: 'white',
        textColor: 'white',
        labelColor: 'white',
        borderColor: 'white',
        backgroundIcon: 'images/icon-droply.png',

        // IMPORTANTE: sin ()
        injectPostData: addExtraPostParameters
    });


    // Initialize colorPicker
    var box = jQuery('#colorPicker');

    if (box.length > 0) {

        box.tinycolorpicker();

        var picker = box.data("plugin_tinycolorpicker");

        // Only call setColor if the plugin instance was successfully retrieved
        if (picker) {
            picker.setColor("#B50A0A");
        }
    }


    // Open the lateral panel
    jQuery('.cd-btn').on('click', function (event) {

        event.preventDefault();

        jQuery('.cd-panel').addClass('is-visible');

    });


    // Close the lateral panel
    jQuery('.cd-panel').on('click', function (event) {

        if (
            jQuery(event.target).is('.cd-panel') ||
            jQuery(event.target).is('.cd-panel-close')
        ) {

            jQuery('.cd-panel').removeClass('is-visible');

            event.preventDefault();
        }

    }); // <-- ESTE CIERRE FALTABA


    function ApplyOption() {

        var theme = 'default';

        var picker =
            jQuery('#colorPicker').data("plugin_tinycolorpicker");

        var color = picker.colorHex;


        jQuery('.cd-panel').removeClass('is-visible');


        if (jQuery('#radio1-1').is(':checked')) {

            theme = 'default';

        } else if (jQuery('#radio1-2').is(':checked')) {

            theme = 'simplex';

        } else if (jQuery('#radio1-3').is(':checked')) {

            theme = 'super-simplex';
        }


        jQuery("#mas").empty();


        jQuery("#mas").droply({
            multi: true,
            logoColor: 'white',
            textColor: 'white',
            labelColor: 'white',
            borderColor: 'white',
            backgroundIcon: 'images/icon-droply.png',
            theme: theme,
            backgroundColor: color
        });

    }

});