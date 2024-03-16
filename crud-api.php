<?php

/**
 * Plugin Name: Crud Api
 * Plugin URI: https://github.com/faridmia
 * Description: <a href="#">hello</a> is the most advanced frontend drag & drop page builder addon
 * Version:     1.0.0
 * Author:      Farid Mia
 * Author URI:  https://profiles.wordpress.org/faridmia/
 * License: GPLv2 or later
 * Text Domain: crud-api
 */

if (!defined('ABSPATH')) {
    exit;
}
define('CRUD_API_PLUGIN_FILE', __FILE__);
define('CRUD_API_DIR_PATH', plugin_dir_path(__FILE__));
define('CRUD_API_DIR_URL', plugin_dir_url(__FILE__));
define('PLUGIN_TEMPLATES_DIR', plugin_dir_path(__FILE__) . 'templates/');
define('CRUD_API_VERSION', '1.0.0');

add_action('wp_enqueue_scripts', 'bookstore_admin_scripts');

function bookstore_admin_scripts()
{
    if (is_front_page()) {
        wp_enqueue_script('bookstore-admin-script', CRUD_API_DIR_URL . '/assets/admin-script.js', array('jquery'), '1.0', true);
        wp_enqueue_style('bookstore-admin-style', CRUD_API_DIR_URL . '/assets/admin-style.css', time());
    }
}




// Register custom post type 'book'
function register_book_post_type()
{
    $args = array(
        'public' => true,
        'label'  => 'Books',
        'supports' => array('title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments'),
        // Add more arguments as needed
    );
    register_post_type('book', $args);
}
add_action('init', 'register_book_post_type');

// Create custom REST API endpoints for CRUD operations on 'book' post type
function register_book_routes()
{
    register_rest_route('bookstore/v1', '/books', array(
        'methods'  => 'GET',
        'callback' => 'get_books',
    ));
    register_rest_route('bookstore/v1', '/books/(?P<id>\d+)', array(
        'methods'  => 'GET',
        'callback' => 'get_book',
    ));
    register_rest_route('bookstore/v1', '/books', array(
        'methods'  => 'POST',
        'callback' => 'create_book',
    ));
    register_rest_route('bookstore/v1', '/books/(?P<id>\d+)', array(
        'methods'  => 'PUT',
        'callback' => 'update_book',
    ));
    register_rest_route('bookstore/v1', '/books/(?P<id>\d+)', array(
        'methods'  => 'DELETE',
        'callback' => 'delete_book',
    ));
    register_rest_route('bookstore/v1', '/books/(?P<id>\d+)', array(
        'methods' => 'DELETE',
        'callback' => 'delete_post_endpoint',
        'permission_callback' => function () {
            return is_user_logged_in() && current_user_can('delete_posts');
        }
    ));
}


add_action('rest_api_init', 'register_book_routes');

function delete_post_endpoint($data)
{
    $post_id = (int) $data['id'];

    if (wp_delete_post($post_id, true)) {
        return new WP_REST_Response('Post deleted successfully', 200);
    } else {
        return new WP_REST_Response('Error deleting post', 500);
    }
}


// Callback function to get all books
function get_books()
{
    $args = array(
        'post_type' => 'book',
        'posts_per_page' => -1,
    );
    $books = get_posts($args);

    return $books;
}

// Callback function to get a single book
function get_book($data)
{
    $book_id = $data['id'];
    $book = get_post($book_id);
    return $book;
}

// // Callback function to create a book
function create_book($data)
{
    $book_data = array(
        'post_type' => 'book',
        'post_title' => $data['title'],
        'post_content' => $data['content'],
        'post_status' => $data['status'],
        // Add more fields as needed
    );

    $book_id = wp_insert_post($book_data);
    return $book_id;
}

// // Callback function to update a book
function update_book($data)
{
    $book_id = $data['id'];
    $book_data = array(
        'ID' => $book_id,
        'post_title' => $data['title'],
        'post_content' => $data['content'],
        // Add more fields as needed
    );
    $updated = wp_update_post($book_data);
    return $updated;
}

