<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
/**
 * Class needed to save and retrieve data of images alt texts from and to wp database
 */
class prisakaru_atd_Database_Operations
{

    private $table_name;


    /**
     * Construct of class
     */
    public function __construct()
    {
        global $wpdb;
        $this->table_name = $wpdb->prefix.'describer_descriptions';

    }//end __construct()


    /**
     * Generates table for alt text
     */
    public function create_descriptions_table()
    {
        global $wpdb;
        if ($wpdb->get_var($wpdb->prepare('SHOW TABLES LIKE %s', $this->table_name)) !== $this->table_name) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql             = "CREATE TABLE $this->table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                image_id bigint(20) UNSIGNED NOT NULL,
                image_url varchar(255) NOT NULL,
                alt_text text NOT NULL,
                PRIMARY KEY  (id),
                UNIQUE KEY image_id (image_id)
            ) $charset_collate;";
            include_once ABSPATH.'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }

    }//end create_descriptions_table()


    /**
     * Inserts alt text row to database
     *
     * @param string $image_id  Id of image.
     * @param string $image_url Url of image attachment.
     * @param string $alt_text  Alternative image text.
     */
    public function save_description($image_id, $image_url, $alt_text)
    {
        global $wpdb;
        $existing_description = $this->get_description($image_id);
        if ($existing_description) {
            $data  = [
                'image_url' => $image_url,
                'alt_text'  => $alt_text,
            ];
            $where = ['image_id' => $image_id];
            $wpdb->update($this->table_name, $data, $where);
        } else {
            $data   = [
                'image_id'  => $image_id,
                'image_url' => $image_url,
                'alt_text'  => $alt_text,
            ];
            $format = [
                '%d',
                '%s',
                '%s',
            ];
            $wpdb->insert($this->table_name, $data, $format);
        }//end if

    }//end save_description()


    /**
     * Get all alt text rows from database
     */
    public function get_descriptions()
    {
        global $wpdb;
        $descriptions = $wpdb->get_results($wpdb->prepare('SELECT * FROM %s', $this->table_name), ARRAY_A);
        return $descriptions;

    }//end get_descriptions()


    /**
     * Get specific alt text row from database by image_id
     *
     * @param string $image_id Id of image to retrieve.
     */
    public function get_description($image_id)
    {
        global $wpdb;
        $description = $wpdb->get_row($wpdb->prepare('SELECT * FROM {$this->table_name} WHERE image_id = %d', $image_id), ARRAY_A);
        return $description;

    }//end get_description()


}//end class
