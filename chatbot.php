<?php
/**
 * Plugin Name: Chatbot WooCommerce
 * Description: Chatbot simple pour WooCommerce avec conseils, ressources et aide.
 * Version: 0.84
 * textdomain: chatbot-woocommerce
 * Domain Path: /languages
 * Author: RECHT Dorian
 * Author URI: https://waverisestudios.com
 * Plugin URI: https://github.com/WaveriseStudios/ChatbotHelperForWordpress
 */

// Ajoute un lien "Réglages" dans la page des plugins (à côté du plugin)
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'chatbot_add_settings_link');

function chatbot_add_settings_link($links) {
    $settings_link = '<a href="' . admin_url('options-general.php?page=chatbot_settings_page') . '">Réglages</a>';
    array_unshift($links, $settings_link);
    return $links;
}

// Enqueue JS
add_action('wp_enqueue_scripts', 'chatbot_enqueue_scripts');
function chatbot_enqueue_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('wp-chatbot-script', plugin_dir_url(__FILE__) . 'chatbot.js', ['jquery'], null, true);
    wp_localize_script('wp-chatbot-script', 'chatbotData', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'faq_url' => get_option('chatbot_faq_url', 'site/faq'),
        'support_email' => get_option('chatbot_support_email', 'support@site.com'),
        'bot_name' => get_option('chatbot_bot_name', 'site assistant'),
        'bot_avatar' => get_option('chatbot_bot_avatar', 'https://cdn-icons-png.flaticon.com/512/4712/4712027.png'),
        'largeur' => get_option('chatbot_largeur', '300px'),
        'longeur' => get_option('chatbot_longueur', '420px'),
        'couleur'=> get_option('chatbot_couleur', '#0073aa'),
        'image_header' => get_option('chatbot_header', 'https://cdn-icons-png.flaticon.com/512/4712/4712027.png'),
    ]);
}

add_action('wp_enqueue_scripts', 'mon_chatbot_styles');

