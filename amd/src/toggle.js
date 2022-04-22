define(['jquery', 'core/config', 'core/str', 'core/modal_factory', 'core/modal_events'], function($) {
    /* eslint no-console: ["error", { allow: ["log", "warn", "error"] }] */
    return {
        init: function() {

// ---------------------------------------------------------------------------------------------------------------------
            var toggleType = function() {
                $(".ai2-header").on('click', function() {
                    $(this).parent().find('.content').toggle();
                    if ($(this).parent().find('.content').is(":visible")) {
                        $(this).find('.icon').removeClass('fa-caret-right').addClass('fa-caret-down');
                    } else {
                        $(this).find('.icon').removeClass('fa-caret-down').addClass('fa-caret-right');
                    }
                    saveToggleState();
                });
            };

// ---------------------------------------------------------------------------------------------------------------------
            var saveToggleState = function() {
                var toggleState = {};
                $('.ai2-header').each(function() {
                    var name = $(this).find('.mname').html();
                    var state = 0;
                    if ($(this).parent().find('.content').is(':visible')) {
                        state = 1;
                    }
                    toggleState[name] = state;
                });

                // Now write the sequence for this course into the user preference
                var courseid = $('#ao-courseid').html();
                $.ajax({
                    url: "../blocks/assessment_information2/ajax/update_toggles.php",
                    type: "POST",
                    data: {'courseid': courseid, 'toggle_state': JSON.stringify(toggleState), 'sesskey': M.cfg.sesskey},
                    success: function(result) {
                        if (result !== '') {
                            console.log('Updated toggle state: ' + result);
                        }
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
            });
        }
    };
});
