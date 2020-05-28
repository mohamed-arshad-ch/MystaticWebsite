<?php

class WC_Product_Video_On_Demand extends WC_Product {
    public function __construct( $product ) {
        parent::__construct( $product );
          $this->supports[]   = 'ajax_add_to_cart';
    }

    public function get_type() {
        return 'video_on_demand';
    }
}