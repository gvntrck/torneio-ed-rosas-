jQuery(document).ready(function ($) {
    if ($('.er-frontend-form-container').length === 0) return;

    // 1. Mover campos para dentro do formulário do WooCommerce
    var $wooForm = $('form.cart');
    if ($wooForm.length > 0) {
        var $formContainer = $('.er-frontend-form-container');
        // Prepend to form, just before the add to cart button if possible
        var $btn = $wooForm.find('.single_add_to_cart_button');
        if ($btn.length > 0) {
            $formContainer.insertBefore($btn);
            // Change button text to Pagar
            $btn.text('Pagar').html('Pagar');
        } else {
            $wooForm.append($formContainer);
        }
    }

    // 2. Validação para grupos de checkbox (required)
    $('form.cart').on('submit', function (e) {
        var valid = true;
        $('.er-checkbox-group-required').each(function () {
            var $group = $(this).closest('.er-form-group');
            if ($group.find('input[type="checkbox"]:checked').length === 0) {
                valid = false;
                var label = $group.find('.er-field-label').text().replace('*', '').trim();
                alert('Por favor, selecione ao menos uma opção em: ' + label);
            }
        });
        if (!valid) {
            e.preventDefault();
        }
    });

    // 3. Atualizar preview do preço total baseado nas escolhas
    function updatePricePreview() {
        var total = 0;
        $('.er-frontend-form-container').find('select, input[type="radio"]:checked, input[type="checkbox"]:checked').each(function () {
            var price = parseFloat($(this).attr('data-price')) || 0;
            if ($(this).is('select')) {
                price = parseFloat($(this).find('option:selected').attr('data-price')) || 0;
            }
            total += price;
        });

        if (total > 0) {
            $('#er-total-preview').text(total.toFixed(2).replace('.', ','));
            $('#er-total-preview-wrap').slideDown();
        } else {
            $('#er-total-preview-wrap').slideUp();
        }
    }

    $('.er-frontend-form-container').on('change', 'select, input[type="radio"], input[type="checkbox"]', function () {
        updatePricePreview();
    });

    // Inicializa o preço se já houver algo marcado (ex: browser auto-fill)
    updatePricePreview();
});
