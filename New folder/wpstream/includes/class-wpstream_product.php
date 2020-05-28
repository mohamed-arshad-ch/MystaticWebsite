<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


/**
 * Description of class-wpstream-wpstream_product
 *
 * @author cretu
 */
class Wpstream_Product {
        
    public function __construct() {
        add_action( 'create_term', array($this,'wpstream_redo_transient') );
        add_action( 'edit_term', array($this,'wpstream_redo_transient') );
        add_action( 'delete_term', array($this,'wpstream_redo_transient') );
    }
    
    
    public function wpstream_redo_transient(){
        delete_transient('wpstream_woo_movie_category_values');
        delete_transient('wpstream_woo_actors_category_values');
        delete_transient('wpstream_woo_product_cat');
        delete_transient('wpstream_woo_movie_rating_category_values');
        $this->wpstream_generate_woo_movie_category_values_shortcode();
        $this->wpstream_generate_actors_category_values_shortcode();
        $this->wpstream_generate_woo_product_tax_values_shortcode();
        $this->wpstream_generate_movie_rating_category_values_shortcode();
    }
    
    
    
    /**
    * Register custom post type
    *
    * @link https://codex.wordpress.org/Function_Reference/register_post_type
    */
    private function register_single_post_type( $fields ) {

    
        $labels = array(
            'name'                  => $fields['plural'],
            'singular_name'         => $fields['singular'],
            'menu_name'             => $fields['menu_name'],
            'new_item'              => sprintf( __( 'New %s', 'wpstream' ), $fields['singular'] ),
            'add_new_item'          => sprintf( __( 'Add new %s', 'wpstream' ), $fields['singular'] ),
            'edit_item'             => sprintf( __( 'Edit %s', 'wpstream' ), $fields['singular'] ),
            'view_item'             => sprintf( __( 'View %s', 'wpstream' ), $fields['singular'] ),
            'view_items'            => sprintf( __( 'View %s', 'wpstream' ), $fields['plural'] ),
            'search_items'          => sprintf( __( 'Search %s', 'wpstream' ), $fields['plural'] ),
            'not_found'             => sprintf( __( 'No %s found', 'wpstream' ), strtolower( $fields['plural'] ) ),
            'not_found_in_trash'    => sprintf( __( 'No %s found in trash', 'wpstream' ), strtolower( $fields['plural'] ) ),
            'all_items'             => sprintf( __( 'All %s', 'wpstream' ), $fields['plural'] ),
            'archives'              => sprintf( __( '%s Archives', 'wpstream' ), $fields['singular'] ),
            'attributes'            => sprintf( __( '%s Attributes', 'wpstream' ), $fields['singular'] ),
            'insert_into_item'      => sprintf( __( 'Insert into %s', 'wpstream' ), strtolower( $fields['singular'] ) ),
            'uploaded_to_this_item' => sprintf( __( 'Uploaded to this %s', 'wpstream' ), strtolower( $fields['singular'] ) ),

            /* Labels for hierarchical post types only. */
            'parent_item'           => sprintf( __( 'Parent %s', 'wpstream' ), $fields['singular'] ),
            'parent_item_colon'     => sprintf( __( 'Parent %s:', 'wpstream' ), $fields['singular'] ),

            /* Custom archive label.  Must filter 'post_type_archive_title' to use. */
			'archive_title'        => $fields['plural'],
        );

        $args = array(
            'labels'             => $labels,
            'description'        => ( isset( $fields['description'] ) ) ? $fields['description'] : '',
            'public'             => ( isset( $fields['public'] ) ) ? $fields['public'] : true,
            'publicly_queryable' => ( isset( $fields['publicly_queryable'] ) ) ? $fields['publicly_queryable'] : true,
            'exclude_from_search'=> ( isset( $fields['exclude_from_search'] ) ) ? $fields['exclude_from_search'] : false,
            'show_ui'            => ( isset( $fields['show_ui'] ) ) ? $fields['show_ui'] : true,
            'show_in_menu'       => ( isset( $fields['show_in_menu'] ) ) ? $fields['show_in_menu'] : true,
            'query_var'          => ( isset( $fields['query_var'] ) ) ? $fields['query_var'] : true,
            'show_in_admin_bar'  => ( isset( $fields['show_in_admin_bar'] ) ) ? $fields['show_in_admin_bar'] : true,
            'capability_type'    => ( isset( $fields['capability_type'] ) ) ? $fields['capability_type'] : 'post',
            'has_archive'        => ( isset( $fields['has_archive'] ) ) ? $fields['has_archive'] : true,
            'hierarchical'       => ( isset( $fields['hierarchical'] ) ) ? $fields['hierarchical'] : true,
            'supports'           => ( isset( $fields['supports'] ) ) ? $fields['supports'] : array(
                    'title',
                    'editor',
                    'excerpt',
                    'author',
                    'thumbnail',
                    'comments',
                    'trackbacks',
                    'custom-fields',
                    'revisions',
                    'page-attributes',
                    'post-formats',
            ),
            'menu_position'      => ( isset( $fields['menu_position'] ) ) ? $fields['menu_position'] : 21,
            'menu_icon'          => ( isset( $fields['menu_icon'] ) ) ? $fields['menu_icon']: 'dashicons-admin-generic',
            'show_in_nav_menus'  => ( isset( $fields['show_in_nav_menus'] ) ) ? $fields['show_in_nav_menus'] : true,
            'taxonomies'          => array( 'category','post_tag' ),
        );

        if ( isset( $fields['rewrite'] ) ) {

            /**
             *  Add $this->plugin_name as translatable in the permalink structure,
             *  to avoid conflicts with other plugins which may use customers as well.
             */
            $args['rewrite'] = $fields['rewrite'];
        }

        if ( $fields['custom_caps'] ) {

            /**
             * Provides more precise control over the capabilities than the defaults.  By default, WordPress
             * will use the 'capability_type' argument to build these capabilities.  More often than not,
             * this results in many extra capabilities that you probably don't need.  The following is how
             * I set up capabilities for many post types, which only uses three basic capabilities you need
             * to assign to roles: 'manage_examples', 'edit_examples', 'create_examples'.  Each post type
             * is unique though, so you'll want to adjust it to fit your needs.
             *
             * @link https://gist.github.com/creativembers/6577149
             * @link http://justintadlock.com/archives/2010/07/10/meta-capabilities-for-custom-post-types
             */
            $args['capabilities'] = array(

                // Meta capabilities
                'edit_post'                 => 'edit_' . strtolower( $fields['singular'] ),
                'read_post'                 => 'read_' . strtolower( $fields['singular'] ),
                'delete_post'               => 'delete_' . strtolower( $fields['singular'] ),

                // Primitive capabilities used outside of map_meta_cap():
                'edit_posts'                => 'edit_' . strtolower( $fields['plural'] ),
                'edit_others_posts'         => 'edit_others_' . strtolower( $fields['plural'] ),
                'publish_posts'             => 'publish_' . strtolower( $fields['plural'] ),
                'read_private_posts'        => 'read_private_' . strtolower( $fields['plural'] ),

                // Primitive capabilities used within map_meta_cap():
                'delete_posts'              => 'delete_' . strtolower( $fields['plural'] ),
                'delete_private_posts'      => 'delete_private_' . strtolower( $fields['plural'] ),
                'delete_published_posts'    => 'delete_published_' . strtolower( $fields['plural'] ),
                'delete_others_posts'       => 'delete_others_' . strtolower( $fields['plural'] ),
                'edit_private_posts'        => 'edit_private_' . strtolower( $fields['plural'] ),
                'edit_published_posts'      => 'edit_published_' . strtolower( $fields['plural'] ),
                'create_posts'              => 'edit_' . strtolower( $fields['plural'] )

            );

            /**
             * Adding map_meta_cap will map the meta correctly.
             * @link https://wordpress.stackexchange.com/questions/108338/capabilities-and-custom-post-types/108375#108375
             */
            $args['map_meta_cap'] = true;

            /**
             * Assign capabilities to users
             * Without this, users - also admins - can not see post type.
             */
            $this->assign_capabilities( $args['capabilities'], $fields['custom_caps_users'] );
        }

        register_post_type( $fields['slug'], $args );

        /**
         * Register Taxnonmies if any
         * @link https://codex.wordpress.org/Function_Reference/register_taxonomy
         */
        if ( isset( $fields['taxonomies'] ) && is_array( $fields['taxonomies'] ) ) {

            foreach ( $fields['taxonomies'] as $taxonomy ) {

                $this->register_single_post_type_taxnonomy( $taxonomy );

            }

        }
        
        $this->wpstream_generate_woo_movie_category_values_shortcode();
        $this->wpstream_generate_actors_category_values_shortcode();
        $this->wpstream_generate_woo_product_tax_values_shortcode();
        $this->wpstream_generate_movie_rating_category_values_shortcode();
        
    }

    
    
    
    public function  wpstream_generate_woo_movie_category_values_shortcode(){
    
        $all_tax_labels=array();
        $property_action_category_values = get_transient('wpstream_woo_movie_category_values');
        if($property_action_category_values===false){
            $terms_category = get_terms( array(
                'taxonomy' => 'wpstream_category',
                'hide_empty' => false,
            ) );
            
            if( is_array($terms_category) ){
                foreach($terms_category as $term){

                    $temp_array=array();
                    $temp_array['label'] = $term->name;
                    $temp_array['value'] = $term->term_id;
                    $all_tax[]=$temp_array;
                    $action_array[]=$temp_array;
                    $all_tax_labels[$term->term_id]=  $term->name;
                    // tax based_array
                    $property_action_category_values[] = $temp_array;

                }
            }
            set_transient('wpstream_woo_movie_category_values_label',$all_tax_labels,60*60*4);
            set_transient('wpstream_woo_movie_category_values',$property_action_category_values,60*60*4);
        }
        return $property_action_category_values;
    }
    
    
    
    
    public  function wpstream_generate_actors_category_values_shortcode(){
        $all_tax_labels=array();
        $movie_actors_values = get_transient('wpstream_woo_actors_category_values');

        if($movie_actors_values===false){
            $terms_actors= get_terms( array(
                'taxonomy' => 'wpstream_actors',
                'hide_empty' => false,
            ) );


            if( is_array($terms_actors) ){
                foreach($terms_actors as $term){
                    $places[$term->name]= $term->term_id;
                    $temp_array=array();
                    $temp_array['label'] = $term->name;
                    $temp_array['value'] = $term->term_id;
                    $all_tax[]=$temp_array;

                    $all_tax_labels[$term->term_id]=  $term->name;
                    $movie_actors_values[] = $temp_array;
                }
            }

         
            set_transient('wpstream_woo_actors_category_values_label',$all_tax_labels,60*60*4);
            set_transient('wpstream_woo_actors_category_values',$movie_actors_values,60*60*4);
        }
        return $movie_actors_values;
    }
    
    
    public function wpstream_generate_woo_product_tax_values_shortcode(){
        $all_tax_labels=array();
        $product_categ_values = get_transient('wpstream_woo_product_cat');
      
        if($product_categ_values===false){
            $product_cat= get_terms( array(
                'taxonomy' => 'product_cat',
                'hide_empty' => false,
            ) );



            if( is_array($product_cat) ){
                foreach($product_cat as $term){
                    $places[$term->name]= $term->term_id;
                    $temp_array=array();
                    $temp_array['label'] = $term->name;
                    $temp_array['value'] = $term->term_id;
                    $all_places[]=$temp_array;
                    $area_array[]=$temp_array;
                    $all_tax[]=$temp_array;
                    $all_tax_labels[$term->term_id]=  $term->name;
                    // tax based_array
                    $product_categ_values[] = $temp_array;

                }
            }
            set_transient('wpstream_woo_product_cat_label',$all_tax_labels,60*60*4);
            set_transient('wpstream_woo_product_cat',$product_categ_values,60*60*4);
        }

        return $product_categ_values;
    }