// // Callback function to delete a book
function delete_book($data)
{
    $book_id = $data['id'];
    $deleted = wp_delete_post($book_id);
    return $deleted;
}

add_action('get_footer', 'bookstore_admin_page');

// Create the admin page
function bookstore_admin_page()
{
    if (is_front_page()) {
?>
        <div class="crud-api-container">
            <div id="book-form" class="book-section">
                <h3>Create New Book</h3>
                <div class="form-group">
                    <label for="title">Post Title:</label><br>
                    <input type="text" id="title" class="form-control" placeholder="Title">
                </div>
                <div class="form-group">
                    <label for="content">Post Content:</label><br>
                    <textarea id="content" class="form-control" placeholder="Content"></textarea>
                </div>
                <button id="create-book" class="btn btn-primary">Create Book</button>
            </div>

            <div id="book-list" class="book-section">
                <h3>Books List</h3>
                <ul id="books" class="list-group"></ul>
            </div>
        </div>
<?php }
}


// default rest api endpoint

add_action('rest_api_init', function () {
    register_rest_route('wp/v2', '/settings', array(
        'methods' => 'GET',
        'callback' => 'get_site_settings',
        'permission_callback' => 'is_user_logged_in_callback', // Allow only authenticated users
    ));
});

// Callback function to retrieve site settings
function get_site_settings($data)
{
    // You can customize this function to retrieve and return site settings as needed
    $settings = array(
        'site_title' => get_bloginfo('name'),
        'site_description' => get_bloginfo('description'),
        // Add more settings as needed
    );

    return rest_ensure_response($settings);
}

// Custom permission callback to check if the user is logged in
function is_user_logged_in_callback()
{
    return true; // Ensure that is_user_logged_in() is called within a WordPress context
}


add_action('rest_api_init', 'register_custom_posts_route');

function register_custom_posts_route()
{
    register_rest_route('custom/v1', '/posts', array(
        'methods'  => 'GET',
        'callback' => 'get_custom_posts',
    ));
}

function get_custom_posts($data)
{
    $args = array(
        'post_type'      => 'post',
        'posts_per_page' => -1,
    );

    $posts = get_posts($args);

    return rest_ensure_response($posts);
}

function custom_page_template($templates)
{
    $templates['custom-post-template.php'] = __("Custom Post Template", "");
    return $templates;
}
add_filter('theme_page_templates', 'custom_page_template');



function save_selected_template($post_id)
{
    // Check if the action is triggered by the user
    if (!isset($_POST['page_template'])) {
        return $post_id;
    }

    // Check if autosave
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return $post_id;
    }

    // Check if current user can edit the post
    if (!current_user_can('edit_post', $post_id)) {
        return $post_id;
    }

    // Sanitize and update page template meta
    $page_template = sanitize_text_field($_POST['page_template']);
    update_post_meta($post_id, '_wp_page_template', $page_template);
}

add_action('save_post', 'save_selected_template');

function apply_custom_page_template($template)
{

    $selected_template = get_post_meta(get_the_ID(), '_wp_page_template', true);

    if ($selected_template === 'custom-post-template.php') {
        // Load your custom template file when the "Pfilter Template" template is selected.
        $template = PLUGIN_TEMPLATES_DIR . 'custom-post-template.php';
    }


    return $template;
}


add_filter('template_include', 'apply_custom_page_template');


add_action('rest_api_init', 'register_custom_taxonomy_endpoint');

function register_custom_taxonomy_endpoint()
{
    register_rest_route('custom/v1', '/taxonomies', array(
        'methods'  => 'GET',
        'callback' => 'get_custom_taxonomies',
    ));
}

function get_custom_taxonomies()
{
    $taxonomies = get_taxonomies(array(), 'objects');

    $formatted_taxonomies = array();
    foreach ($taxonomies as $taxonomy) {
        $formatted_taxonomies[] = array(
            'name' => $taxonomy->name,
            'label' => $taxonomy->label,
            'hierarchical' => $taxonomy->hierarchical,
            // Add more taxonomy properties as needed
        );
    }

    return $formatted_taxonomies;
}
