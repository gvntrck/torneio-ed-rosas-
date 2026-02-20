jQuery(document).ready(function($) {
    var rawData = $('#er_form_fields').val();
    var fields = [];
    try {
        fields = rawData ? JSON.parse(rawData) : [];
    } catch (e) {
        fields = [];
    }

    var $container = $('#er-fields-container');
    var tmpl = wp.template('er-field');

    function renderFields() {
        $container.empty();
        fields.forEach(function(field, index) {
            var html = tmpl(field);
            $container.append(html);
        });
        updateHiddenJSON();
    }

    function generateId() {
        return Math.random().toString(36).substr(2, 9);
    }

    function updateHiddenJSON() {
        var newFields = [];
        $container.find('.er-field-row').each(function() {
            var $row = $(this);
            newFields.push({
                id: $row.attr('data-id'),
                label: $row.find('.er-input-label').val(),
                type: $row.find('.er-input-type').val(),
                required: $row.find('.er-input-required').is(':checked'),
                options: $row.find('.er-input-options').val()
            });
        });
        fields = newFields;
        $('#er_form_fields').val(JSON.stringify(fields));
    }

    // Handlers
    $('#er-add-field-btn').on('click', function() {
        fields.push({
            id: generateId(),
            label: '',
            type: 'text',
            required: false,
            options: ''
        });
        renderFields();
    });

    $container.on('click', '.er-remove-field-btn', function() {
        if(confirm('Tem certeza?')) {
            $(this).closest('.er-field-row').remove();
            updateHiddenJSON();
        }
    });

    $container.on('click', '.er-move-up-btn', function() {
        var $row = $(this).closest('.er-field-row');
        $row.insertBefore($row.prev('.er-field-row'));
        updateHiddenJSON();
    });

    $container.on('click', '.er-move-down-btn', function() {
        var $row = $(this).closest('.er-field-row');
        $row.insertAfter($row.next('.er-field-row'));
        updateHiddenJSON();
    });

    $container.on('change input', 'input, select', function() {
        var $row = $(this).closest('.er-field-row');
        var type = $row.find('.er-input-type').val();
        
        if (['select', 'radio', 'checkbox', 'checkbox_group', 'consent'].includes(type)) {
            $row.find('.er-options-col').removeClass('hidden');
        } else {
            $row.find('.er-options-col').addClass('hidden');
            $row.find('.er-input-options').val('');
        }
        updateHiddenJSON();
    });

    renderFields();
});