    public function wpstream_generate_movie_rating_category_values_shortcode(){
       $all_tax_labels=array();
        $movie_rating_values = get_transient('wpstream_woo_movie_rating_category_values');
        if($movie_rating_values===false){
            $movie_ratiog= get_terms( array(
                'taxonomy' => 'wpstream_movie_rating',
                'hide_empty' => false,
            ) );
            if( is_array($movie_ratiog) ){
                foreach($movie_ratiog as $term){
                    $places[$term->name]= $term->term_id;
                    $temp_array=array();
                    $temp_array['label'] = $term->name;
                    $temp_array['value'] = $term->term_id;
                    $all_places[]=$temp_array;
                    $area_array[]=$temp_array;
                    $all_tax[]=$temp_array;
                    $all_tax_labels[$term->term_id]=  $term->name;
                    // tax based_array
                    $movie_rating_values[] = $temp_array;

                }
            }
            set_transient('wpstream_woo_movie_rating_category_values_label',$all_tax_labels,60*60*4);
            set_transient('wpstream_woo_movie_rating_category_values',$movie_rating_values,60*60*4);
        }

        return $movie_rating_values;

    }



    
    /**
    * Register taxonomy custom post type
    *
    * @link https://codex.wordpress.org/Function_Reference/register_taxonomy
    */
    
