/**
 * Add Featured Image Column to Posts Table with simplified AJAX approach
 * Add this code to your theme's functions.php or in a custom plugin
 */

// Add the Featured Image column to the posts table (posts only)
function add_featured_image_column($columns) {
    global $pagenow, $typenow;
    if (!is_admin() || $pagenow !== 'edit.php' || $typenow !== 'post') {
        return $columns;
    }
    
    $new_columns = array();
    
    // Add columns before the title
    foreach($columns as $key => $value) {
        if ($key == 'title') {
            $new_columns['featured_image'] = 'Featured Image';
        }
        $new_columns[$key] = $value;
    }
    
    return $new_columns;
}
add_filter('manage_posts_columns', 'add_featured_image_column');

// Display the featured image in the column with simple edit/delete
function display_featured_image_column($column_name, $post_id) {
    if ($column_name == 'featured_image') {
        echo '<div class="featured-image-column-container" data-post-id="' . $post_id . '">';
        
        if (has_post_thumbnail($post_id)) {
            $thumbnail_id = get_post_thumbnail_id($post_id);
            $thumbnail_url = get_the_post_thumbnail_url($post_id, 'thumbnail');
            
            echo '<div class="featured-image-preview">';
            echo '<img src="' . esc_url($thumbnail_url) . '" width="60" height="60" />';
            echo '<div class="featured-image-actions">';
            echo '<a href="#" class="edit-featured-image dashicons dashicons-edit" data-post-id="' . $post_id . '" title="Edit Featured Image"></a>';
            echo '<a href="#" class="delete-featured-image dashicons dashicons-trash" data-post-id="' . $post_id . '" title="Remove Featured Image"></a>';
            echo '</div>';
            echo '</div>';
        } else {
            echo '<div class="no-featured-img">';
            echo '<span>No Image</span>';
            echo '<a href="#" class="add-featured-image dashicons dashicons-plus-alt" data-post-id="' . $post_id . '" title="Add Featured Image"></a>';
            echo '</div>';
        }
        
        echo '</div>';
    }
}
add_action('manage_posts_custom_column', 'display_featured_image_column', 10, 2);

// Add CSS to style the column
function featured_image_column_css() {
    global $pagenow, $typenow;
    if (!is_admin() || $pagenow !== 'edit.php' || $typenow !== 'post') {
        return;
    }
    
    echo '<style>
        .column-featured_image { 
            width: 80px; 
            text-align: center;
        }
        .featured-image-column-container {
            position: relative;
            width: 60px;
            margin: 0 auto;
        }
        .featured-image-preview {
            position: relative;
            display: inline-block;
        }
        .featured-image-preview img {
            border-radius: 4px;
            border: 1px solid #ddd;
            display: block;
            margin: 0 auto;
        }
        .featured-image-actions {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            display: flex;
            justify-content: center;
            align-items: center;
            opacity: 0;
            transition: opacity 0.2s;
            border-radius: 4px;
        }
        .featured-image-preview:hover .featured-image-actions {
            opacity: 1;
        }
        .featured-image-actions a {
            color: #fff;
            font-size: 16px;
            margin: 0 5px;
            text-decoration: none;
        }
        .featured-image-actions a:hover {
            color: #00b9eb;
        }
        .no-featured-img {
            color: #999;
            font-size: 12px;
            height: 60px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            border: 1px dashed #ddd;
            border-radius: 4px;
            cursor: pointer;
        }
        .no-featured-img:hover {
            background-color: #f7f7f7;
            border-color: #999;
        }
        .no-featured-img .dashicons {
            font-size: 20px;
            margin-top: 5px;
            color: #0073aa;
        }
        
        /* Modal styles */
        #featured-image-modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 99999;
            background: rgba(0,0,0,0.7);
        }
        #featured-image-modal-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: white;
            padding: 20px;
            border-radius: 5px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        #featured-image-modal-close {
            position: absolute;
            right: 10px;
            top: 10px;
            font-size: 20px;
            color: #666;
            cursor: pointer;
        }
        #featured-image-form {
            margin-top: 15px;
        }
        #featured-image-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(100px, 1fr));
            gap: 10px;
            margin-top: 15px;
            max-height: 300px;
            overflow-y: auto;
            padding: 10px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        .image-gallery-item {
            cursor: pointer;
            border: 2px solid transparent;
            border-radius: 4px;
            overflow: hidden;
        }
        .image-gallery-item.selected {
            border-color: #0073aa;
        }
        .image-gallery-item img {
            width: 100%;
            height: auto;
            display: block;
        }
        .tab-navigation {
            display: flex;
            margin-bottom: 15px;
            border-bottom: 1px solid #ddd;
        }
        .tab-button {
            padding: 10px 15px;
            background: #f9f9f9;
            border: 1px solid #ddd;
            border-bottom: none;
            border-radius: 4px 4px 0 0;
            margin-right: 5px;
            cursor: pointer;
        }
        .tab-button.active {
            background: white;
            border-bottom: 1px solid white;
            margin-bottom: -1px;
        }
        .tab-content {
            display: none;
        }
        .tab-content.active {
            display: block;
        }
        #image-upload-progress {
            display: none;
            margin-top: 10px;
            padding: 10px;
            background: #f0f8ff;
            border: 1px solid #cce5ff;
            border-radius: 4px;
        }
        #selected-image-preview {
            margin-top: 15px;
            text-align: center;
            display: none;
        }
        #selected-image-preview img {
            max-width: 100%;
            max-height: 200px;
            border: 1px solid #ddd;
            border-radius: 4px;
            padding: 5px;
        }
    </style>';
}
add_action('admin_head', 'featured_image_column_css');

