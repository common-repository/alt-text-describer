<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Class which makes requests to api and retrieves info
 */
class prisakaru_atd_Requests_Operations
{


    /**
     * Construct of class
     */
    public function __construct()
    {

    }//end __construct()


    /**
     * Make api call
     *
     * @param string  $url     Endpoint of api.
     * @param array   $data    Data to send to api.
     * @param integer $timeout Timeout of call.
     */
    public function make_api_request($url, $data, $timeout=45)
    {
        $api_url  = 'https://api.prisakaru.lt/'.$url;
        $response = wp_remote_post(
            $api_url,
            [
                'body'    => wp_json_encode($data),
                'headers' => ['Content-Type' => 'application/json'],
                'timeout' => $timeout,
            ]
        );
        return is_wp_error($response) ? [ 'error' => $response->get_error_message() ] : $response;

    }//end make_api_request()


    /**
     * Get alternative text from api by image url
     *
     * @param string $image_url Url of wp attachment.
     * @param string $language  Language to use for alternative text generation.
     */
    public function get_description($image_url, $language)
    {
        $data = [
            'image_url' => $image_url,
            'api_key'   => get_option('prisakaru_atd_api_key'),
            'language'  => $language,
        ];
        return $this->make_api_request('request_description', $data);

    }//end get_description()


    /**
     * Gets alternative texts for images without alternative texts
     *
     * @param string $language Language for alternative texts.
     */
    public function generate_alt_for_images($language='English')
    {
        $this->make_images_request($language, false);

    }//end generate_alt_for_images()


    /**
     * Gets alternative texts for all images
     *
     * @param string $language Language for alternative texts.
     */
    public function generate_alt_for_all_images($language='English')
    {
        $this->make_images_request($language, true);

    }//end generate_alt_for_all_images()


    /**
     * Proccess response from api
     *
     * @param array               $response            Response from api.
     * @param array               $image               Image info.
     * @param Database_Operations $database_operations Class of database operations.
     * @param boolean             $is_ajax             Check if its for ajax call.
     */
    public function proccess_image_response($response, $image, $database_operations, $is_ajax=true)
    {
        if ('error' === $response['status'] && $is_ajax) {
            if ('no_credits' === ( $response['type'] || 'no_key' === $response['type'] || 'wrong_key' === $response['type'] )) {
                wp_send_json($response);
            }

            $alt = $response['content'];
            $database_operations->save_description($image['id'], $image['url'], $alt);
            update_post_meta($image['id'], '_wp_attachment_image_alt', $alt);
        } else if ('success' === $response['status']) {
            update_post_meta($image['id'], '_wp_attachment_image_alt', $response['content']);
            $database_operations->save_description($image['id'], $image['url'], $response['content']);
        }

    }//end proccess_image_response()


    /**
     * Make api call for single image
     *
     * @param string  $image_url Url of wp attachment.
     * @param string  $image_id  Id of wp attachment.
     * @param string  $language  Language to use for alternative text.
     * @param boolean $is_ajax   Check if its for ajax call.
     */
    public function make_single_request($image_url, $image_id, $language, $is_ajax=false)
    {
        $database_operations = new prisakaru_atd_Database_operations();
        $image               = [];
        $image['url']        = $image_url;
        $image['id']         = $image_id;
        $response            = json_decode($this->get_description($image['url'], $language)['body'], true);
        $this->proccess_image_response($response, $image, $database_operations, $is_ajax);

    }//end make_single_request()


    /**
     * Make api call for multiple images
     *
     * @param string  $language   Language to use for alternative text.
     * @param boolean $all_images Check if this should be generator for images without alt texts or for all images.
     */
    public function make_images_request($language, $all_images=true)
    {
        $image_operations    = new prisakaru_atd_Image_operations();
        $database_operations = new prisakaru_atd_Database_operations();
        $images              = $all_images ? $image_operations->get_all_images_list() : $image_operations->get_list_without_alts();
        $total_images        = count($images);
        $processed_images    = 0;
        $image               = reset($images);
        $response            = '';
        if (false !== $image) {
            $response = json_decode($this->get_description($image['url'], $language)['body'], true);
            $this->proccess_image_response($response, $image, $database_operations);
            ++$processed_images;
        }

        wp_send_json(
            [
                'status'    => 'success',
                'total'     => $total_images,
                'processed' => $processed_images,
                'response'  => $response,
            ]
        );

    }//end make_images_request()


    /**
     * Make call to api to check how many credits user have
     *
     * @param string $api_key User's api key.
     */
    public function get_user_credits_by_api_key($api_key)
    {
        $data = [ 'api_key' => $api_key ];
        return json_decode($this->make_api_request('get_user_info', $api_key)['body'])->credits_left;

    }//end get_user_credits_by_api_key()


}//end class