    private function register_single_post_type_taxnonomy( $tax_fields ) {

        $labels = array(
            'name'                       => $tax_fields['plural'],
            'singular_name'              => $tax_fields['single'],
            'menu_name'                  => $tax_fields['plural'],
            'all_items'                  => sprintf( __( 'All %s' , 'wpstream' ), $tax_fields['plural'] ),
            'edit_item'                  => sprintf( __( 'Edit %s' , 'wpstream' ), $tax_fields['single'] ),
            'view_item'                  => sprintf( __( 'View %s' , 'wpstream' ), $tax_fields['single'] ),
            'update_item'                => sprintf( __( 'Update %s' , 'wpstream' ), $tax_fields['single'] ),
            'add_new_item'               => sprintf( __( 'Add New %s' , 'wpstream' ), $tax_fields['single'] ),
            'new_item_name'              => sprintf( __( 'New %s Name' , 'wpstream' ), $tax_fields['single'] ),
            'parent_item'                => sprintf( __( 'Parent %s' , 'wpstream' ), $tax_fields['single'] ),
            'parent_item_colon'          => sprintf( __( 'Parent %s:' , 'wpstream' ), $tax_fields['single'] ),
            'search_items'               => sprintf( __( 'Search %s' , 'wpstream' ), $tax_fields['plural'] ),
            'popular_items'              => sprintf( __( 'Popular %s' , 'wpstream' ), $tax_fields['plural'] ),
            'separate_items_with_commas' => sprintf( __( 'Separate %s with commas' , 'wpstream' ), $tax_fields['plural'] ),
            'add_or_remove_items'        => sprintf( __( 'Add or remove %s' , 'wpstream' ), $tax_fields['plural'] ),
            'choose_from_most_used'      => sprintf( __( 'Choose from the most used %s' , 'wpstream' ), $tax_fields['plural'] ),
            'not_found'                  => sprintf( __( 'No %s found' , 'wpstream' ), $tax_fields['plural'] ),
        );

        $args = array(
        	'label'                 => $tax_fields['plural'],
        	'labels'                => $labels,
        	'hierarchical'          => ( isset( $tax_fields['hierarchical'] ) )          ? $tax_fields['hierarchical']          : true,
        	'public'                => ( isset( $tax_fields['public'] ) )                ? $tax_fields['public']                : true,
        	'show_ui'               => ( isset( $tax_fields['show_ui'] ) )               ? $tax_fields['show_ui']               : true,
        	'show_in_nav_menus'     => ( isset( $tax_fields['show_in_nav_menus'] ) )     ? $tax_fields['show_in_nav_menus']     : true,
        	'show_tagcloud'         => ( isset( $tax_fields['show_tagcloud'] ) )         ? $tax_fields['show_tagcloud']         : true,
        	'meta_box_cb'           => ( isset( $tax_fields['meta_box_cb'] ) )           ? $tax_fields['meta_box_cb']           : null,
        	'show_admin_column'     => ( isset( $tax_fields['show_admin_column'] ) )     ? $tax_fields['show_admin_column']     : true,
        	'show_in_quick_edit'    => ( isset( $tax_fields['show_in_quick_edit'] ) )    ? $tax_fields['show_in_quick_edit']    : true,
        	'update_count_callback' => ( isset( $tax_fields['update_count_callback'] ) ) ? $tax_fields['update_count_callback'] : '',
        	'show_in_rest'          => ( isset( $tax_fields['show_in_rest'] ) )          ? $tax_fields['show_in_rest']          : true,
        	'rest_base'             => $tax_fields['taxonomy'],
        	'rest_controller_class' => ( isset( $tax_fields['rest_controller_class'] ) ) ? $tax_fields['rest_controller_class'] : 'WP_REST_Terms_Controller',
        	'query_var'             => $tax_fields['taxonomy'],
        	'rewrite'               => ( isset( $tax_fields['rewrite'] ) )               ? $tax_fields['rewrite']               : true,
        	'sort'                  => ( isset( $tax_fields['sort'] ) )                  ? $tax_fields['sort']                  : '',
        );

        $args = apply_filters( $tax_fields['taxonomy'] . '_args', $args );

        register_taxonomy( $tax_fields['taxonomy'], $tax_fields['post_types'], $args );

    }

