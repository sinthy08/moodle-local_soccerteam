define(['jquery', 'core/ajax', 'core/templates', 'core/notification'],
function($, Ajax, Templates, Notification) {
    return {
        init: function(courseid) {
            // Valid positions list
            const validPositions = ['Goalkeeper', 'Defender', 'Midfielder', 'Forward'];

            // Load form data and render template
            Ajax.call([{
                methodname: 'local_soccerteam_get_form_data',
                args: {courseid: courseid},
                done: function(data) {
                    // Add courseid to the data
                    data.courseid = courseid;

                    // Render the form
                    Templates.render('local_soccerteam/form', data).then(function(html) {
                        $('#formcontainer').html(html);

                        // Handle form submission
                        $('#soccerteam-form').on('submit', function(e) {
                            e.preventDefault();

                            var userid = $('#userid').val();
                            var position = $('#position').val();
                            var jerseynumber = $('#jerseynumber').val();

                            // Basic validation
                            if (!userid || !position || !jerseynumber) {
                                Notification.alert('', 'Please fill in all required fields');
                                return;
                            }

                            // Validate position
                            if (!validPositions.includes(position)) {
                                $('#status-message')
                                    .removeClass('alert-success alert-info')
                                    .addClass('alert-danger')
                                    .text('Error: Invalid position selected')
                                    .show();
                                return;
                            }

                            // Show loading indicator
                            $('#status-message')
                                .removeClass('alert-danger alert-success')
                                .addClass('alert-info')
                                .text('Saving data...')
                                .show();

                            // Check for duplicate jersey number
                            Ajax.call([{
                                methodname: 'local_soccerteam_check_jersey_number',
                                args: {
                                    courseid: parseInt(courseid),
                                    userid: parseInt(userid),
                                    jerseynumber: parseInt(jerseynumber)
                                },
                                done: function(response) {
                                    if (response.duplicate) {
                                        // Show error for duplicate jersey number
                                        $('#status-message')
                                            .removeClass('alert-success alert-info')
                                            .addClass('alert-danger')
                                            .text(response.message)
                                            .show();
                                    } else {
                                        // Save data via AJAX if jersey number is not duplicate
                                        savePlayerData(courseid, userid, position, jerseynumber);
                                    }
                                },
                                fail: function(error) {
                                    $('#status-message')
                                        .removeClass('alert-success alert-info')
                                        .addClass('alert-danger')
                                        .text('Error checking jersey number: ' + error.message)
                                        .show();
                                    Notification.exception(error);
                                }
                            }]);
                        });

                        // Handle cancel button
                        $('#cancel-btn').on('click', function() {
                            $('#soccerteam-form')[0].reset();
                            $('.position-description').text('');
                            $('#status-message').hide();
                        });
                    }).fail(function(error) {
                        Notification.exception(error);
                    });
                },
                fail: function(error) {
                    Notification.exception(error);
                }
            }]);

            /**
             * Save player data function
             * @param {number} courseid - The course ID
             * @param {number} userid - The user ID
             * @param {string} position - The player position
             * @param {number} jerseynumber - The jersey number
             */
            function savePlayerData(courseid, userid, position, jerseynumber) {
                Ajax.call([{
                    methodname: 'local_soccerteam_save_player_data',
                    args: {
                        courseid: parseInt(courseid),
                        userid: parseInt(userid),
                        position: position,
                        jerseynumber: parseInt(jerseynumber)
                    },
                    done: function(response) {
                        // Show success message
                        $('#status-message')
                            .removeClass('alert-danger alert-info')
                            .addClass('alert-success')
                            .text(response.message)
                            .show();

                        // Reset form after 2 seconds
                        setTimeout(function() {
                            $('#soccerteam-form')[0].reset();
                            $('.position-description').text('');
                            $('#status-message').hide();
                        }, 2000);
                    },
                    fail: function(error) {
                        $('#status-message')
                            .removeClass('alert-success alert-info')
                            .addClass('alert-danger')
                            .text('Error saving data: ' + error.message)
                            .show();
                        Notification.exception(error);
                    }
                }]);
            }
        }
    };
});
