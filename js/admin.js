jQuery(document).ready(function($) {
    // Initialize color picker
    $('.color-field').wpColorPicker();

    // Handle admin theme selection
    $('select[name="hoa_galaxy_settings[admin_theme]"]').on('change', function() {
        const selectedTheme = $(this).val();
        const body = $('body');

        // Remove existing theme classes
        body.removeClass(function(index, className) {
            return (className.match(/(^|\s)hoa-galaxy-admin-theme-\S+/g) || []).join(' ');
        });

        // Add new theme class
        body.addClass('hoa-galaxy-admin-theme-' + selectedTheme);
    }).trigger('change'); // Trigger on load to apply initial theme
});