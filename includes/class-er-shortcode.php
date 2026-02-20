<?php
if (!defined('ABSPATH')) {
    exit;
}

class ER_Shortcode
{

    public function __construct()
    {
        add_shortcode('ed_rosas_form', array($this, 'render_shortcode'));
        add_action('wp_enqueue_scripts', array($this, 'register_scripts'));
    }

    public function register_scripts()
    {
        wp_register_style('er-frontend-css', ER_TORNEIOS_PLUGIN_URL . 'assets/css/frontend.css', array(), ER_TORNEIOS_VERSION);
        wp_register_script('er-frontend-js', ER_TORNEIOS_PLUGIN_URL . 'assets/js/frontend.js', array('jquery'), ER_TORNEIOS_VERSION, true);
    }

    public function render_shortcode($atts)
    {
        $atts = shortcode_atts(array(
            'id' => 0,
        ), $atts, 'ed_rosas_form');

        $post_id = intval($atts['id']);
        if (!$post_id || get_post_type($post_id) !== 'er_forms') {
            return '<p>Formulário de torneio inválido.</p>';
        }

        $fields_json = get_post_meta($post_id, '_er_form_fields', true);
        if (empty($fields_json)) {
            return '';
        }

        $fields = json_decode($fields_json, true);
        if (empty($fields) || !is_array($fields)) {
            return '';
        }

        wp_enqueue_style('er-frontend-css');
        wp_enqueue_script('er-frontend-js');

        ob_start();
        ?>
                <div id="er-form-wrapper-<?php echo esc_attr($post_id); ?>" class="er-frontend-form-container" data-form-id="<?php echo esc_attr($post_id); ?>">
                    <input type="hidden" name="er_tournament_form_id" value="<?php echo esc_attr($post_id); ?>">
            
                    <?php foreach ($fields as $field):
                        $field_id = esc_attr($field['id']);
                        $field_name = 'er_field_' . $field_id;
                        $required_attr = !empty($field['required']) ? 'required' : '';
                        $required_mark = !empty($field['required']) ? ' <span class="required">*</span>' : '';

                        // Parse options
                        $options_raw = isset($field['options']) ? explode(',', $field['options']) : array();
                        $options = array();
                        foreach ($options_raw as $opt_raw) {
                            $opt_raw = trim($opt_raw);
                            if (empty($opt_raw))
                                continue;
                            $parts = explode('::', $opt_raw);
                            $opt_label = trim($parts[0]);
                            $opt_price = isset($parts[1]) ? floatval(trim($parts[1])) : 0;
                            $options[] = array('label' => $opt_label, 'price' => $opt_price, 'value' => sanitize_title($opt_label));
                        }
                        ?>
                            <div class="er-form-group er-type-<?php echo esc_attr($field['type']); ?>">
                                <label class="er-field-label"><?php echo esc_html($field['label']); ?><?php echo $required_mark; ?></label>
                                <div class="er-field-input">
                                    <?php if (in_array($field['type'], array('text', 'email', 'tel', 'number'))): ?>
                                            <input type="<?php echo esc_attr($field['type']); ?>" name="<?php echo esc_attr($field_name); ?>" <?php echo $required_attr; ?> class="er-input-text">
                        
                                    <?php elseif ($field['type'] === 'select'): ?>
                                            <select name="<?php echo esc_attr($field_name); ?>" <?php echo $required_attr; ?> class="er-input-select">
                                                <option value="">Selecione...</option>
                                                <?php foreach ($options as $opt): ?>
                                                        <option value="<?php echo esc_attr($opt['value']); ?>" data-price="<?php echo esc_attr($opt['price']); ?>"><?php echo esc_html($opt['label']); ?><?php echo $opt['price'] > 0 ? ' (+ R$ ' . number_format($opt['price'], 2, ',', '.') . ')' : ''; ?></option>
                                                <?php endforeach; ?>
                                            </select>
                            
                                    <?php elseif ($field['type'] === 'radio'): ?>
                                            <?php foreach ($options as $index => $opt): ?>
                                                    <label class="er-radio-label">
                                                        <input type="radio" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($opt['value']); ?>" data-price="<?php echo esc_attr($opt['price']); ?>" <?php echo $required_attr; ?>>
                                                        <?php echo esc_html($opt['label']); ?>                    <?php echo $opt['price'] > 0 ? ' (+ R$ ' . number_format($opt['price'], 2, ',', '.') . ')' : ''; ?>
                                                    </label>
                                            <?php endforeach; ?>

                                    <?php elseif ($field['type'] === 'checkbox' || $field['type'] === 'consent'): ?>
                                            <?php
                                            $opt_val = !empty($options) ? $options[0]['value'] : 'sim';
                                            $opt_label = !empty($options) ? $options[0]['label'] : $field['label'];
                                            $opt_price = !empty($options) ? $options[0]['price'] : 0;
                                            ?>
                                            <label class="er-checkbox-label">
                                                <input type="checkbox" name="<?php echo esc_attr($field_name); ?>" value="<?php echo esc_attr($opt_val); ?>" data-price="<?php echo esc_attr($opt_price); ?>" <?php echo $required_attr; ?>>
                                                <?php echo esc_html($opt_label); ?>                <?php echo $opt_price > 0 ? ' (+ R$ ' . number_format($opt_price, 2, ',', '.') . ')' : ''; ?>
                                            </label>

                                    <?php elseif ($field['type'] === 'checkbox_group'): ?>
                                            <?php foreach ($options as $index => $opt): ?>
                                                    <label class="er-checkbox-label">
                                                        <input type="checkbox" name="<?php echo esc_attr($field_name . '[]'); ?>" value="<?php echo esc_attr($opt['value']); ?>" data-price="<?php echo esc_attr($opt['price']); ?>">
                                                        <?php echo esc_html($opt['label']); ?>                    <?php echo $opt['price'] > 0 ? ' (+ R$ ' . number_format($opt['price'], 2, ',', '.') . ')' : ''; ?>
                                                    </label>
                                            <?php endforeach; ?>
                                            <?php if ($required_attr): ?>
                                                    <input type="hidden" class="er-checkbox-group-required" required>
                                            <?php endif; ?>
                                    <?php endif; ?>
                                </div>
                            </div>
                    <?php endforeach; ?>
            
                    <div id="er-total-preview-wrap" style="display:none;">
                        <strong>Acréscimos:</strong> R$ <span id="er-total-preview">0,00</span>
                    </div>
                </div>
                <?php
                return ob_get_clean();
    }
}
