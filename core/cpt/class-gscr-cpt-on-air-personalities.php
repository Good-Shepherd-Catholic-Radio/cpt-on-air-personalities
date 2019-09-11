<?php
/**
 * Class CPT_GSCR_On_Air Personalities
 *
 * Creates the post type.
 *
 * @since 1.0.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

class CPT_GSCR_On_Air_Personalities extends RBM_CPT {

	public $post_type = 'on-air-personality';
	public $p2p = 'radio-show';
	public $label_singular = null;
	public $label_plural = null;
	public $labels = array();
	public $icon = 'microphone';
	public $post_args = array(
		'hierarchical' => true,
		'supports' => array( 'title', 'editor', 'author', 'thumbnail' ),
		'has_archive' => false,
		'rewrite' => array(
			'slug' => 'on-air-personality',
			'with_front' => false,
			'feeds' => false,
			'pages' => true
		),
		'menu_position' => 11,
		//'capability_type' => 'on-air-personality',
	);

	/**
	 * CPT_GSCR_On-Air Personalities constructor.
	 *
	 * @since 1.0.0
	 */
	function __construct() {

		// This allows us to Localize the Labels
		$this->label_singular = __( 'On-Air Personality', 'gscr-cpt-on-air-personalities' );
		$this->label_plural = __( 'On-Air Personalities', 'gscr-cpt-on-air-personalities' );

		$this->labels = array(
			'menu_name' => __( 'On-Air Personalities', 'gscr-cpt-on-air-personalities' ),
			'all_items' => __( 'All On-Air Personalities', 'gscr-cpt-on-air-personalities' ),
		);

		parent::__construct();
		
		add_filter( 'rbm_cpts_available_p2p_posts', array( $this, 'p2p_query_args' ), 10, 3 );
		
		add_filter( 'rbm_cpts_p2p_select_args', array( $this, 'p2p_select_args' ), 10, 3 );
		
		add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'admin_column_add' ) );
		
		add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'admin_column_display' ), 10, 2 );
		
		add_filter( 'redirect_canonical', array( $this, 'redirect_canonical' ) );
		
	}
	
	/**
	 * Modify the RBM CPTs P2P Query Args for the Select Field
	 * 
	 * @param		array  $args         WP_Query Args
	 * @param		string $post_type    Current Post Tpe
	 * @param		string $relationship Connected-to Post Type
	 *                                                
	 * @access		public
	 * @since		1.0.0
	 * @return		array  WP_Query Args
	 */
	public function p2p_query_args( $args, $post_type, $relationship ) {
		
		// Recurring Events are technically Child Posts. This prevents a rediculus list of thousands of Events from showing
		$args['post_parent'] = 0;
		$args['post_type'] = 'tribe_events';
		$args['eventDisplay'] = 'custom';
		$args['post_status'] = 'publish';
		$args['tax_query'] = array(
			'tax_query' => array(
				'relationship' => 'AND',
				array(
					'taxonomy' => 'tribe_events_cat',
					'field' => 'slug',
					'terms' => array( 'radio-show' ),
					'operator' => 'IN'
				),
			),
		);
		
		return $args;
		
	}
	
	/**
	 * Modify the RBM FH Select Field Args for the P2P Metabox
	 * 
	 * @param		array  $args         Select Field Args
	 * @param		string $post_type    Current Post Type
	 * @param		string $relationship Connected-to Post Type
	 *                                                
	 * @access		public
	 * @since		1.0.0
	 * @return		array  Select Field Args
	 */
	public function p2p_select_args( $args, $post_type, $relationship ) {
		
		if ( $post_type == $this->post_type && 
			$relationship == $this->p2p ) {
			$args['multiple'] = true;
		}
		
		return $args;
		
	}
	
	/**
	 * Adds an Admin Column
	 * 
	 * @param		array $columns Array of Admin Columns
	 *                                       
	 * @access		public
	 * @since		1.0.0
	 * @return		array Modified Admin Column Array
	 */
	public function admin_column_add( $columns ) {
		
		$columns['radio-show'] = _x( 'Radio Show(s)', 'Radio Show Admin Column Label', 'gscr-cpt-on-air-personalities' );
		
		return $columns;
		
	}
	
	/**
	 * Displays data within Admin Columns
	 * 
	 * @param		string  $column  Admin Column ID
	 * @param		integer $post_id Post ID
	 *                               
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function admin_column_display( $column, $post_id ) {
		
		switch ( $column ) {
				
			case 'radio-show' :
				
				$connected_posts = rbm_cpts_get_p2p_parent( $this->p2p, $post_id );
				
				if ( ! is_array( $connected_posts ) ) $connected_posts = array( $connected_posts );
				
				echo '<ul style="margin-top: 0; list-style-type: disc; padding-left: 1.25em;">';
				foreach ( $connected_posts as $connected ) : if ( empty( $connected ) ) continue; ?>

					<li>
						<?php edit_post_link( get_the_title( $connected ), '', '', $connected ); ?>
					</li>
					
				<?php endforeach;
				echo '</ul>';
				
				break;
			case 'default' :
				echo rbm_field( $column, $post_id );
				break;
				
		}
		
	}
	
	/**
	 * Allow Pagination to work on On-Air Personalities for their Custom Radio Shows Query by disabling Canonical Redirects
	 * 
	 * @param		string $redirect_url Canonical Redirect URL
	 *                                                
	 * @access		public
	 * @since		1.0.0
	 * @return		string Canonical Redirect URL. False to not Redirect
	 */
	public function redirect_canonical( $redirect_url ) {
		
		if ( is_singular( $this->post_type ) ) {
			return false;
		}
		
		return $redirect_url;
	
	}
	
}