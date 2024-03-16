<!-- custom-page-template.php -->
<?php
/*
Template Name: Custom Page Template
*/
get_header(); // Include header
?>

<!-- Your HTML content -->
<div id="post-container"></div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        fetch('http://localhost/eduor/wp-json/custom/v1/posts')
            .then(
                response => response.json()
            )
            .then(data => {
                const postContainer = document.getElementById('post-container');
                data.forEach(post => {
                    postContainer.innerHTML += `
                    <h2>${post.post_title}</h2>
                    <p>${post.post_content}</p>
                `;
                });
                console.log(data)
            })
            .catch(error => {
                console.error('Error fetching posts:', error);
            });
    });
</script>

<?php get_footer(); // Include footer 
?>