function mon_chatbot_styles() {
    wp_add_inline_style('wp-block-library', '
        .chat-option,
        .category-button,
        a,
        .satisfaction {
            background: none;
            border: none;
            color: #0073aa;
            text-decoration: none;
            font-size: 12px;
            padding: 0;
            margin: 4px 0;
            cursor: pointer;
            display: inline-block;
        }

        .chat-option:hover,
        .category-button:hover,
        a,
        .satisfaction:hover {
            color: #005177;
            cursor: pointer;
            text-decoration: none;
        }
    ');
}

// Affiche le bouton et la fenêtre du chatbot
add_action('wp_footer', 'chatbot_add_button_html');
function chatbot_add_button_html() {
    ?>
    <div id="chatbot-button" style="position: fixed; bottom: 20px; right: 20px; z-index: 9999; width: 64px; height: 64px; background: <?php echo esc_attr(get_option('chatbot_couleur', '#0073aa')); ?>; border-radius: 16px; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 6px rgba(0,0,0,0.3); cursor: pointer;">
        <img src="<?php echo esc_url(get_option('chatbot_bot_avatar', 'bot_avatar')); ?>" alt="Chat" style="width: 36px; height: 36px; border-radius: 50%;">
    </div>

    <div id="chatbot-window" style="display:none; position: fixed; bottom: 95px; right: 20px; width: <?php echo esc_attr(get_option('chatbot_largeur', '300px')); ?>; height: <?php echo esc_attr(get_option('chatbot_longueur', '420px')); ?>; background: #fff; border: 1px solid #ccc; border-radius: 5px; box-shadow: 0 4px 10px rgba(0,0,0,0.1); padding: 0; z-index: 99999; font-family: sans-serif;">
        <div id="chatbot-header" style="display: flex; align-items: center; justify-content: space-between; background: <?php echo esc_attr(get_option('chatbot_couleur', '#0073aa')); ?>; color: white; padding: 8px 10px; border-top-left-radius: 5px; border-top-right-radius: 5px;">
            <div style="display: flex; align-items: center;">
                <img id="chatbot-avatar" src="" alt="Bot" style="width: 30px; height: 30px; border-radius: 50%; margin-right: 8px;">
                <strong id="chatbot-name">Assistant</strong>
            </div>
            <button id="chatbot-restart" style="background: transparent; border: none; color: white; cursor: pointer; font-size: 18px;" title="Recommencer"> 🔁</button>
        </div>
        <div class="chatbot-messages" style="padding: 10px; max-height: 330px; overflow-y:auto; display:flex; flex-direction:column;"></div>
        <div id="chatbot-content" style="padding: 0 10px 10px 10px;"></div>
    </div>
    <?php
}

add_action('admin_init', 'chatbot_register_settings');
function chatbot_register_settings() {
    // Enregistrer les options
    register_setting('chatbot_settings_group', 'chatbot_faq_url');
    register_setting('chatbot_settings_group', 'chatbot_support_email');
    register_setting('chatbot_settings_group', 'chatbot_bot_name');
    register_setting('chatbot_settings_group', 'chatbot_bot_avatar');
    register_setting('chatbot_settings_group', 'chatbot_largeur');
    register_setting('chatbot_settings_group', 'chatbot_longueur');
    register_setting('chatbot_settings_group', 'chatbot_couleur');
    register_setting('chatbot_settings_group', 'chatbot_header');

    // Section Général
    add_settings_section(
        'chatbot_section_general',
        'Paramètres généraux',
        'chatbot_section_general_cb',
        'chatbot_settings_page'
    );

    // Section Personnalisation
    add_settings_section(
        'chatbot_section_personnalisation',
        'Personnalisation',
        'chatbot_section_personnalisation_cb',
        'chatbot_settings_page'
    );

    // Champs dans Général
    add_settings_field(
        'chatbot_faq_url',
        'URL FAQ',
        'chatbot_faq_url_cb',
        'chatbot_settings_page',
        'chatbot_section_general'
    );

    add_settings_field(
        'chatbot_support_email',
        'Email support',
        'chatbot_support_email_cb',
        'chatbot_settings_page',
        'chatbot_section_general'
    );

        // Champs dans Personnalisation
    add_settings_field(
        'chatbot_bot_name',
        'Nom du bot',
        'chatbot_bot_name_cb',
        'chatbot_settings_page',
        'chatbot_section_personnalisation'
    );
        // Champs dans Personnalisation
    add_settings_field(
        'chatbot_couleur',
        'Couleur du chatbot',
        'chatbot_couleur_cb',
        'chatbot_settings_page',
        'chatbot_section_personnalisation'
    );

    add_settings_field(
        'chatbot_image_header',
        'Image d\'en-tête du chatbot',
        'chatbot_image_header_cb',
        'chatbot_settings_page',
        'chatbot_section_personnalisation'
    );

    // Champs dans Personnalisation
    add_settings_field(
        'chatbot_bot_avatar',
        'Avatar du bot',
        'chatbot_bot_avatar_cb',
        'chatbot_settings_page',
        'chatbot_section_personnalisation'
    );

    add_settings_field(
        'chatbot_largeur',
        'Largeur du chatbot',
        'chatbot_largeur_cb',
        'chatbot_settings_page',
        'chatbot_section_personnalisation'
    );

    add_settings_field(
        'chatbot_longueur',
        'Longueur du chatbot',
        'chatbot_longueur_cb',
        'chatbot_settings_page',
        'chatbot_section_personnalisation'
    );
}

// Callbacks sections
function chatbot_section_general_cb() {
    echo '<p>Paramètres de base pour votre chatbot.</p>';
}

function chatbot_section_personnalisation_cb() {
    echo '<p>Réglez l\'apparence du chatbot ici.</p>';
}

// Callbacks champs
function chatbot_faq_url_cb() {
    $value = get_option('chatbot_faq_url', '');
    echo '<input type="text" name="chatbot_faq_url" value="' . esc_attr($value) . '" placeholder="URL de la FAQ" />';
}

function chatbot_support_email_cb() {
    $value = get_option('chatbot_support_email', '');
    echo '<input type="email" name="chatbot_support_email" value="' . esc_attr($value) . '" placeholder="Email de support" />';
}

function chatbot_bot_avatar_cb() {
    $value = get_option('chatbot_bot_avatar', '');
    echo '<input type="text" name="chatbot_bot_avatar" value="' . esc_attr($value) . '" placeholder="URL de l\'image" />';
}

function chatbot_couleur_cb() {
    $value = get_option('chatbot_couleur', '#0073aa');
    echo '<input type="color" name="chatbot_couleur" value="' . esc_attr($value) . '" placeholder="Couleur de fond de l\'icône du chatbot" />';
}

function chatbot_image_header_cb() {
    $value = get_option('chatbot_image_header', '');
    echo '<input type="text" name="chatbot_image_header" value="' . esc_attr($value) . '" placeholder="URL de l\'image d\'en-tête" />';
}

function chatbot_bot_name_cb() {
    $value = get_option('chatbot_bot_name', '');
    echo '<input type="text" name="chatbot_bot_name" value="' . esc_attr($value) . '" placeholder="Nom du bot" />';
}

function chatbot_largeur_cb() {
    $value = get_option('chatbot_largeur', '');
    echo '<input type="text" name="chatbot_largeur" value="' . esc_attr($value) . '" />';
}

function chatbot_longueur_cb() {
    $value = get_option('chatbot_longueur', '');
    echo '<input type="text" name="chatbot_longueur" value="' . esc_attr($value) . '" />';
}

// Ajouter la page dans le menu admin
function chatbot_add_admin_menu() {
    add_options_page(
        'Réglages Chatbot',
        'Chatbot',
        'manage_options',
        'chatbot_settings_page',
        'chatbot_settings_page_callback'
    );
}

add_action('admin_menu', 'chatbot_add_admin_menu');
add_action('admin_init', 'chatbot_register_settings');

// Affichage de la page de réglages
function chatbot_settings_page_callback() {
        // Si le bouton de réinitialisation des vues est cliqué
    if (isset($_POST['chatbot_reset_views']) && check_admin_referer('chatbot_reset_views_action')) {
        update_option('chatbot_view_count', 0);
        echo '<div class="updated"><p>Nombre de vues réinitialisé.</p></div>';
    }

    function chatbot_get_total_views() {
        $total_views = 0;

        // Regrouper toutes les catégories d’articles
        $categories = get_categories(['hide_empty' => false]);
        foreach ($categories as $cat) {
            $total_views += (int) get_term_meta($cat->term_id, 'views', true);
        }

        // Regrouper toutes les catégories produits WooCommerce
        $product_cats = get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]);
        if (!is_wp_error($product_cats)) {
            foreach ($product_cats as $pcat) {
                $total_views += (int) get_term_meta($pcat->term_id, 'views', true);
            }
        }

        return $total_views;
    }

    $current_views = chatbot_get_total_views();

    ?>
    <div class="wrap">
        <h1>Réglages du Chatbot</h1>
        <form action="options.php" method="post">
            <?php
            settings_fields('chatbot_settings_group');
            do_settings_sections('chatbot_settings_page');
            submit_button();
            ?>
        </form>
                <hr>
        <h2>Statistiques</h2>
        <p><strong>Nombre de vues actuelles :</strong> <?php echo intval($current_views); ?></p>

        <form method="post" action="">
            <?php wp_nonce_field('chatbot_reset_views_action'); ?>
            <input type="submit" name="chatbot_reset_views" class="button button-secondary" value="Réinitialiser les vues" onclick="return confirm('Remettre le compteur de vues à zéro ?');" />
        </form>
    </div>
    <?php
}

