// define(['jquery', 'core/ajax', 'core/templates'], function($, Ajax, Templates) {
//     return {
//         init: function(courseid) {
//             Ajax.call([{
//                 methodname: 'local_soccerteam_get_form_data',
//                 args: {courseid: courseid},
//                 done: function(data) {
//                     Templates.render('local_soccerteam/form', data).then(function(html) {
//                         $('#formcontainer').html(html);
//                     });
//                 }
//             }]);
//         }
//     };
// });


define(['jquery', 'core/ajax', 'core/templates'], function($, Ajax, Templates) {
    return {
        init: function(courseid) {
            console.log('Initializing soccer team form with course ID:', courseid);

            Ajax.call([{
                methodname: 'local_soccerteam_get_form_data',
                args: {courseid: courseid},
                done: function(data) {
                    console.log('AJAX call successful. Data received:', data);

                    Templates.render('local_soccerteam/form', data).then(function(html) {
                        console.log('Template rendered successfully.');
                        $('#formcontainer').html(html);
                    }).catch(function(renderError) {
                        console.error('Error rendering template:', renderError);
                    });
                },
                fail: function(error) {
                    console.error('AJAX call failed:', error);
                }
            }]);
        }
    };
});
