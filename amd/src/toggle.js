define(['jquery', 'core/config', 'core/str', 'core/modal_factory', 'core/modal_events'], function($) {
    /* eslint no-console: ["error", { allow: ["log", "warn", "error"] }] */
    return {
        init: function() {

// ---------------------------------------------------------------------------------------------------------------------
            var toggleType = function() {
                $(".ai2-header").on('click', function() {
                    console.log('toggle!');
                    $(this).parent().find('.content').toggle();
                    if ($(this).parent().find('.content').is(":visible")) {
                        console.log('is visible');
                        $(this).find('.icon').removeClass('fa-caret-right').addClass('fa-caret-down');
                    } else {
                        console.log('is not visible');
                        $(this).find('.icon').removeClass('fa-caret-down').addClass('fa-caret-right');
                    }
                });
            };
// ---------------------------------------------------------------------------------------------------------------------
            var initFunctions = function() {
                // Initialise all required functions from above
                toggleType();
            };

// _____________________________________________________________________________________________________________________
            $(document).ready(function() {
                initFunctions();

                $('.ai2-header').css('cursor', 'pointer');
                $('.ai2-header').click();
            });
        }
    };
});
