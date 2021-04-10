<?php 

	add_action( 'wp_enqueue_scripts', 'wptalk_enqueue_styles' );
	function wptalk_enqueue_styles() {
		$parent_style = 'parent-style';
		wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
		wp_enqueue_style( 'child-style',
		get_stylesheet_directory_uri() . '/style.css',
		array( $parent_style ),
			wp_get_theme()->get('Version')
		);
	}
	
	// Custom.js 추가 연결
	add_action( 'wp_enqueue_scripts', 'my_custom_scripts' );
	function my_custom_scripts() {
		wp_enqueue_script( 'custom-js', get_stylesheet_directory_uri() . '/js/custom.js', array( 'jquery' ),'',true );
	}
	
	// SVG 사용
	add_filter( 'mime_types', 'custom_upload_mimes' );
	function custom_upload_mimes( $existing_mimes ) {
		$existing_mimes['svg'] = 'image/svg+xml';
		return $existing_mimes;
	}

	// 메뉴 숏코드 사용 [menu name="menu_name"]
	add_shortcode('menu', 'print_menu_shortcode');
	function print_menu_shortcode($atts, $content = null) {
		extract(shortcode_atts(array( 'name' => null, 'class' => null ), $atts));
		return wp_nav_menu( array( 'menu' => $name, 'menu_class' => 'myMenuClass', 'echo' => false ) );
	}

?>

<?php // 페이지 카테고리 추가 및 필터

	// 페이지 포스트 타입에 '페이지 카테고리' 추가
	add_action( 'init', 'custom_taxonomies_with_page', 0 );
	function custom_taxonomies_with_page() {
		// page-category
		register_taxonomy( 'page-category', array( 'page' ), array(
			'labels' => array(
				'name' => '페이지 카테고리',
				'label' => '페이지 카테고리',
				'menu_name' => '카테고리',
			),
			'hierarchical' => true, // Default: false
			'show_admin_column' => true, // Default: false
			'show_in_rest' => true,
		) );
	}
	
	// 관리자에 사용자 지정 분류 드롭 다운 표시
	add_action('restrict_manage_posts', 'page_filter_custom_post_type_by_taxonomy');
	function page_filter_custom_post_type_by_taxonomy() {
		global $typenow;
		$post_type = 'page'; 			// 게시물 유형 변경
		$taxonomy  = 'page-category'; 	// 분류법 변경
		if ($typenow == $post_type) {
			$selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
			$info_taxonomy = get_taxonomy($taxonomy);
			wp_dropdown_categories(array(
				'show_option_all' => sprintf( __( '모든 %s', 'textdomain' ), $info_taxonomy->label ),
				'taxonomy'        => $taxonomy,
				'name'            => $taxonomy,
				'orderby'         => 'name',
				'selected'        => $selected,
				'show_count'      => true,
				'hide_empty'      => true,
			));
		};
	}

	// 관리자에서 분류별로 게시물 필터링
	add_filter('parse_query', 'page_convert_id_to_term_in_query');
	function page_convert_id_to_term_in_query($query) {
		global $pagenow;
		$post_type = 'page';			// 게시물 유형 변경
		$taxonomy  = 'page-category'; 	// 분류법 변경
		$q_vars    = &$query->query_vars;
		if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
			$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
			$q_vars[$taxonomy] = $term->slug;
		}
	}

?>

<?php // 카테고리 카운트 숏코드

	// 카테고리 카운트
	add_action('template_redirect', 'category_count');
	function category_count(){		
		global $wp_query;
		global $current_count;
		
		$current_object = $wp_query->queried_object;

		if( array_key_exists('queried_object', $wp_query) ){			
			if(  $current_object != NULL ){
			
				if( array_key_exists('count', $current_object) ){
					$current_count = $wp_query->queried_object->count;
				}
				
				if( array_key_exists('taxonomy', $current_object) ){
					$current_taxonomy = $wp_query->queried_object->taxonomy;
				}
			
			}	
		}
	}
		
	// 카운트 숏코드 사용
	add_shortcode('category_count', 'print_category_count');
	function print_category_count( $atts, $content = null ) {
		global $current_count;
		if( $current_count == '' ){
			return false;
		} else {
			return "<span class='current-count'>$current_count</span>";
		}
	}

?>

