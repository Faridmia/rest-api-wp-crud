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
define('CRUD_API_VERSION', '1.0.0');

add_action('wp_enqueue_scripts', 'bookstore_admin_scripts');

function bookstore_admin_scripts()
{
    wp_enqueue_script('bookstore-admin-script', CRUD_API_DIR_URL . '/assets/admin-script.js', array('jquery'), '1.0', true);
    wp_enqueue_style('bookstore-admin-style', CRUD_API_DIR_URL . '/assets/admin-style.css', time());
}


add_action('get_footer', 'bookstore_admin_page');

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

// Create the admin page
function bookstore_admin_page()
{
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
<?php
}