    /**
     * Assign capabilities to users
     *
     * @link https://codex.wordpress.org/Function_Reference/register_post_type
     * @link https://typerocket.com/ultimate-guide-to-custom-post-types-in-wordpress/
     */
    public function assign_capabilities( $caps_map, $users  ) {

        foreach ( $users as $user ) {

            $user_role = get_role( $user );

            foreach ( $caps_map as $cap_map_key => $capability ) {

                $user_role->add_cap( $capability );

            }

        }

    }

    /**
     * CUSTOMIZE CUSTOM POST TYPE AS YOU WISH.
     */


    public   function wpstream_category_callback_function($tag){
            if(is_object ($tag)){
                $t_id                       =   $tag->term_id;
                $term_meta                  =   get_option( "taxonomy_$t_id");
                $pagetax                    =   $term_meta['pagetax'] ? $term_meta['pagetax'] : '';
                $category_featured_image    =   $term_meta['category_featured_image'] ? $term_meta['category_featured_image'] : '';
                $category_tagline           =   $term_meta['category_tagline'] ? $term_meta['category_tagline'] : '';
                $category_tagline           =   stripslashes($category_tagline);
                $category_attach_id         =   $term_meta['category_attach_id'] ? $term_meta['category_attach_id'] : '';
            }else{
                $pagetax                    =   '';
                $category_featured_image    =   '';
                $category_tagline           =   '';
                $category_attach_id         =   '';
            }

            print'
            <table class="form-table">
            <tbody>    
                <tr class="form-field">
                    <th scope="row" valign="top"><label for="term_meta[pagetax]">'.esc_html__( 'Page id for this term','wpstream').'</label></th>
                    <td> 
                        <input type="text" name="term_meta[pagetax]" class="postform" value="'.$pagetax.'">  
                        <p class="description">'.esc_html__( 'Page id for this term','wpstream').'</p>
                    </td>

                    <tr valign="top">
                        <th scope="row"><label for="category_featured_image">'.esc_html__( 'Featured Image','wpstream').'</label></th>
                        <td>
                            <input id="category_featured_image" type="text" class="postform" size="36" name="term_meta[category_featured_image]" value="'.$category_featured_image.'" />
                            <input id="category_featured_image_button" type="button"  class="upload_button button category_featured_image_button" value="'.esc_html__( 'Upload Image','wpstream').'" />
                            <input id="category_attach_id" type="hidden" size="36" name="term_meta[category_attach_id]" value="'.$category_attach_id.'" />
                        </td>
                    </tr> 

                    <tr valign="top">
                        <th scope="row"><label for="term_meta[category_tagline]">'. esc_html__( 'Category Tagline','wpstream').'</label></th>
                        <td>
                            <input id="category_tagline" type="text" size="36" name="term_meta[category_tagline]" value="'.$category_tagline.'" />
                        </td>
                    </tr> 



                    <input id="category_tax" type="hidden" size="36" name="term_meta[category_tax]" value="'.$tag->taxonomy.'" />


                </tr>
            </tbody>
            </table>';
    }

    
    
    
    
    
     /**
     * CUSTOMIZE CUSTOM POST TYPE AS YOU WISH.
     */


