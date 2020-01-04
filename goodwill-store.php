<?php
/*
Plugin Name: GoodWill Store
Plugin URI: https://github.com/walegbenga/GoodWillStore
Description: Create a GoodWill Store to display product information
Version: .0
Author: Gbenga Ogunbule
Email: walegbenga807@gmail.com
License: GPLv2
*/

/*  Copyright 2020  Gbenga Ogunbule  (email : walegbenga807@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

// Call function when plugin is activated
register_activation_hook( __FILE__, 'goodwill_store_install' );

function goodwill_store_install() {

    //setup default option values
    $gw_options_arr = array(
        'currency_sign' => '$'
    );

    //save our default option values
    update_option( 'goodwill_options', $gw_options_arr );

}


// Action hook to initialize the plugin
add_action( 'init', 'goodwill_store_init' );

//Initialize the Halloween Store
function goodwill_store_init() {

	//register the products custom post type
	$labels = array(
        'name'               => __( 'Products', 'goodwill-plugin' ),
        'singular_name'      => __( 'Product', 'goodwill-plugin' ),
        'add_new'            => __( 'Add New', 'goodwill-plugin' ),
        'add_new_item'       => __( 'Add New Product', 'goodwill-plugin' ),
        'edit_item'          => __( 'Edit Product', 'goodwill-plugin' ),
        'new_item'           => __( 'New Product', 'goodwill-plugin' ),
        'all_items'          => __( 'All Products', 'goodwill-plugin' ),
        'view_item'          => __( 'View Product', 'goodwill-plugin' ),
        'search_items'       => __( 'Search Products', 'goodwill-plugin' ),
        'not_found'          =>  __( 'No products found', 'goodwill-plugin' ),
        'not_found_in_trash' => __( 'No products found in Trash', 'goodwill-plugin' ),
        'menu_name'          => __( 'Products', 'goodwill-plugin' )
    );

    $args = array(
        'labels'             => $labels,
        'public'             => true,
        'publicly_queryable' => true,
        'show_ui'            => true,
        'show_in_menu'       => true,
        'query_var'          => true,
        'rewrite'            => true,
        'capability_type'    => 'post',
        'has_archive'        => true,
        'hierarchical'       => false,
        'menu_position'      => null,
        'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt' )
    );

	register_post_type( 'goodwill-products', $args );

}

// Action hook to add the post products menu item
add_action( 'admin_menu', 'goodwill_store_menu' );

//create the GoodWill Masks sub-menu
function goodwill_store_menu() {

    add_options_page( __( 'GoodWill Store Settings Page', 'goodwill-plugin' ), __( 'GoodWill Store Settings', 'goodwill-plugin' ), 'manage_options', 'goodwill-store-settings', 'goodwill_store_settings_page' );

}

//build the plugin settings page
function goodwill_store_settings_page() {

    //load the plugin options array
    $gw_options_arr = get_option( 'goodwill_options' );

	//set the option array values to variables
	$gw_inventory = ( ! empty( $gw_options_arr['show_inventory'] ) ) ? $gw_options_arr['show_inventory'] : '';
	$gw_currency_sign = $gw_options_arr['currency_sign'];
    ?>
    <div class="wrap">
    <h2><?php _e( 'Goodwill Store Options', 'goodwill-plugin' ) ?></h2>

    <form method="post" action="options.php">
        <?php settings_fields( 'goodwill-settings-group' ); ?>
        <table class="form-table">
            <tr valign="top">
            <th scope="row"><?php _e( 'Show Product Inventory', 'goodwill-plugin' ) ?></th>
            <td><input type="checkbox" name="goodwill_options[show_inventory]" <?php echo checked( $gw_inventory, 'on' ); ?> /></td>
            </tr>

            <tr valign="top">
            <th scope="row"><?php _e( 'Currency Sign', 'goodwill-plugin' ) ?></th>
            <td><input type="text" name="goodwill_options[currency_sign]" value="<?php echo esc_attr( $gw_currency_sign ); ?>" size="1" maxlength="1" /></td>
            </tr>
        </table>

        <p class="submit">
        <input type="submit" class="button-primary" value="<?php _e( 'Save Changes', 'goodwill-plugin' ); ?>" />
        </p>

    </form>
    </div>
<?php
}

// Action hook to register the plugin option settings
add_action( 'admin_init', 'goodwill_store_register_settings' );

function goodwill_store_register_settings() {

    //register the array of settings
    register_setting( 'goodwill-settings-group', 'goodwill_options', 'goodwill_sanitize_options' );

}

function goodwill_sanitize_options( $options ) {

	$options['show_inventory'] = ( ! empty( $options['show_inventory'] ) ) ? sanitize_text_field( $options['show_inventory'] ) : '';
	$options['currency_sign'] = ( ! empty( $options['currency_sign'] ) ) ? sanitize_text_field( $options['currency_sign'] ) : '';

	return $options;

}

//Action hook to register the Products meta box
add_action( 'add_meta_boxes', 'goodwill_store_register_meta_box' );

function goodwill_store_register_meta_box() {

    // create our custom meta box
	add_meta_box( 'goodwill-product-meta', __( 'Product Information','goodwill-plugin' ), 'goodwill_meta_box', 'goodwill-products', 'side', 'default' );

}

//build product meta box
function goodwill_meta_box( $post ) {

    // retrieve our custom meta box values
    $gw_meta = get_post_meta( $post->ID, '_goodwill_product_data', true );

    $gw_sku = ( ! empty( $gw_meta['sku'] ) ) ? $gw_meta['sku'] : '';
    $gw_price = ( ! empty( $gw_meta['price'] ) ) ? $gw_meta['price'] : '';
    $gw_weight = ( ! empty( $gw_meta['weight'] ) ) ? $gw_meta['weight'] : '';
    $gw_color = ( ! empty( $gw_meta['color'] ) ) ? $gw_meta['color'] : '';
    $gw_inventory = ( ! empty( $gw_meta['inventory'] ) ) ? $gw_meta['inventory'] : '';

	//nonce field for security
	wp_nonce_field( 'meta-box-save', 'goodwill-plugin' );

    // display meta box form
    echo '<table>';
    echo '<tr>';
    echo '<td>' .__('Sku', 'goodwill-plugin').':</td><td><input type="text" name="goodwill_product[sku]" value="'.esc_attr( $gw_sku ).'" size="10"></td>';
    echo '</tr><tr>';
    echo '<td>' .__('Price', 'goodwill-plugin').':</td><td><input type="text" name="goodwill_product[price]" value="'.esc_attr( $gw_price ).'" size="5"></td>';
    echo '</tr><tr>';
    echo '<td>' .__('Weight', 'goodwill-plugin').':</td><td><input type="text" name="goodwill_product[weight]" value="'.esc_attr( $gw_weight ).'" size="5"></td>';
    echo '</tr><tr>';
    echo '<td>' .__('Color', 'goodwill-plugin').':</td><td><input type="text" name="goodwill_product[color]" value="'.esc_attr( $gw_color ).'" size="5"></td>';
    echo '</tr><tr>';
    echo '<td>Inventory:</td><td><select name="goodwill_product[inventory]" id="goodwill_product[inventory]">
            <option value="In Stock"' .selected( $gw_inventory, 'In Stock', false ). '>' .__( 'In Stock', 'goodwill-plugin' ). '</option>
            <option value="Backordered"' .selected( $gw_inventory, 'Backordered', false ). '>' .__( 'Backordered', 'goodwill-plugin' ). '</option>
            <option value="Out of Stock"' .selected( $gw_inventory, 'Out of Stock', false ). '>' .__( 'Out of Stock', 'goodwill-plugin' ). '</option>
            <option value="Discontinued"' .selected( $gw_inventory, 'Discontinued', false ). '>' .__( 'Discontinued', 'goodwill-plugin' ). '</option>
        </select></td>';
    echo '</tr>';

    //display the meta box shortcode legend section
    echo '<tr><td colspan="2"><hr></td></tr>';
    echo '<tr><td colspan="2"><strong>' .__( 'Shortcode Legend', 'goodwill-plugin' ).'</strong></td></tr>';
    echo '<tr><td>' .__( 'Sku', 'goodwill-plugin' ) .':</td><td>[gw show=sku]</td></tr>';
    echo '<tr><td>' .__( 'Price', 'goodwill-plugin' ).':</td><td>[gw show=price]</td></tr>';
    echo '<tr><td>' .__( 'Weight', 'goodwill-plugin' ).':</td><td>[gw show=weight]</td></tr>';
    echo '<tr><td>' .__( 'Color', 'goodwill-plugin' ).':</td><td>[gw show=color]</td></tr>';
    echo '<tr><td>' .__( 'Inventory', 'goodwill-plugin' ).':</td><td>[gw show=inventory]</td></tr>';
    echo '</table>';
}

// Action hook to save the meta box data when the post is saved
add_action( 'save_post','goodwill_store_save_meta_box' );

//save meta box data
function goodwill_store_save_meta_box( $post_id ) {

	//verify the post type is for Halloween Products and metadata has been posted
	if ( get_post_type( $post_id ) == 'goodwill-products' && isset( $_POST['goodwill_product'] ) ) {

		//if autosave skip saving data
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
			return;

		//check nonce for security
		wp_verify_nonce( 'meta-box-save', 'goodwill-plugin' );

        //store option values in a variable
        $goodwill_product_data = $_POST['goodwill_product'];

        //use array map function to sanitize option values
        $goodwill_product_data = array_map( 'sanitize_text_field', $goodwill_product_data );

        // save the meta box data as post metadata
        update_post_meta( $post_id, '_goodwill_product_data', $goodwill_product_data );

	}

}

// Action hook to create the products shortcode
add_shortcode( 'gw', 'goodwill_store_shortcode' );

//create shortcode
function goodwill_store_shortcode( $atts, $content = null ) {
    global $post;

    extract( shortcode_atts( array(
        "show" => ''
    ), $atts ) );

    //load options array
    $gw_options_arr = get_option( 'goodwill_options' );

    //load product data
    $gw_product_data = get_post_meta( $post->ID, '_goodwill_product_data', true );

    if ( $show == 'sku') {

        $gw_show = ( ! empty( $gw_product_data['sku'] ) ) ? $gw_product_data['sku'] : '';

    }elseif ( $show == 'price' ) {

        $gw_show = $gw_options_arr['currency_sign'];
        $gw_show = ( ! empty( $gw_product_data['price'] ) ) ? $gw_show . $gw_product_data['price'] : '';

    }elseif ( $show == 'weight' ) {

        $gw_show = ( ! empty( $gw_product_data['weight'] ) ) ? $gw_product_data['weight'] : '';

    }elseif ( $show == 'color' ) {

        $gw_show = ( ! empty( $gw_product_data['color'] ) ) ? $gw_product_data['color'] : '';

    }elseif ( $show == 'inventory' ) {

        $gw_show = ( ! empty( $gw_product_data['inventory'] ) ) ? $gw_product_data['inventory'] : '';

    }

	//return the shortcode value to display
    return $gw_show;
}

// Action hook to create plugin widget
add_action( 'widgets_init', 'goodwill_store_register_widgets' );

//register the widget
function goodwill_store_register_widgets() {

    register_widget( 'gw_widget' );

}

//gw_widget class
class gw_widget extends WP_Widget {

    //process our new widget
    function __construct() {

        $widget_ops = array(
			'classname'   => 'hs-widget-class',
			'description' => __( 'Display Goodwill Products','goodwill-plugin' ) );
        parent::__construct( 'gw_widget', __( 'Products Widget','goodwill-plugin'), $widget_ops );

    }

    //build our widget settings form
    function form( $instance ) {

        $defaults = array(
			'title'           => __( 'Products', 'goodwill-plugin' ),
			'number_products' => '3' );

        $instance = wp_parse_args( (array) $instance, $defaults );
        $title = $instance['title'];
        $number_products = $instance['number_products'];
        ?>
            <p><?php _e('Title', 'goodwill-plugin') ?>:
				<input class="widefat" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" /></p>
            <p><?php _e( 'Number of Products', 'goodwill-plugin' ) ?>:
				<input name="<?php echo $this->get_field_name( 'number_products' ); ?>" type="text" value="<?php echo absint( $number_products ); ?>" size="2" maxlength="2" />
			</p>
        <?php
    }

    //save our widget settings
    function update( $new_instance, $old_instance ) {

        $instance = $old_instance;
        $instance['title'] = sanitize_text_field( $new_instance['title'] );
        $instance['number_products'] = absint( $new_instance['number_products'] );

        return $instance;

    }

     //display our widget
    function widget( $args, $instance ) {
        global $post;

        extract( $args );

        echo $before_widget;
        $title = apply_filters( 'widget_title', $instance['title'] );
        $number_products = $instance['number_products'];

        if ( ! empty( $title ) ) { echo $before_title . esc_html( $title ) . $after_title; };

		//custom query to retrieve products
		$args = array(
			'post_type'			=>	'goodwill-products',
			'posts_per_page'	=>	absint( $number_products )
		);

        $dispProducts = new WP_Query();
        $dispProducts->query( $args );

        while ( $dispProducts->have_posts() ) : $dispProducts->the_post();

            //load options array
            $gw_options_arr = get_option( 'goodwill_options' );

            //load custom meta values
            $gw_product_data = get_post_meta( $post->ID, '_goodwill_product_data', true );

            $gw_price = ( ! empty( $gw_product_data['price'] ) ) ? $gw_product_data['price'] : '';
            $gw_inventory = ( ! empty( $gw_product_data['inventory'] ) ) ? $gw_product_data['inventory'] : '';
            ?>
			<p>
				<a href="<?php the_permalink(); ?>" rel="bookmark" title="<?php the_title_attribute(); ?> Product Information">
				<?php the_title(); ?>
				</a>
			</p>
			<?php
			echo '<p>' .__( 'Price', 'goodwill-plugin' ). ': '.$gw_options_arr['currency_sign'] .$gw_price .'</p>';

            //check if Show Inventory option is enabled
            if ( $gw_options_arr['show_inventory'] ) {

				//display the inventory metadata for this product
                echo '<p>' .__( 'Stock', 'goodwill-plugin' ). ': ' .$gw_inventory .'</p>';

            }
            echo '<hr>';

        endwhile;

		wp_reset_postdata();

        echo $after_widget;

    }

}
