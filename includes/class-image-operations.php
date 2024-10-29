<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Class for images operations
 */
class prisakaru_atd_Image_Operations
{

    private $images;


    /**
     * Construct of class
     */
    public function __construct()
    {
        $this->images = get_posts(
            [
                'post_type'      => 'attachment',
                'posts_per_page' => -1,
                'post_status'    => 'any',
            ]
        );

    }//end __construct()


    /**
     * Get images without alternative texts from wp
     */
    public function get_list_without_alts()
    {
        $empty_images = [];
        foreach ($this->images as $image) {
            $alt_text = get_post_meta($image->ID, '_wp_attachment_image_alt', true);
            if (is_string($alt_text) && empty(trim($alt_text))) {
                $this->extract_image_info($image, $empty_images);
            }
        }

        return $empty_images;

    }//end get_list_without_alts()


    /**
     * Get all images from wp
     */
    public function get_all_images_list()
    {
        $all_images = [];
        foreach ($this->images as $image) {
            $this->extract_image_info($image, $all_images);
        }

        return $all_images;

    }//end get_all_images_list()


    /**
     * Get info of image
     *
     * @param object $image       WP attachment object of image.
     * @param array  $image_array Array to which we will put extracted data.
     */
    private function extract_image_info($image, &$image_array)
    {
        $image_info        = [];
        $image_info['id']  = $image->ID;
        $image_info['url'] = wp_get_attachment_url($image->ID);
        $image_info['alt'] = get_post_meta($image->ID, '_wp_attachment_image_alt', true);
        $image_array[]     = $image_info;

    }//end extract_image_info()


}//end class