    public function wpstream_category_callback_add_function($tag){
        if(is_object ($tag)){
            $t_id                       =   $tag->term_id;
            $term_meta                  =   get_option( "taxonomy_$t_id");
            $pagetax                    =   $term_meta['pagetax'] ? $term_meta['pagetax'] : '';
            $category_featured_image    =   $term_meta['category_featured_image'] ? $term_meta['category_featured_image'] : '';
            $category_tagline           =   $term_meta['category_tagline'] ? $term_meta['category_tagline'] : '';
            $category_attach_id         =   $term_meta['category_attach_id'] ? $term_meta['category_attach_id'] : '';
        }else{
            $pagetax                    =   '';
            $category_featured_image    =   '';
            $category_tagline           =   '';
            $category_attach_id         =   '';

        }

        print'
        <div class="form-field">
        <label for="term_meta[pagetax]">'. esc_html__( 'Page id for this term','wpstream').'</label>
            <input type="text" name="term_meta[pagetax]" class="postform" value="'.$pagetax.'">  
        </div>

        <div class="form-field">
            <label for="term_meta[pagetax]">'. esc_html__( 'Featured Image','wpstream').'</label>
            <input id="category_featured_image" type="text" size="36" name="term_meta[category_featured_image]" value="'.$category_featured_image.'" />
            <input id="category_featured_image_button" type="button"  class="upload_button button category_featured_image_button" value="'.esc_html__( 'Upload Image','wpstream').'" />
           <input id="category_attach_id" type="hidden" size="36" name="term_meta[category_attach_id]" value="'.$category_attach_id.'" />

        </div>     

        <div class="form-field">
        <label for="term_meta[category_tagline]">'. esc_html__( 'Category Tagline','wpstream').'</label>
            <input id="category_tagline" type="text" size="36" name="term_meta[category_tagline]" value="'.$category_tagline.'" />
        </div> 
        <input id="category_tax" type="hidden" size="36" name="term_meta[category_tax]" value="'.$tag->taxonomy.'" />
        ';
    }

