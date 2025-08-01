jQuery(document).ready(function($) {
    // Function to initialize color pickers
    function initializeColorPickers() {
        $('.color-field').wpColorPicker();
    }

    // Initialize existing color pickers on load
    initializeColorPickers();

    // Handle adding new color fields
    $('.hoa-galaxy-multi-color-picker').on('click', '.add-color', function() {
        const fieldName = $(this).data('field-name');
        const container = $(this).closest('.hoa-galaxy-multi-color-picker');
        const newIndex = container.find('.hoa-galaxy-color-item').length;
        const newItem = `
            <div class="hoa-galaxy-color-item">
                <input type="text" name="hoa_galaxy_settings[${fieldName}][${newIndex}]" value="#ffffff" class="color-field" />
                <button type="button" class="button remove-color">-</button>
            </div>
        `;
        $(this).before(newItem);
        initializeColorPickers(); // Initialize color picker for the new field
    });

    // Handle removing color fields
    $('.hoa-galaxy-multi-color-picker').on('click', '.remove-color', function() {
        $(this).closest('.hoa-galaxy-color-item').remove();
    });

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