<?php
if (!defined('ABSPATH')) {
    exit;
}

class ER_WooCommerce
{

    public function __construct()
    {
        // 1. Validar e capturar dados do form ao adicionar ao carrinho
        add_filter('woocommerce_add_cart_item_data', array($this, 'add_cart_item_data'), 10, 3);

        // 2. Ajustar preço baseando-se nos acréscimos das opções
        add_action('woocommerce_before_calculate_totals', array($this, 'calculate_totals'), 10, 1);

        // 3. Exibir os dados preenchidos no carrinho/checkout
        add_filter('woocommerce_get_item_data', array($this, 'get_item_data'), 10, 2);

        // 4. Salvar as opções nos metadados do item do pedido
        add_action('woocommerce_checkout_create_order_line_item', array($this, 'create_order_line_item'), 10, 4);
    }

    public function add_cart_item_data($cart_item_data, $product_id, $variation_id)
    {
        if (isset($_POST['er_tournament_form_id'])) {
            $form_id = intval($_POST['er_tournament_form_id']);

            $fields_json = get_post_meta($form_id, '_er_form_fields', true);
            if (empty($fields_json)) {
                return $cart_item_data;
            }

            $fields = json_decode($fields_json, true);
            if (empty($fields) || !is_array($fields)) {
                return $cart_item_data;
            }

            $er_data = array();
            $extra_price = 0;

            foreach ($fields as $field) {
                $field_id = sanitize_text_field($field['id']);
                $field_name = 'er_field_' . $field_id;

                // Parse options array para recuperar preços originais e valores
                $options_raw = isset($field['options']) ? explode(',', $field['options']) : array();
                $field_options = array();
                foreach ($options_raw as $opt_raw) {
                    $opt_raw = trim($opt_raw);
                    if (empty($opt_raw))
                        continue;
                    $parts = explode('::', $opt_raw);
                    $opt_label = trim($parts[0]);
                    $opt_price = isset($parts[1]) ? floatval(trim($parts[1])) : 0;
                    $field_options[sanitize_title($opt_label)] = array(
                        'label' => $opt_label,
                        'price' => $opt_price
                    );
                }

                if (isset($_POST[$field_name])) {
                    $posted_value = $_POST[$field_name];

                    if (is_array($posted_value)) {
                        // Checkbox group múltiplo
                        $values_labels = array();
                        foreach ($posted_value as $val) {
                            $val_sanitized = sanitize_text_field(wp_unslash($val));
                            if (isset($field_options[$val_sanitized])) {
                                $values_labels[] = $field_options[$val_sanitized]['label'];
                                $extra_price += $field_options[$val_sanitized]['price'];
                            } else {
                                $values_labels[] = $val_sanitized;
                            }
                        }
                        $er_data[$field['label']] = implode(', ', $values_labels);

                    } else {
                        // Radio, select, checkbox unica escolha e textos
                        $val_sanitized = sanitize_text_field(wp_unslash($posted_value));

                        if (in_array($field['type'], array('select', 'radio', 'checkbox', 'consent'))) {
                            if (isset($field_options[$val_sanitized])) {
                                $er_data[$field['label']] = $field_options[$val_sanitized]['label'];
                                $extra_price += $field_options[$val_sanitized]['price'];
                            } else {
                                $er_data[$field['label']] = $val_sanitized;
                                // Para checkbox único que não achou option: checa original fallback
                                if ($field['type'] === 'checkbox' && !empty($options_raw)) {
                                    // O label natural costuma ser o próprio nome se for único
                                    if ($val_sanitized === sanitize_title($options_raw[0])) {
                                        // Fallback handled mainly by value if matched
                                    } else {
                                        $er_data[$field['label']] = $field['label'];
                                    }
                                }
                            }
                        } else {
                            $er_data[$field['label']] = $val_sanitized;
                        }
                    }
                }
            }

            if (!empty($er_data)) {
                $cart_item_data['er_tournament_data'] = $er_data;
                $cart_item_data['er_tournament_extra_price'] = $extra_price;
                $cart_item_data['unique_key'] = md5(microtime() . rand());
            }
        }
        return $cart_item_data;
    }

    public function calculate_totals($cart)
    {
        if (is_admin() && !defined('DOING_AJAX')) {
            return;
        }

        if (did_action('woocommerce_before_calculate_totals') >= 2) {
            return;
        }

        foreach ($cart->get_cart() as $cart_item_key => $cart_item) {
            if (isset($cart_item['er_tournament_extra_price']) && $cart_item['er_tournament_extra_price'] > 0) {
                $base_price = (float) $cart_item['data']->get_price();
                $new_price = $base_price + (float) $cart_item['er_tournament_extra_price'];
                $cart_item['data']->set_price($new_price);
            }
        }
    }

    public function get_item_data($item_data, $cart_item)
    {
        if (isset($cart_item['er_tournament_data'])) {
            foreach ($cart_item['er_tournament_data'] as $key => $value) {
                $item_data[] = array(
                    'key' => $key,
                    'value' => $value,
                );
            }
        }
        return $item_data;
    }

    public function create_order_line_item($item, $cart_item_key, $values, $order)
    {
        if (isset($values['er_tournament_data'])) {
            foreach ($values['er_tournament_data'] as $key => $value) {
                // Adiciona como order item meta nativo para fácil visualização
                // em ambas telas de Checkout success, Minha Conta e Admin
                $item->add_meta_data($key, $value);
            }
        }
    }
}
