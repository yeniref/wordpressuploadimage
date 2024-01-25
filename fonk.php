function uploadImageToWordPressWithMetadata($image_url, $alt_text = null, $caption = null, $description = null)
    {
        if (!function_exists('wp_crop_image')) {
            include(ABSPATH . 'wp-admin/includes/image.php');
        }
        // Resmi al
        $image_contents = file_get_contents($image_url);

        $image_extension = explode('/', getimagesizefromstring($image_contents)['mime'])[1];

        $image_name = sanitize_title($alt_text) . "." . $image_extension;

        $image_type = wp_check_filetype($image_name, null);

        $new_file_name = wp_unique_filename(wp_upload_dir()['path'], $image_name);

        $upload = wp_upload_bits($new_file_name, null, $image_contents);

        if (empty($upload['error'])) {
            $attachment = array(
                'guid'           => $upload['url'],
                'post_mime_type' => $image_type['type'],
                'post_title'     => $image_name,
                'post_content'   => $description,
                'post_excerpt'   => $alt_text,
                'post_status'    => 'inherit',
            );

            $attach_id = wp_insert_attachment($attachment, $upload['file']);

            if (!is_wp_error($attach_id)) {
                if ($alt_text !== null) {
                    update_post_meta($attach_id, '_wp_attachment_image_alt', $alt_text);
                }
                if ($caption !== null) {
                    update_post_meta($attach_id, '_wp_attachment_image_caption', $caption);
                }
                $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
                wp_update_attachment_metadata($attach_id, $attach_data);
                return $attach_id;
            }
        }

        return false;
    }