// Add our custom modal to the footer
function add_featured_image_modal() {
    global $pagenow, $typenow;
    if (!is_admin() || $pagenow !== 'edit.php' || $typenow !== 'post') {
        return;
    }
    
    ?>
    <!-- The Modal -->
    <div id="featured-image-modal">
        <div id="featured-image-modal-content">
            <span id="featured-image-modal-close">&times;</span>
            <h2 id="featured-image-modal-title">Set Featured Image</h2>
            
            <div class="tab-navigation">
                <div class="tab-button active" data-tab="upload-tab">Upload New</div>
                <div class="tab-button" data-tab="media-tab">Media Library</div>
            </div>
            
            <div id="upload-tab" class="tab-content active">
                <form id="featured-image-form" method="post" enctype="multipart/form-data">
                    <input type="hidden" id="featured-image-post-id" value="">
                    <p><input type="file" id="featured-image-file" accept="image/*"></p>
                    <div id="image-upload-progress">Uploading image...</div>
                </form>
            </div>
            
            <div id="media-tab" class="tab-content">
                <div>
                    <input type="text" id="media-search" placeholder="Search images..." style="width: 100%; margin-bottom: 10px;">
                </div>
                <div id="featured-image-gallery">
                    <div class="loading-images">Loading media library...</div>
                </div>
            </div>
            
            <div id="selected-image-preview"></div>
            
            <div style="margin-top: 15px; text-align: right;">
                <button id="featured-image-cancel" class="button">Cancel</button>
                <button id="featured-image-submit" class="button button-primary" disabled>Set as Featured Image</button>
            </div>
        </div>
    </div>
    <?php
}
add_action('admin_footer', 'add_featured_image_modal');