// Incrémentation des vues des catégories liées à un article affiché
add_action('wp', 'chatbot_increment_category_views');
function chatbot_increment_category_views() {
    if (is_single()) {
        global $post;
        $post_id = $post->ID;
        $cookie_name = 'chatbot_viewed_post_' . $post_id;

        // Évite de compter plusieurs fois la même visite dans 1h
        if (!isset($_COOKIE[$cookie_name])) {
            // Catégories blog
            $categories = get_the_category($post_id);
            if ($categories) {
                foreach ($categories as $cat) {
                    $views = (int) get_term_meta($cat->term_id, 'views', true);
                    update_term_meta($cat->term_id, 'views', $views + 1);
                }
            }
            // Catégories produit WooCommerce
            if (taxonomy_exists('product_cat')) {
                $product_cats = wp_get_post_terms($post_id, 'product_cat');
                if ($product_cats && !is_wp_error($product_cats)) {
                    foreach ($product_cats as $pcat) {
                        $views = (int) get_term_meta($pcat->term_id, 'views', true);
                        update_term_meta($pcat->term_id, 'views', $views + 1);
                    }
                }
            }
        }
    }
}


add_action('wp_ajax_get_blog_categories', 'chatbot_get_blog_categories');
add_action('wp_ajax_nopriv_get_blog_categories', 'chatbot_get_blog_categories');
function chatbot_get_blog_categories() {
    $terms = get_terms([
        'taxonomy' => 'category',
        'hide_empty' => true,
        'meta_key' => 'views',
        'orderby' => 'meta_value_num',
        'order' => 'DESC',
        'number' => 5,
    ]);

    $data = [];
    foreach ($terms as $term) {
        $views = get_term_meta($term->term_id, 'views', true);
        $views = ($views === '') ? 0 : (int) $views;
        $data[] = [
            'id' => $term->term_id,
            'name' => $term->name,
            'views' => $views,
        ];
    }

    wp_send_json(array_slice($data, 0, 5));
}