    /**
     * CUSTOMIZE CUSTOM POST TYPE AS YOU WISH.
     */
    

    function wpstream_category_save_extra_fields_callback($term_id ){
        if ( isset( $_POST['term_meta'] ) ) {
            $t_id = $term_id;
            $term_meta = get_option( "taxonomy_$t_id");
            $cat_keys = array_keys($_POST['term_meta']);
            $allowed_html   =   array();
                foreach ($cat_keys as $key){
                    $key=sanitize_key($key);
                    if (isset($_POST['term_meta'][$key])){
                        $term_meta[$key] =  wp_kses( $_POST['term_meta'][$key],$allowed_html);
                    }
                }
            //save the option array
             update_option( "taxonomy_$t_id", $term_meta );
        }
    }

    
    
    
   

    /**
     * Create post types
     */
    public function create_custom_post_type() {

        /**
         * This is not all the fields, only what I find important. Feel free to change this function ;)
         *
         * @link https://codex.wordpress.org/Function_Reference/register_post_type
         *
         * For more info on fields:
         * @link https://github.com/JoeSz/WordPress-Plugin-Boilerplate-Tutorial/blob/9fb56794bc1f8aebfe04e99b15881db0c4bc61bd/wpstream/includes/class-wpstream-post_types.php#L230
         */
        $post_types_fields = array(
            array(
                'slug'                  =>  'wpstream_product',
                'singular'              => __( 'Free Live Channel / Free Video','wpstream'),
                'plural'                => __( 'Free Live Channels / Free Videos','wpstream'),
                'menu_name'             => __( 'Free Live Channels / Free Videos','wpstream'),
                'description'           => __( 'Free Live Channels / Free Videos','wpstream'),
                'has_archive'           => true,
                'hierarchical'          => false,
                'menu_icon'             => 'dashicons-tag',
                'rewrite'               => array(
                                            'slug'                  => 'wpstream',
                                            'with_front'            => true,
                                            'pages'                 => true,
                                            'feeds'                 => true,
                                            'ep_mask'               => EP_PERMALINK,
                                        ),
                'menu_position'         => 21,
                'public'                => true,
                'publicly_queryable'    => true,
                'exclude_from_search'   => true,
                'show_ui'               => true,
                'show_in_menu'          => true,
                'query_var'             => true,
                'show_in_admin_bar'     => true,
                'show_in_nav_menus'     => true,
                'supports'              => array(
                    'title',
                    'editor',
                    'thumbnail',
                    'comments',
                  
                ),
                'custom_caps'           => true,
                'custom_caps_users'     => array(
                    'administrator',
                ),
                'taxonomies'            => array(

                   
                    array(
                        'taxonomy'          => 'wpstream_actors',
                        'plural'            => esc_html__('Actors','wpstream'),
                        'single'            => esc_html__('Actor','wpstream'),
                        'post_types'        =>  array('wpstream_product','product'),
                        'hierarchical'      => true,
                        'query_var'         => true,
                        'rewrite'           => array( 'slug' => 'actors' )
                    ),
                    
                    array(
                        'taxonomy'          => 'wpstream_category',
                        'plural'            => esc_html__('Media Categories','wpstream'),
                        'single'            => esc_html__('Media Category','wpstream'),
                        'post_types'        =>  array('wpstream_product','product'),
                        'hierarchical'      => true,
                        'query_var'         => true,
                        'rewrite'           => array( 'slug' => 'media_category' )
                    ),
                    
                    array(
                        'taxonomy'          => 'wpstream_movie_rating',
                        'plural'            => esc_html__('Movie Ratings','wpstream'),
                        'single'            => esc_html__('Movie Rating','wpstream'),
                        'post_types'        =>  array('wpstream_product','product'),
                        'hierarchical'      => true,
                        'query_var'         => true,
                        'rewrite'           => array( 'slug' => 'rating' )
                    ),

                ),
            ),
        );

        
        
        // loop torugh custom post type array and register
        foreach ( $post_types_fields as $fields ) {

            $this->register_single_post_type( $fields );

        }

       

    }

    
    
    
       
     

       
}
