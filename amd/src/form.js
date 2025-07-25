define(['jquery', 'core/ajax', 'core/templates', 'core/notification'],
function($, Ajax, Templates, Notification) {
    return {
        init: function(courseid) {
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

                            // Validate form data
                            if (!userid || !position || !jerseynumber) {
                                Notification.alert('', 'Please fill in all required fields');
                                return;
                            }

                            // Show loading indicator
                            $('#status-message')
                                .removeClass('alert-danger alert-success')
                                .addClass('alert-info')
                                .text('Saving data...')
                                .show();

                            // Save data via AJAX
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
        }
    };
});
