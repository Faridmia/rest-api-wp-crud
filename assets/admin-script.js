// AJAX to create book
const siteNameElement = window.location.href;
jQuery('#create-book').on('click', function() {
    let title = jQuery('#title').val();
    let content = jQuery('#content').val();
    
    jQuery.ajax({
        url: `${siteNameElement}/wp-json/bookstore/v1/books`,
        method: 'POST',
        data: {
            title: title,
            content: content,
            status: 'publish', // Set the post status to 'publish'
        },
        success: function(response) {
            alert('Book created successfully');
            jQuery('#title').val('');
            jQuery('#content').val('');
            fetchBooks();
        },
        error: function(error) {
            alert('Error creating book');
        }
    });
});

// AJAX to update book
jQuery(document).on('click', '.update-book', function() {
    let bookId = jQuery(this).data('id');
    let title = jQuery('#update-title-' + bookId).val();
    let content = jQuery('#update-content-' + bookId).val();
    
    jQuery.ajax({
        url: `${siteNameElement}/wp-json/bookstore/v1/books/` + bookId,
        method: 'PUT',
        data: {
            title: title,
            content: content
        },
        success: function(response) {
            alert('Book updated successfully');
            fetchBooks();
        },
        error: function(error) {
            alert('Error updating book');
        }
    });
});

// Function to delete a book
function deleteBook(bookId) {
    if (confirm('Are you sure you want to delete this book?')) {
        jQuery.ajax({
            url: `${siteNameElement}/wp-json/bookstore/v1/books/${bookId}`,
            method: 'DELETE',
            success: function(response) {
                alert('Book deleted successfully');
                fetchBooks(); // Refresh the book list after deletion
            },
            error: function(error) {
                alert('Error deleting book');
            }
        });
    }
}


// Function to fetch books
function fetchBooks() {
    jQuery.ajax({
        url: `${siteNameElement}/wp-json/bookstore/v1/books`,
        method: 'GET',
        success: function(response) {
            var booksHtml = '';
            response.forEach(function(book) {
                booksHtml += `<li class="list-group-item-data">
                                  <input type="text" id="update-title-${book.ID}" value="${book.post_title}">
                                  <textarea id="update-content-${book.ID}">${book.post_content}</textarea>
                                  <button class="update-book btn btn-primary" data-id="${book.ID}">Update</button>
                                  <button class="delete-book btn btn-danger" data-id="${book.ID}">Delete</button>
                              </li>`;
            });
            jQuery('#books').html(booksHtml);
        },
        error: function(error) {
            alert('Error fetching books');
        }
    });
}

// Attach event listener to delete buttons (using event delegation)
jQuery(document).on('click', '.delete-book', function() {
    let bookId = jQuery(this).data('id');
    deleteBook(bookId);
});


fetchBooks();
