<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
    /**
     * WP alt texts table generator with wp default functionality
     */
class prisakaru_atd_Image_List_Table extends WP_List_Table
{

    private $images_list;


    /**
     * Construct of class
     */
    public function __construct()
    {
        $args              = [
            'post_type'      => 'attachment',
            'post_mime_type' => 'image',
            'post_status'    => 'inherit',
            'posts_per_page' => -1,
        ];
        $images            = get_posts($args);
        $this->images_list = array_map(
            function ($post) {
                return [
                    'image_id'  => $post->ID,
                    'image_url' => wp_get_attachment_url($post->ID),
                    'alt_text'  => get_post_meta($post->ID, '_wp_attachment_image_alt', true),
                ];
            },
            $images
        );

        parent::__construct(
            [
                'singular' => 'Image',
                'plural'   => 'Images',
                'ajax'     => false,
            ]
        );

    }//end __construct()


    /**
     * Retrieve columns
     */
    public function get_columns()
    {
        return [
            'image_id'  => 'Image ID',
            'image_url' => 'Image URL',
            'alt_text'  => 'Alt Text',
        ];

    }//end get_columns()


    /**
     * Default columns
     *
     * @param array  $item        Array of images info.
     * @param string $column_name Name of column.
     */
    protected function column_default($item, $column_name)
    {
        return isset($item[$column_name]) ? $item[$column_name] : '';

    }//end column_default()


    /**
     * Columns checkboxes
     *
     * @param array $item Array of images info.
     */
    protected function column_cb($item)
    {
        return sprintf(
            '<input type="checkbox" name="element[]" value="%s" />',
            $item['id']
        );

    }//end column_cb()


    /**
     * Prepares items for wp table
     */
    public function prepare_items()
    {
        $per_page              = 10;
        $current_page          = $this->get_pagenum();
        $total_items           = count($this->images_list);
        $data                  = array_slice($this->images_list, ( ( $current_page - 1 ) * $per_page ), $per_page);
        $columns               = $this->get_columns();
        $hidden                = [];
        $sortable              = [];
        $primary               = 'name';
        $this->items           = $data;
        $this->_column_headers = [
            $columns,
            $hidden,
            $sortable,
            $primary,
        ];
        $this->set_pagination_args(
            [
                'total_items' => $total_items,
                'per_page'    => $per_page,
            ]
        );

    }//end prepare_items()


}//end class
