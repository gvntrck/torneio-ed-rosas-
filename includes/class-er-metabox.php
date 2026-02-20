<?php
if (!defined('ABSPATH')) {
    exit;
}

class ER_Metabox
{

    public function __construct()
    {
        add_action('add_meta_boxes', array($this, 'add_boxes'));
        add_action('save_post_er_forms', array($this, 'save_data'), 10, 2);
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
    }

    public function add_boxes()
    {
        add_meta_box(
            'er_forms_builder',
            __('Construtor do Formulário', 'torneio-ed-rosas'),
            array($this, 'render_builder'),
            'er_forms',
            'normal',
            'high'
        );
    }

    public function enqueue_admin_scripts($hook)
    {
        global $post;

        if ('post.php' === $hook || 'post-new.php' === $hook) {
            if ('er_forms' === $post->post_type) {
                wp_enqueue_style('er-admin-css', ER_TORNEIOS_PLUGIN_URL . 'assets/css/admin.css', array(), ER_TORNEIOS_VERSION);
                wp_enqueue_script('er-admin-js', ER_TORNEIOS_PLUGIN_URL . 'assets/js/admin.js', array('jquery'), ER_TORNEIOS_VERSION, true);
            }
        }
    }

    public function render_builder($post)
    {
        wp_nonce_field('er_save_form_data', 'er_form_nonce');

        $fields_json = get_post_meta($post->ID, '_er_form_fields', true);

        // Fallback to default fields if it's a new post or empty
        if (empty($fields_json)) {
            $default_fields = array(
                array('id' => uniqid(), 'label' => 'Nome completo', 'type' => 'text', 'required' => true, 'options' => ''),
                array('id' => uniqid(), 'label' => 'Data de nascimento', 'type' => 'text', 'required' => true, 'options' => ''),
                array('id' => uniqid(), 'label' => 'E-mail', 'type' => 'email', 'required' => true, 'options' => ''),
                array('id' => uniqid(), 'label' => 'Celular', 'type' => 'tel', 'required' => true, 'options' => ''),
                array('id' => uniqid(), 'label' => 'Clube/ Cidade:', 'type' => 'text', 'required' => true, 'options' => ''),
                array('id' => uniqid(), 'label' => 'Titulação:', 'type' => 'select', 'required' => false, 'options' => 'Outra, GM, WGM, IM, WIM, FM, WFM, CM, WCM, NM, WNM, WCNM, CNM'),
                array('id' => uniqid(), 'label' => 'ID CBX:', 'type' => 'text', 'required' => true, 'options' => ''),
                array('id' => uniqid(), 'label' => 'ID FIDE:', 'type' => 'text', 'required' => true, 'options' => ''),
                array('id' => uniqid(), 'label' => 'Deficiente (PCD)?', 'type' => 'radio', 'required' => true, 'options' => 'Sim, Não'),
                array('id' => uniqid(), 'label' => 'PCD especifique:', 'type' => 'text', 'required' => false, 'options' => ''),
                array('id' => uniqid(), 'label' => 'Quantidade de canecas', 'type' => 'number', 'required' => false, 'options' => ''),
                array('id' => uniqid(), 'label' => 'Meia', 'type' => 'checkbox', 'required' => false, 'options' => 'Possuo 60 anos ou mais – aplicar meia inscrição'),
                array('id' => uniqid(), 'label' => 'Opção meia (60+)', 'type' => 'checkbox_group', 'required' => true, 'options' => 'OPEN, BLITZ, RÁPIDO, DUPLAS, SOLUCIONISMO'),
                array('id' => uniqid(), 'label' => 'Opção', 'type' => 'checkbox_group', 'required' => true, 'options' => 'OPEN, BLITZ, RÁPIDO, DUPLAS, SOLUCIONISMO'),
                array('id' => uniqid(), 'label' => 'Nome da Equipe/Dupla', 'type' => 'text', 'required' => true, 'options' => ''),
                array('id' => uniqid(), 'label' => 'Nome (Jogador mesa 1)', 'type' => 'text', 'required' => true, 'options' => ''),
                array('id' => uniqid(), 'label' => 'Data de nascimento (Jogador 1)', 'type' => 'text', 'required' => true, 'options' => ''),
                array('id' => uniqid(), 'label' => 'ID CBX (Jogador 1)', 'type' => 'text', 'required' => true, 'options' => ''),
                array('id' => uniqid(), 'label' => 'ID FIDE (Jogador 1)', 'type' => 'text', 'required' => true, 'options' => ''),
                array('id' => uniqid(), 'label' => 'Nome (Jogador mesa 2)', 'type' => 'text', 'required' => true, 'options' => ''),
                array('id' => uniqid(), 'label' => 'Data de nascimento (Jogador 2)', 'type' => 'text', 'required' => true, 'options' => ''),
                array('id' => uniqid(), 'label' => 'ID CBX (Jogador 2)', 'type' => 'text', 'required' => true, 'options' => ''),
                array('id' => uniqid(), 'label' => 'ID FIDE (Jogador 2)', 'type' => 'text', 'required' => true, 'options' => ''),
                array('id' => uniqid(), 'label' => 'Política de reembolso', 'type' => 'consent', 'required' => true, 'options' => 'Aceito o prazo de 7 dias'),
                array('id' => uniqid(), 'label' => 'Política de Direito de Uso de Imagem', 'type' => 'consent', 'required' => true, 'options' => 'Aceito a Política de Direito de Uso de Imagem'),
                array('id' => uniqid(), 'label' => 'Termo de Consentimento LGPD', 'type' => 'consent', 'required' => true, 'options' => 'Aceito o Termo de Consentimento LGPD'),
            );
            $fields_json = wp_json_encode($default_fields);
        }

        ?>
                <div class="er-builder-wrapper">
                    <p class="description">
                        Use este construtor para editar os campos do formulário para inscrições do torneio.<br>
                        Para campos que alteram o preço do produto (como opções de categoria Extra/Meia), use o formato "Opção::Preco". Exemplo: <code>BLITZ::50.00, OPEN::150.00</code>. Decimais devem ser com ponto.
                    </p>
            
                    <textarea name="er_form_fields" id="er_form_fields" style="display:none;"><?php echo esc_textarea($fields_json); ?></textarea>

                    <div id="er-fields-container"></div>

                    <button type="button" class="button button-primary" id="er-add-field-btn">
                        + Adicionar Novo Campo
                    </button>
                </div>

                <!-- Template para novo campo -->
                <script type="text/html" id="tmpl-er-field">
                    <div class="er-field-row" data-id="{{data.id}}">
                        <div class="er-field-header">
                            <strong>Campo</strong>
                            <button type="button" class="button button-small er-remove-field-btn">Remover</button>
                            <button type="button" class="button button-small er-move-up-btn">↑</button>
                            <button type="button" class="button button-small er-move-down-btn">↓</button>
                        </div>
                        <div class="er-field-body">
                            <div class="er-col">
                                <label>Rótulo do Campo</label>
                                <input type="text" class="er-input-label" value="{{data.label}}" placeholder="Ex: Nome Completo">
                            </div>
                            <div class="er-col">
                                <label>Tipo</label>
                                <select class="er-input-type">
                                    <option value="text" <# if(data.type=='text') print('selected'); #>>Texto</option>
                                    <option value="email" <# if(data.type=='email') print('selected'); #>>E-mail</option>
                                    <option value="tel" <# if(data.type=='tel') print('selected'); #>>Telefone</option>
                                    <option value="number" <# if(data.type=='number') print('selected'); #>>Número</option>
                                    <option value="select" <# if(data.type=='select') print('selected'); #>>Caixa de Seleção (Dropdown)</option>
                                    <option value="radio" <# if(data.type=='radio') print('selected'); #>>Rádio (Única Escolha)</option>
                                    <option value="checkbox" <# if(data.type=='checkbox') print('selected'); #>>Checkbox (Verdadeiro/Falso)</option>
                                    <option value="checkbox_group" <# if(data.type=='checkbox_group') print('selected'); #>>Grupo de Checkbox (Múltipla Escolha)</option>
                                    <option value="consent" <# if(data.type=='consent') print('selected'); #>>Termo de Consentimento</option>
                                </select>
                            </div>
                            <div class="er-col er-col-small">
                                <label>Obrigatório</label>
                                <input type="checkbox" class="er-input-required" value="1" <# if(data.required) print('checked'); #>>
                            </div>
                            <div class="er-col er-options-col <# if(['text','email','tel','number'].includes(data.type)) print('hidden'); #>">
                                <label>Opções (Separe por vírgula. Use Nome::Preco para valorizador)</label>
                                <input type="text" class="er-input-options" value="{{data.options}}" placeholder="Ex: Opção 1, Opção 2::50.00">
                            </div>
                        </div>
                    </div>
                </script>
                <?php
    }

    public function save_data($post_id, $post)
    {
        // Verifica nonces e permissões
        if (!isset($_POST['er_form_nonce']) || !wp_verify_nonce(wp_unslash($_POST['er_form_nonce']), 'er_save_form_data')) {
            return;
        }
        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }
        if ('er_forms' !== $post->post_type) {
            return;
        }

        if (isset($_POST['er_form_fields'])) {
            // Decodifica JSON, higieniza arrays e recodifica JSON.
            $raw_json = wp_unslash($_POST['er_form_fields']);
            $fields = json_decode($raw_json, true);

            if (is_array($fields)) {
                $sanitized_fields = array();
                foreach ($fields as $f) {
                    $sanitized_fields[] = array(
                        'id' => sanitize_text_field($f['id']),
                        'label' => sanitize_text_field($f['label']),
                        'type' => sanitize_text_field($f['type']),
                        'required' => !empty($f['required']) ? true : false,
                        'options' => sanitize_text_field($f['options']),
                    );
                }
                update_post_meta($post_id, '_er_form_fields', wp_json_encode($sanitized_fields));
            }
        }
    }
}