<?php // '작업' 포스트 타입 생성

	// 포스트 타입 생성
	add_action( 'init', 'add_artwork_post_type_fn' );
	function add_artwork_post_type_fn() {
	
		$type_artwork_labels = array(
			'name'               => '작업',
			'all_items'     	 => '모든 작업',
			'add_new'            => '작업 생성',
			'add_new_item'       => '작업 생성',
			'edit_item'          => '작업 수정',
			'search_items'       => '작업 검색',
			'not_found'          => '작업이 없습니다.',
			'not_found_in_trash' => '휴지통에 작업이 없습니다.',
			'menu_name' 		 => '작업',
		);
		$type_board_args = array(
			'labels'        		=> $type_artwork_labels,
			'description'   		=> '작업 데이터 보관',
			'public'        		=> true,
			'publicly_queryable'	=> true,
			'hierarchical'			=> true,
			'menu_position' 		=> 5,
			'supports'      		=> array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments', 'custom-fields'),
			'has_archive'   		=> true,
			// 'show_ui'			=> false,
			// 'show_in_menu'		=> false,
		);
	
		// 포스트 타입 생성
		register_post_type( 'artwork', $type_board_args );
	
	}
	
	// 페이지 포스트 타입에 '페이지 카테고리' 추가
	add_action( 'init', 'custom_taxonomies_with_artwork', 0 );
	function custom_taxonomies_with_artwork() {
		// artwork-category
		register_taxonomy( 'artwork-category', array( 'artwork' ), array(
			'labels' => array(
				'name' => '작업 카테고리',
				'label' => '작업 카테고리',
				'menu_name' => '카테고리',
			),
			'hierarchical' => true, // Default: false
			'show_admin_column' => true, // Default: false
			'show_in_rest' => true,
		) );
	}
	
	// 관리자에 사용자 지정 분류 드롭 다운 표시
	add_action('restrict_manage_posts', 'artwork_filter_custom_post_type_by_taxonomy');
	function artwork_filter_custom_post_type_by_taxonomy() {
		global $typenow;
		$post_type = 'artwork'; 			// 게시물 유형 변경
		$taxonomy  = 'artwork-category'; 	// 분류법 변경
		if ($typenow == $post_type) {
			$selected      = isset($_GET[$taxonomy]) ? $_GET[$taxonomy] : '';
			$info_taxonomy = get_taxonomy($taxonomy);
			wp_dropdown_categories(array(
				'show_option_all' => sprintf( __( '모든 %s', 'textdomain' ), $info_taxonomy->label ),
				'taxonomy'        => $taxonomy,
				'name'            => $taxonomy,
				'orderby'         => 'name',
				'selected'        => $selected,
				'show_count'      => true,
				'hide_empty'      => true,
			));
		};
	}
	
	// 관리자에서 분류별로 게시물 필터링
	add_filter('parse_query', 'artwork_convert_id_to_term_in_query');
	function artwork_convert_id_to_term_in_query($query) {
		global $pagenow;
		$post_type = 'artwork';				// 게시물 유형 변경
		$taxonomy  = 'artwork-category'; 	// 분류법 변경
		$q_vars    = &$query->query_vars;
		if ( $pagenow == 'edit.php' && isset($q_vars['post_type']) && $q_vars['post_type'] == $post_type && isset($q_vars[$taxonomy]) && is_numeric($q_vars[$taxonomy]) && $q_vars[$taxonomy] != 0 ) {
			$term = get_term_by('id', $q_vars[$taxonomy], $taxonomy);
			$q_vars[$taxonomy] = $term->slug;
		}
	}

?>

<?php // Divi 모듈에서 커스텀 포스트 타입 검색
	
	// Divi 모듈에서 커스텀 포스트 타입 검색
	add_action( 'wp_loaded', 'custom_remove_default_et_pb_custom_search' );
	function custom_remove_default_et_pb_custom_search(){
		remove_action( 'pre_get_posts', 'et_pb_custom_search' );
		add_action( 'pre_get_posts', 'custom_et_pb_custom_search' );
	}
	function custom_et_pb_custom_search( $query = false ){
		if ( is_admin() || ! is_a( $query, 'WP_Query' ) || ! $query->is_search ) {
			return;
		}
		if( isset( $_GET['et_pb_searchform_submit'] ) ){
			$postTypes = array();
			if( ! isset($_GET['et_pb_include_posts'] ) && ! isset( $_GET['et_pb_include_pages'] ) ){
				$postTypes = array( 'post' );
			}
			if( isset( $_GET['et_pb_include_pages'] ) ){
				$postTypes = array( 'page' );
			}
			if( isset( $_GET['et_pb_include_posts'] ) ){
				$postTypes[] = 'post';
			}
			/* BEGIN Add custom post types */
			$postTypes[] = 'project';
			$postTypes[] = 'artwork';
			/* END Add custom post types */
			$query->set( 'post_type', $postTypes );
			if( ! empty( $_GET['et_pb_search_cat'] ) ){
				$categories_array = explode( ',', $_GET['et_pb_search_cat'] );
				$query->set( 'category__not_in', $categories_array );
			}
			if( isset( $_GET['et-posts-count'] ) ){
				$query->set( 'posts_per_page', (int) $_GET['et-posts-count'] );
			}
		}
	}

?>