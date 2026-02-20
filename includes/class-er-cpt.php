<?php
if (!defined('ABSPATH')) {
    exit;
}

class ER_CPT
{
    public function __construct()
    {
        add_action('init', array($this, 'register_cpt'));
        add_filter('manage_er_forms_posts_columns', array($this, 'set_custom_edit_er_forms_columns'));
        add_action('manage_er_forms_posts_custom_column', array($this, 'custom_er_forms_column'), 10, 2);
    }

    public function register_cpt()
    {
        $labels = array(
            'name' => _x('Formulários Torneio', 'Post Type General Name', 'torneio-ed-rosas'),
            'singular_name' => _x('Formulário', 'Post Type Singular Name', 'torneio-ed-rosas'),
            'menu_name' => __('Form Torneios', 'torneio-ed-rosas'),
            'name_admin_bar' => __('Formulário Torneio', 'torneio-ed-rosas'),
            'add_new' => __('Adicionar Novo', 'torneio-ed-rosas'),
            'add_new_item' => __('Adicionar Novo Formulário', 'torneio-ed-rosas'),
            'new_item' => __('Novo Formulário', 'torneio-ed-rosas'),
            'edit_item' => __('Editar Formulário', 'torneio-ed-rosas'),
            'view_item' => __('Ver Formulário', 'torneio-ed-rosas'),
            'all_items' => __('Todos os Formulários', 'torneio-ed-rosas'),
            'search_items' => __('Pesquisar Formulários', 'torneio-ed-rosas'),
            'not_found' => __('Nenhum formulário encontrado.', 'torneio-ed-rosas'),
            'not_found_in_trash' => __('Nenhum formulário encontrado na lixeira.', 'torneio-ed-rosas')
        );

        $args = array(
            'labels' => $labels,
            'public' => false,
            'publicly_queryable' => false,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => false,
            'rewrite' => false,
            'capability_type' => 'post',
            'has_archive' => false,
            'hierarchical' => false,
            'menu_position' => 56,
            'menu_icon' => 'dashicons-list-view',
            'supports' => array('title')
        );

        register_post_type('er_forms', $args);
    }

    public function set_custom_edit_er_forms_columns($columns)
    {
        $new_columns = array();
        foreach ($columns as $key => $title) {
            $new_columns[$key] = $title;
            // Insere a coluna shortcode logo após o título
            if ($key === 'title') {
                $new_columns['shortcode'] = __('Shortcode', 'torneio-ed-rosas');
            }
        }
        return $new_columns;
    }

    public function custom_er_forms_column($column, $post_id)
    {
        if ($column === 'shortcode') {
            echo '<code>[ed_rosas_form id="' . esc_attr($post_id) . '"]</code>';
        }
    }
}