// Add JavaScript for our custom modal handling
function featured_image_column_js() {
    global $pagenow, $typenow;
    if (!is_admin() || $pagenow !== 'edit.php' || $typenow !== 'post') {
        return;
    }
    
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        var selectedAttachmentId = 0;
        var currentPostId = 0;
        var mediaItems = [];
        var isMediaLoaded = false;
        
        // Open modal when clicking add or edit
        $(document).on('click', '.add-featured-image, .edit-featured-image, .no-featured-img', function(e) {
            e.preventDefault();
            
            // Get the post ID from the data attribute
            if ($(this).data('post-id')) {
                currentPostId = $(this).data('post-id');
            } else {
                // If the element doesn't have the post-id attribute, try to get it from the parent
                currentPostId = $(this).closest('.featured-image-column-container').data('post-id');
            }
            
            console.log('Opening modal for post ID:', currentPostId);
            
            if (!currentPostId) {
                console.error('Failed to get post ID');
                alert('Error: Could not determine post ID. Please try again.');
                return;
            }
            
            $('#featured-image-post-id').val(currentPostId);
            $('#featured-image-modal-title').text('Set Featured Image for ' + $('#post-' + currentPostId + ' .title a.row-title').text());
            openModal();
            
            // Reset form
            $('#featured-image-file').val('');
            $('#featured-image-submit').prop('disabled', true);
            $('#selected-image-preview').hide().empty();
            selectedAttachmentId = 0;
            
            // Load media if tab is active and not loaded
            if ($('#media-tab').hasClass('active') && !isMediaLoaded) {
                loadMediaLibrary();
            }
        });
        
        // Close modal when clicking the X or Cancel
        $('#featured-image-modal-close, #featured-image-cancel').on('click', function() {
            closeModal();
        });
        
        // Close modal when clicking outside of it
        $(window).on('click', function(e) {
            if ($(e.target).is('#featured-image-modal')) {
                closeModal();
            }
        });
        
        // Switch tabs
        $('.tab-button').on('click', function() {
            var tabId = $(this).data('tab');
            
            // Toggle active class on tabs
            $('.tab-button').removeClass('active');
            $(this).addClass('active');
            
            // Toggle content visibility
            $('.tab-content').removeClass('active');
            $('#' + tabId).addClass('active');
            
            // Load media if needed
            if (tabId === 'media-tab' && !isMediaLoaded) {
                loadMediaLibrary();
            }
            
            // Reset selected state
            selectedAttachmentId = 0;
            $('#featured-image-submit').prop('disabled', true);
            $('#selected-image-preview').hide().empty();
        });
        
        // File input change handler
        $('#featured-image-file').on('change', function() {
            var file = this.files[0];
            if (file) {
                // Preview the image
                var reader = new FileReader();
                reader.onload = function(e) {
                    $('#selected-image-preview').html('<img src="' + e.target.result + '" alt="Selected image">').show();
                    $('#featured-image-submit').prop('disabled', false);
                    selectedAttachmentId = 0; // We're uploading a new file
                }
                reader.readAsDataURL(file);
            } else {
                $('#selected-image-preview').hide().empty();
                $('#featured-image-submit').prop('disabled', true);
            }
        });
        
        // Media search
        $('#media-search').on('input', function() {
            var searchTerm = $(this).val().toLowerCase();
            
            if (searchTerm === '') {
                // Show all items
                $('.image-gallery-item').show();
            } else {
                // Filter items
                $('.image-gallery-item').each(function() {
                    var itemName = $(this).data('name').toLowerCase();
                    if (itemName.indexOf(searchTerm) > -1) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });
        
        // Handle delete featured image
        $(document).on('click', '.delete-featured-image', function(e) {
            e.preventDefault();
            var post_id = $(this).data('post-id');
            
            if (confirm('Are you sure you want to remove this featured image?')) {
                deleteFeaturedImage(post_id);
            }
        });
        
        // Set featured image button handler
        $('#featured-image-submit').on('click', function() {
            // Get the post ID from the hidden input to make sure we have it
            currentPostId = $('#featured-image-post-id').val();
            
            console.log('Set featured image clicked:');
            console.log('Post ID from hidden field:', currentPostId);
            
            if (!currentPostId) {
                console.error('Post ID is missing');
                alert('Error: Post ID is missing. Please try again.');
                return;
            }
            
            if ($('#upload-tab').hasClass('active')) {
                // Upload new image
                if ($('#featured-image-file')[0].files.length > 0) {
                    uploadFeaturedImage();
                }
            } else {
                // Use selected image from media library
                if (selectedAttachmentId > 0) {
                    console.log('Setting existing image as featured image:');
                    console.log('Post ID:', currentPostId);
                    console.log('Attachment ID:', selectedAttachmentId);
                    setExistingFeaturedImage(selectedAttachmentId);
                }
            }
        });
        
        // Load media library images
        function loadMediaLibrary() {
            $('#featured-image-gallery').html('<div class="loading-images">Loading media library...</div>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'get_media_library_images',
                    _wpnonce: '<?php echo wp_create_nonce("get-media-library-images"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        mediaItems = response.data;
                        displayMediaItems(mediaItems);
                        isMediaLoaded = true;
                    } else {
                        $('#featured-image-gallery').html('<div class="error">Error loading media library</div>');
                    }
                },
                error: function() {
                    $('#featured-image-gallery').html('<div class="error">Error connecting to server</div>');
                }
            });
        }
        
        // Display media items in gallery
        function displayMediaItems(items) {
            var galleryHtml = '';
            
            if (items.length === 0) {
                galleryHtml = '<div class="no-images">No images found in your media library</div>';
            } else {
                for (var i = 0; i < items.length; i++) {
                    galleryHtml += '<div class="image-gallery-item" data-id="' + items[i].id + '" data-name="' + items[i].title + '">' +
                                   '<img src="' + items[i].thumbnail + '" alt="' + items[i].title + '">' +
                                   '</div>';
                }
            }
            
            $('#featured-image-gallery').html(galleryHtml);
            
            // Add click handler for gallery items
            $('.image-gallery-item').on('click', function() {
                $('.image-gallery-item').removeClass('selected');
                $(this).addClass('selected');
                
                var itemId = $(this).data('id');
                selectedAttachmentId = itemId;
                
                // Find the selected item
                var selectedItem = null;
                for (var i = 0; i < mediaItems.length; i++) {
                    if (mediaItems[i].id == itemId) {
                        selectedItem = mediaItems[i];
                        break;
                    }
                }
                
                if (selectedItem) {
                    $('#selected-image-preview').html('<img src="' + selectedItem.medium + '" alt="' + selectedItem.title + '">').show();
                    $('#featured-image-submit').prop('disabled', false);
                }
            });
        }
        
        // Upload new featured image
        function uploadFeaturedImage() {
            var file = $('#featured-image-file')[0].files[0];
            var formData = new FormData();
            
            formData.append('action', 'upload_featured_image');
            formData.append('post_id', currentPostId);
            formData.append('_wpnonce', '<?php echo wp_create_nonce("upload-featured-image"); ?>');
            formData.append('featured_image', file);
            
            console.log('Uploading new featured image:');
            console.log('Post ID:', currentPostId);
            console.log('File:', file.name, 'Size:', file.size, 'Type:', file.type);
            
            $('#image-upload-progress').show();
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    $('#image-upload-progress').hide();
                    console.log('Upload response:', response);
                    
                    if (response.success) {
                        console.log('Upload successful! Reloading page...');
                        closeModal();
                        location.reload();
                    } else {
                        console.error('Upload error:', response.data ? response.data.message : 'Unknown error');
                        alert('Error: ' + (response.data ? response.data.message : 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    $('#image-upload-progress').hide();
                    console.error('AJAX upload error:');
                    console.error('Status:', status);
                    console.error('Error:', error);
                    console.error('Response text:', xhr.responseText);
                    alert('Error connecting to the server: ' + status + ' - ' + error);
                }
            });
        }
        
        // Set existing image as featured
        function setExistingFeaturedImage(attachmentId) {
            console.log('AJAX request to set existing featured image:');
            console.log('Post ID:', currentPostId);
            console.log('Attachment ID:', attachmentId);
            console.log('Nonce:', '<?php echo wp_create_nonce("set-existing-featured-image"); ?>');
            
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'set_existing_featured_image',
                    post_id: currentPostId,
                    attachment_id: attachmentId,
                    _wpnonce: '<?php echo wp_create_nonce("set-existing-featured-image"); ?>'
                },
                beforeSend: function(xhr) {
                    console.log('Before send - request headers:', xhr);
                },
                success: function(response) {
                    console.log('AJAX response:', response);
                    
                    if (response.success) {
                        console.log('Success! Reloading page...');
                        closeModal();
                        location.reload();
                    } else {
                        console.error('Error in AJAX response:', response.data ? response.data.message : 'Unknown error');
                        alert('Error: ' + (response.data ? response.data.message : 'Unknown error'));
                    }
                },
                error: function(xhr, status, error) {
                    console.error('AJAX error:');
                    console.error('Status:', status);
                    console.error('Error:', error);
                    console.error('Response text:', xhr.responseText);
                    alert('Error connecting to the server: ' + status + ' - ' + error);
                }
            });
        }
        
        // Delete featured image
        function deleteFeaturedImage(postId) {
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: 'delete_featured_image',
                    post_id: postId,
                    _wpnonce: '<?php echo wp_create_nonce("delete-featured-image"); ?>'
                },
                success: function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('Error connecting to the server. Please try again.');
                }
            });
        }
        
        // Helper functions
        function openModal() {
            $('#featured-image-modal').show();
        }
        
        function closeModal() {
            $('#featured-image-modal').hide();
        }
    });
    </script>
    <?php
}
add_action('admin_footer', 'featured_image_column_js');

