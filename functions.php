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
	
	/* Custom.js 추가 연결 */
	add_action( 'wp_enqueue_scripts', 'my_custom_scripts' );
	function my_custom_scripts() {
		wp_enqueue_script( 'custom-js', get_stylesheet_directory_uri() . '/js/custom.js', array( 'jquery' ),'',true );
	}
	
	/* SVG 사용 */
	add_filter( 'mime_types', 'custom_upload_mimes' );
	function custom_upload_mimes( $existing_mimes ) {
		$existing_mimes['svg'] = 'image/svg+xml';
		return $existing_mimes;
	}

	/* 메뉴 숏코드 사용 [menu name="menu_name"] */
	add_shortcode('menu', 'print_menu_shortcode');
	function print_menu_shortcode($atts, $content = null) {
		extract(shortcode_atts(array( 'name' => null, 'class' => null ), $atts));
		return wp_nav_menu( array( 'menu' => $name, 'menu_class' => 'myMenuClass', 'echo' => false ) );
	}

?>

<?php // 페이지에 글 태그 포함

	/* 페이지에 태그 및 카테고리 지원 추가 */
	add_action('init', 'tags_categories_support_all');
	function tags_categories_support_all(){
		register_taxonomy_for_object_type('post_tag', 'page');
		//register_taxonomy_for_object_type('category', 'page');  
	}
	
	/* 모든 태그와 카테고리가 검색어에 포함되었는지 확인 */
	add_action('pre_get_posts', 'tags_categories_support_query');
	function tags_categories_support_query($wp_query){
		if ($wp_query->get('tag')) $wp_query->set('post_type', 'any');
		//if ($wp_query->get('category_name')) $wp_query->set('post_type', 'any');
	}
	
?>

<?php // 페이지 카테고리 추가 및 필터
		
	/* 페이지 포스트 타입에 '페이지 카테고리' 추가 */
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
	
	/* 관리자에 사용자 지정 분류 드롭 다운 표시 */
	add_action('restrict_manage_posts', 'post_filter_custom_post_type_by_taxonomy');
	function post_filter_custom_post_type_by_taxonomy() {
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

	/* 관리자에서 분류별로 게시물 필터링 */
	add_filter('parse_query', 'post_convert_id_to_term_in_query');
	function post_convert_id_to_term_in_query($query) {
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