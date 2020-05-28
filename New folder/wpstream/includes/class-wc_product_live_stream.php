<?php
 class WC_Product_Live_Stream extends WC_Product{

    public function __construct( $product ) {
        parent::__construct( $product );
          $this->supports[]   = 'ajax_add_to_cart';
    }

    // Needed since Woocommerce version 3
    public function get_type() {
        return 'live_stream';
    }
}