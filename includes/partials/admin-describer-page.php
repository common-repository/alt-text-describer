<?php
if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
$image_operations    = new prisakaru_atd_Image_Operations();
$database_operations = new prisakaru_atd_Database_Operations();
$req_operations      = new prisakaru_atd_Requests_Operations();
$all_images_list     = $image_operations->get_all_images_list();
$all_without_alt     = $image_operations->get_list_without_alts();
$image_list_table    = new prisakaru_atd_Image_List_Table();
$image_list_table->prepare_items();
$api_key      = get_option('prisakaru_atd_api_key');
$credits_left = $req_operations->get_user_credits_by_api_key($api_key);
?>
<div id="progressModal" class="modal">
    <div class="modal-content">
    <div id="progress-body"></div>
    <button id="cancelButton">Cancel</button>
    </div>
</div>
<div class="wrap">
    <div class="row w-100">
        <div class="col-2">
            <p>Total images count: <?php echo count($image_operations->get_all_images_list()); ?></p>
        </div>
        <div class="col-2">
            <p>Images without alt: <?php echo count($image_operations->get_list_without_alts()); ?></p>
        </div>
    </div>
    <div class="row w-100 mt-30">
        <div class="col-3 px-10">
            <button class="button" id="button_generate_all_images_alt">Generate alt for all images</button>
        </div>
        <div class="col-3 px-10">
            <button class="button" id="button_generate_images_alt">Generate alt images without alt</button>
        </div>
    </div>
    <div id="refreshMessage"></div>
    <h3 class="mt-30">Currently you have: <?php echo esc_html($credits_left); ?> credits</h3>
    <a href="https://prisakaru.lt/#packages-container">Buy more credits</a>
    <p class="mt-30"><strong>NOTE: Every image alt generation will cost you 1 credit for each.</strong></p>
    <h3 class="mt-30">All images</h3>
    <?php $image_list_table->display(); ?>
</div>