// Get media library images
function get_media_library_images() {
    check_ajax_referer('get-media-library-images');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => 'Permission denied'));
    }
    
    $args = array(
        'post_type'      => 'attachment',
        'post_mime_type' => 'image',
        'post_status'    => 'inherit',
        'posts_per_page' => 50,
        'orderby'        => 'date',
        'order'          => 'DESC'
    );
    
    $query = new WP_Query($args);
    $images = array();
    
    foreach ($query->posts as $image) {
        $thumbnail = wp_get_attachment_image_src($image->ID, 'thumbnail');
        $medium = wp_get_attachment_image_src($image->ID, 'medium');
        
        $images[] = array(
            'id'        => $image->ID,
            'title'     => $image->post_title,
            'thumbnail' => $thumbnail ? $thumbnail[0] : '',
            'medium'    => $medium ? $medium[0] : ''
        );
    }
    
    wp_send_json_success($images);
}
add_action('wp_ajax_get_media_library_images', 'get_media_library_images');

// Upload and set featured image
function upload_featured_image() {
    // Debug information
    error_log('upload_featured_image called');
    error_log('POST data: ' . print_r($_POST, true));
    error_log('FILES data: ' . print_r($_FILES, true));
    
    check_ajax_referer('upload-featured-image');
    
    if (!current_user_can('edit_posts')) {
        error_log('Permission denied: User cannot edit posts');
        wp_send_json_error(array('message' => 'Permission denied'));
        return;
    }
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    error_log('Post ID: ' . $post_id);
    
    // Validate post
    $post = get_post($post_id);
    if (!$post) {
        error_log('Post not found: ' . $post_id);
        wp_send_json_error(array('message' => 'Invalid post - not found'));
        return;
    }
    
    if ($post->post_type !== 'post') {
        error_log('Invalid post type: ' . $post->post_type);
        wp_send_json_error(array('message' => 'Invalid post type - expected "post", got "' . $post->post_type . '"'));
        return;
    }
    
    // Check if file is uploaded
    if (!isset($_FILES['featured_image']) || $_FILES['featured_image']['error'] !== UPLOAD_ERR_OK) {
        error_log('File upload error: ' . (isset($_FILES['featured_image']) ? $_FILES['featured_image']['error'] : 'No file uploaded'));
        wp_send_json_error(array('message' => 'No image uploaded or upload error (code: ' . 
            (isset($_FILES['featured_image']) ? $_FILES['featured_image']['error'] : 'no file') . ')'));
        return;
    }
    
    // Check PHP file upload errors
    if ($_FILES['featured_image']['error'] !== UPLOAD_ERR_OK) {
        $upload_error_strings = array(
            UPLOAD_ERR_INI_SIZE   => 'The uploaded file exceeds the upload_max_filesize directive in php.ini.',
            UPLOAD_ERR_FORM_SIZE  => 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.',
            UPLOAD_ERR_PARTIAL    => 'The uploaded file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE    => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing a temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'A PHP extension stopped the file upload.'
        );
        
        $error_code = $_FILES['featured_image']['error'];
        $error_message = isset($upload_error_strings[$error_code]) ? $upload_error_strings[$error_code] : 'Unknown upload error';
        
        error_log('File upload error: ' . $error_message . ' (code ' . $error_code . ')');
        wp_send_json_error(array('message' => $error_message));
        return;
    }
    
    // Check file type
    $file_type = wp_check_filetype(basename($_FILES['featured_image']['name']));
    error_log('File type check: ' . print_r($file_type, true));
    
    if (!$file_type['type'] || strpos($file_type['type'], 'image/') !== 0) {
        error_log('Invalid file type: ' . $file_type['type']);
        wp_send_json_error(array('message' => 'The uploaded file is not a valid image.'));
        return;
    }
    
    // Upload and set as featured image
    error_log('Loading WordPress media handling libraries');
    require_once(ABSPATH . 'wp-admin/includes/image.php');
    require_once(ABSPATH . 'wp-admin/includes/file.php');
    require_once(ABSPATH . 'wp-admin/includes/media.php');
    
    error_log('Attempting to handle upload for post: ' . $post_id);
    $attachment_id = media_handle_upload('featured_image', $post_id);
    
    if (is_wp_error($attachment_id)) {
        error_log('Upload error: ' . $attachment_id->get_error_message());
        wp_send_json_error(array('message' => $attachment_id->get_error_message()));
        return;
    }
    
    error_log('File uploaded successfully. Attachment ID: ' . $attachment_id);
    
    // Set as featured image
    error_log('Attempting to set featured image: post=' . $post_id . ', attachment=' . $attachment_id);
    $result = set_post_thumbnail($post_id, $attachment_id);
    error_log('set_post_thumbnail result: ' . ($result ? 'true' : 'false'));
    
    if ($result) {
        // Double-check
        $new_thumbnail_id = get_post_thumbnail_id($post_id);
        error_log('New thumbnail ID after setting: ' . $new_thumbnail_id);
        
        wp_send_json_success(array('message' => 'Featured image set successfully', 'attachment_id' => $attachment_id));
    } else {
        error_log('Failed to set featured image');
        wp_send_json_error(array('message' => 'Failed to set featured image'));
    }
}
add_action('wp_ajax_upload_featured_image', 'upload_featured_image');