function chatbot_get_product_categories() {
    $terms = get_terms([
        'taxonomy' => 'product_cat', // <-- ici product_cat et non category
        'hide_empty' => true,
        'orderby' => 'meta_value_num',
        'meta_key' => 'views',
        'order' => 'DESC',
        'number' => 5, // limite aussi la requête côté base
    ]);
    $data = [];

    if (!empty($terms) && !is_wp_error($terms)) {
        foreach ($terms as $term) {
            $views = (int) get_term_meta($term->term_id, 'views', true);
            $data[] = [
                'id' => $term->term_id,
                'name' => $term->name,
                'views' => $views
            ];
        }

        wp_send_json($data); // on envoie tout car limité à 5 dans get_terms
    } else {
        wp_send_json([]);
    }
}


// AJAX - Articles d’un blog
add_action('wp_ajax_get_posts_by_category', 'chatbot_get_posts_by_category');
add_action('wp_ajax_nopriv_get_posts_by_category', 'chatbot_get_posts_by_category');
function chatbot_get_posts_by_category() {
    $id = intval($_POST['category_id']);
    $posts = get_posts(['category' => $id, 'numberposts' => 10]);
    $data = [];
    foreach ($posts as $post) {
        $data[] = ['title' => $post->post_title, 'link' => get_permalink($post)];
    }
    wp_send_json($data);
}

// AJAX - Produits WooCommerce par catégorie
add_action('wp_ajax_get_products_by_category', 'chatbot_get_products_by_category');
add_action('wp_ajax_nopriv_get_products_by_category', 'chatbot_get_products_by_category');
function chatbot_get_products_by_category() {
    $id = intval($_POST['category_id']);
    $products = wc_get_products(['category' => [get_term($id)->slug], 'limit' => 10]);
    $data = [];
    foreach ($products as $product) {
        $data[] = ['title' => $product->get_name(), 'link' => get_permalink($product->get_id())];
    }
    wp_send_json($data);
}


function chatbot_initialize_views_for_all_terms() {
    $all_terms = array_merge(
        get_categories(['hide_empty' => false]),
        get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false])
    );

    foreach ($all_terms as $term) {
        if (get_term_meta($term->term_id, 'views', true) === '') {
            update_term_meta($term->term_id, 'views', 0);
        }
    }
}
