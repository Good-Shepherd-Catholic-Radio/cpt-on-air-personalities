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
		'has_archive' => true,
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
		
		add_action( 'add_meta_boxes', array( $this, 'add_meta_boxes' ) );
		
		add_filter( 'rbm_cpts_p2p_select_args', array( $this, 'p2p_select_args' ), 10, 3 );
		
		add_filter( 'manage_' . $this->post_type . '_posts_columns', array( $this, 'admin_column_add' ) );
		
		add_action( 'manage_' . $this->post_type . '_posts_custom_column', array( $this, 'admin_column_display' ), 10, 2 );
		
	}
	
	/**
	 * Add Meta Box
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function add_meta_boxes() {
		
		add_meta_box(
			'ebook-download-url',
			sprintf( _x( '%s Meta', 'Metabox Title', 'gscr-cpt-on-air-personalities' ), $this->label_singular ),
			array( $this, 'metabox_content' ),
			$this->post_type,
			'normal'
		);
		
	}
	
	/**
	 * Add Meta Field
	 * 
	 * @access		public
	 * @since		1.0.0
	 * @return		void
	 */
	public function metabox_content() {
		
		rbm_do_field_text(
			'ebook_download_url',
			_x( 'Download URL', 'Download URL Label', 'gscr-cpt-on-air-personalities' ),
			false,
			array(
				'description' => __( 'The URL to download this asset, or the landing page URL.', 'gscr-cpt-on-air-personalities' ),
			)
		);
		
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
		
		if ( $post_type == 'on-air-personality' && 
			$relationship == 'radio-show' ) {
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
				
				$connected_posts = rbm_cpts_get_p2p_parent( $column, $post_id );
				
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
	
}