// Set existing image as featured
function set_existing_featured_image() {
    // Debug information
    error_log('set_existing_featured_image called');
    error_log('POST data: ' . print_r($_POST, true));
    
    check_ajax_referer('set-existing-featured-image');
    
    if (!current_user_can('edit_posts')) {
        error_log('Permission denied: User cannot edit posts');
        wp_send_json_error(array('message' => 'Permission denied'));
        return;
    }
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    $attachment_id = isset($_POST['attachment_id']) ? intval($_POST['attachment_id']) : 0;
    
    error_log('Post ID: ' . $post_id);
    error_log('Attachment ID: ' . $attachment_id);
    
    // Validate post
    $post = get_post($post_id);
    if (!$post) {
        error_log('Post not found: ' . $post_id);
        wp_send_json_error(array('message' => 'Invalid post - not found'));
        return;
    }
    
    if ($post->post_type !== 'post') {
        error_log('Invalid post type: ' . $post->post_type);
        wp_send_json_error(array('message' => 'Invalid post type - expected "post", got "' . $post->post_type . '"'));
        return;
    }
    
    // Validate attachment
    $attachment = get_post($attachment_id);
    if (!$attachment) {
        error_log('Attachment not found: ' . $attachment_id);
        wp_send_json_error(array('message' => 'Invalid attachment - not found'));
        return;
    }
    
    if ($attachment->post_type !== 'attachment') {
        error_log('Invalid attachment type: ' . $attachment->post_type);
        wp_send_json_error(array('message' => 'Invalid attachment type'));
        return;
    }
    
    if (strpos($attachment->post_mime_type, 'image/') !== 0) {
        error_log('Invalid mime type: ' . $attachment->post_mime_type);
        wp_send_json_error(array('message' => 'Invalid mime type - not an image'));
        return;
    }
    
    // Set as featured image with extra debugging
    error_log('Attempting to set featured image: post=' . $post_id . ', attachment=' . $attachment_id);
    
    $result = set_post_thumbnail($post_id, $attachment_id);
    error_log('set_post_thumbnail result: ' . ($result ? 'true' : 'false'));
    
    if ($result) {
        // Double-check if it was really set
        $new_thumbnail_id = get_post_thumbnail_id($post_id);
        error_log('New thumbnail ID after setting: ' . $new_thumbnail_id);
        
        if ($new_thumbnail_id == $attachment_id) {
            wp_send_json_success(array('message' => 'Featured image set successfully'));
        } else {
            error_log('Thumbnail ID mismatch: expected=' . $attachment_id . ', actual=' . $new_thumbnail_id);
            wp_send_json_error(array('message' => 'Failed to set featured image - ID mismatch'));
        }
    } else {
        error_log('Failed to set featured image');
        wp_send_json_error(array('message' => 'Failed to set featured image'));
    }
}
add_action('wp_ajax_set_existing_featured_image', 'set_existing_featured_image');

// Delete featured image
function delete_featured_image() {
    check_ajax_referer('delete-featured-image');
    
    if (!current_user_can('edit_posts')) {
        wp_send_json_error(array('message' => 'Permission denied'));
    }
    
    $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
    
    // Validate post
    $post = get_post($post_id);
    if (!$post || $post->post_type !== 'post') {
        wp_send_json_error(array('message' => 'Invalid post'));
    }
    
    // Delete the featured image
    if (delete_post_thumbnail($post_id)) {
        wp_send_json_success();
    } else {
        wp_send_json_error(array('message' => 'Failed to delete featured image'));
    }
}
add_action('wp_ajax_delete_featured_image', 'delete_featured_image');