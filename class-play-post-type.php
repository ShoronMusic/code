<?php

defined( 'ABSPATH' ) || exit;

class Play_Post_Type {

		protected static $_instance = null;

		public static function instance() {
				if ( is_null( self::$_instance ) ) {
						self::$_instance = new self();
				}

				return self::$_instance;
		}

		public function __construct() {
				add_action( 'init', array( $this, 'register' ) );

				$terms = apply_filters( 'play_taxonomy_columns', [ 'genre', 'artist', 'featuring', 'collabo', 'released', 'origin', 'mood', 'activity', 'product_tag'] );

				foreach ( $terms as $key => $term ) {
						add_action( $term . '_add_form_fields', array( $this, 'edit_term_fields' ) );
						add_action( $term . '_edit_form_fields', array( $this, 'edit_term_fields' ) );
						add_filter( 'manage_edit-' . $term . '_columns', array( $this, 'term_columns' ), 10 );
						add_filter( 'manage_' . $term . '_custom_column', array( $this, 'term_column' ), 10, 3 );
				}

				add_action( 'created_term', array( $this, 'save_term_fields' ), 10, 3 );
				add_action( 'edit_term', array( $this, 'save_term_fields' ), 10, 3 );

				do_action( 'play_block_post_type_init', $this );
		}

		public function register() {
				// register station post type
				register_post_type(
						'station',
						apply_filters(
								'play_register_post_type_station',
								array(
										'labels'			 => $this->get_labels( 'Station', 'Stations' ),
										'public'			 => true,
										'has_archive'  => 'stations',
										'rewrite' 		 => array( 'slug' => 'station', 'with_front' => true ),
										'show_in_rest' => true,
										'supports'		 => array( 'title', 'editor', 'author', 'thumbnail', 'comments', 'custom-fields' ),
										'menu_icon' 	 => plugin_dir_url( dirname( __FILE__ ) ) . 'build/icon.station.svg'
								)
						)
				);

				register_taxonomy(
						'artist',
						apply_filters( 'play_register_post_taxonomy_artist_types', array( 'station', 'product' ) ),
						apply_filters(
								'play_register_post_taxonomy_artist',
								array(
										'labels'						=> $this->get_labels( 'Artist', 'Artists' ),
										'rewrite' 					=> array( 'slug' => 'artist' ),
										'show_in_rest'			=> true,
										'hierarchical'			=> false,
										'show_ui' 					=> true,
										'show_admin_column' => false,
										'capabilities'			=> array(
												'assign_terms' => 'read'
										),
										'sort'							=> true,
										'args'							=> array(
												'orderby' => 'term_order',
										)
								)
						)
				);

				register_taxonomy(
						'featuring',
						apply_filters( 'play_register_post_taxonomy_featuring_types', 'station' ),
						apply_filters(
								'play_register_post_taxonomy_station_featuring',
								array(
										'labels'						=> $this->get_labels( 'featuring' ),
										'rewrite' 					=> array( 'slug' => 'featuring' ),
										'show_in_rest'			=> true,
										'hierarchical'			=> false,
										'show_ui' 					=> true,
										'show_admin_column' => true
								)
						)
				);

				register_taxonomy(
						'collabo',
						apply_filters( 'play_register_post_taxonomy_collabo_types', 'station' ),
						apply_filters(
								'play_register_post_taxonomy_station_collabo',
								array(
										'labels'						=> $this->get_labels( 'collaboration' ),
										'rewrite' 					=> array( 'slug' => 'collabo' ),
										'show_in_rest'			=> true,
										'hierarchical'			=> false,
										'show_ui' 					=> true,
										'show_admin_column' => true
								)
						)
				);


				register_taxonomy(
						'origin',
						apply_filters( 'play_register_post_taxonomy_origin_types', 'station' ),
						apply_filters(
								'play_register_post_taxonomy_station_origin',
								array(
										'labels'						=> $this->get_labels( 'Origin' ),
										'rewrite' 					=> array( 'slug' => 'origin' ),
										'show_in_rest'			=> true,
										'hierarchical'			=> false,
										'show_ui' 					=> true,
										'show_admin_column' => true
								)
						)
				);


				register_taxonomy(
						'released',
						apply_filters( 'play_register_post_taxonomy_released_types', 'station' ),
						apply_filters(
								'play_register_post_taxonomy_station_released',
								array(
										'labels'						=> $this->get_labels( 'Released', 'Release' ),
										'rewrite' 					=> array( 'slug' => 'released' ),
										'show_in_rest'			=> true,
										'hierarchical'			=> false,
										'show_ui' 					=> true,
										'show_admin_column' => true
								)
						)
				);


				register_taxonomy(
						'vocal',
						apply_filters( 'play_register_post_taxonomy_vocal_types', 'station' ),
						apply_filters(
								'play_register_post_taxonomy_station_vocal',
								array(
										'labels'						=> $this->get_labels( 'vocal', 'vocals' ),
										'rewrite' 					=> array( 'slug' => 'vocal' ),
										'show_in_rest'			=> true,
										'hierarchical'			=> false,
										'show_ui' 					=> true,
										'show_admin_column' => true
								)
						)
				);


				register_taxonomy(
						'music',
						apply_filters( 'play_register_post_taxonomy_music_types', 'station' ),
						apply_filters(
								'play_register_post_taxonomy_station_music',
								array(
										'labels'						=> $this->get_labels( 'music', 'musics' ),
										'rewrite' 					=> array( 'slug' => 'music' ),
										'show_in_rest'			=> true,
										'hierarchical'			=> false,
										'show_ui' 					=> true,
										'show_admin_column' => true
								)
						)
				);

				register_taxonomy(
						'genre',
						apply_filters( 'play_register_post_taxonomy_genre_types', 'station' ),
						apply_filters(
								'play_register_post_taxonomy_genre',
								array(
										'labels'						=> $this->get_labels( 'Genre', 'Genres' ),
										'rewrite' 					=> array( 'slug' => 'genre' ),
										'show_in_rest'			=> true,
										'hierarchical'			=> true,
										'show_ui' 					=> true,
										'show_admin_column' => true,
										'capabilities'			=> array(
												'assign_terms' => 'read'
										)
								)
						)
				);

				register_taxonomy(
						'mood',
						apply_filters( 'play_register_post_taxonomy_mood_types', 'station' ),
						apply_filters(
								'play_register_post_taxonomy_station_mood',
								array(
										'labels'						=> $this->get_labels( 'Mood', 'Moods' ),
										'rewrite' 					=> array( 'slug' => 'mood' ),
										'show_in_rest'			=> true,
										'hierarchical'			=> false,
										'show_ui' 					=> true,
										'show_admin_column' => true
								)
						)
				);

				register_taxonomy(
						'activity',
						apply_filters( 'play_register_post_taxonomy_activity_types', 'station' ),
						apply_filters(
								'play_register_post_taxonomy_station_activity',
								array(
										'labels'						=> $this->get_labels( 'Activity', 'Activities' ),
										'rewrite' 					=> array( 'slug' => 'activity' ),
										'show_in_rest'			=> true,
										'hierarchical'			=> false,
										'show_ui' 					=> true,
										'show_admin_column' => true
								)
						)
				);

				register_taxonomy(
						'station_tag',
						apply_filters( 'play_register_post_taxonomy_station_tag_types', 'station' ),
						apply_filters(
								'play_register_post_taxonomy_station_tag',
								array(
										'labels'						=> $this->get_labels( 'Tag', 'Tags' ),
										'rewrite' 					=> array( 'slug' => 'station-tag' ),
										'show_in_rest'			=> true,
										'hierarchical'			=> false,
										'show_ui' 					=> true,
										'show_admin_column' => true,
										'capabilities'			=> array(
												'assign_terms' => 'read'
										)

								)
								
						)
				);
		}



		public function get_labels( $singular, $plural = '' ) {
				$locale = get_locale();
				if ( $plural == '' ) {
						$plural = $singular;
				}
				$labels = array(
						'name'											 => $plural,
						'singular_name' 						 => $singular,
						'search_items'							 => sprintf( __( 'Search %s' ), $plural ),
						'all_items' 								 => sprintf( __( 'All %s' ), $plural ),
						'parent_item' 							 => sprintf( __( 'Parent %s' ), $plural ),
						'parent_item_colon' 				 => sprintf( __( 'Parent %s:' ), $plural ),
						'edit_item' 								 => sprintf( __( 'Edit %s' ), $singular ),
						'update_item' 							 => sprintf( __( 'Update %s' ), $singular ),
						'add_new_item'							 => sprintf( __( 'Add New %s' ), $singular ),
						'add_new' 									 => __( 'Add New' ),
						'new_item'									 => sprintf( __( 'Add New %s' ), $singular ),
						'view_item' 								 => sprintf( __( 'View %s' ), $singular ),
						'popular_items' 						 => sprintf( __( 'Popular %s' ), $plural ),
						'new_item_name' 						 => sprintf( __( 'New %s Name' ), $singular ),
						'separate_items_with_commas' => sprintf( __( 'Separate %s with commas' ), $plural ),
						'add_or_remove_items' 			 => sprintf( __( 'Add or remove %s' ), $plural ),
						'choose_from_most_used' 		 => sprintf( __( 'Choose from the most used %s' ), $plural ),
						'not_found' 								 => sprintf( __( 'No %s found' ), $plural ),
						'not_found_in_trash'				 => sprintf( __( 'No %s found in trash' ), $plural ),
						'menu_name' 								 => $plural,
						'name_admin_bar'						 => $singular
				);

				return apply_filters( 'play_' . strtolower( $singular ) . '_labels_locale', $labels, $locale );
		}

		public function edit_term_fields( $term ) {
				wp_enqueue_media();
				$thumbnail_id = 0;
				if ( isset( $term->term_id ) ) {
						$thumbnail_id = absint( get_term_meta( $term->term_id, 'thumbnail_id', true ) );
				}
				$wrap 	= '<div class="form-field term-thumbnail-wrap"><label>Thumbnail</label>%s</div>';
				$el 		= '<img src="%s" width="60px" height="60px" style="background: #fff;"><input type="hidden" name="thumbnail_id" value="' . $thumbnail_id . '"><button type="button" class="button upload-btn">Upload</button>';
				$holder = 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
				$image	= $holder;
				if ( isset( $term->term_id ) ) {
						$wrap = '<tr class="form-field term-thumbnail-wrap"><th scope="row" valign="top"><label>Thumbnail</label></th><td>%s</td></tr>';
						if ( $thumbnail_id ) {
								$image = wp_get_attachment_thumb_url( $thumbnail_id );
						}
				}
				echo sprintf( $wrap, sprintf( $el, $image ) );
				?>
				<script type="text/javascript">
						(function ($) {
								'use strict';
								var media;
								$(document).on('click', '.upload-btn', function (e) {
										e.preventDefault();
										if (media) {
												media.open();
												return;
										}
										var that = $(this);
										media = wp.media({title: 'Choose an image', multiple: false})
												.open()
												.on('select', function (e) {
														var obj = media.state().get('selection').first().toJSON();
														var thumbnail = obj.sizes.thumbnail || obj.sizes.full;
														that.siblings('img').attr('src', thumbnail.url);
														that.prev().val(obj.id);
												});
								});
								$(document).ajaxComplete(function (event, request, options) {
										$('div.term-thumbnail-wrap img').attr('src', '<?php echo $holder; ?>');
								});
						})(jQuery);
				</script>
				<?php
		}

		public function save_term_fields( $term_id, $tt_id = '', $taxonomy = '' ) {
				if ( isset( $_POST[ 'thumbnail_id' ] ) ) {
						update_term_meta( $term_id, 'thumbnail_id', absint( $_POST[ 'thumbnail_id' ] ) );
				}
		}

		public function term_columns( $columns ) {
				$new = array();
				foreach ( $columns as $key => $title ) {
						if ( $key == 'description' ) {
								$new[ 'thumb' ] = 'Thumbnail';
						}
						$new[ $key ] = $title;
				}

				return $new;
		}

		public function term_column( $columns, $column, $id ) {
				if ( 'thumb' === $column ) {
						$thumbnail_id = (int) get_term_meta( $id, 'thumbnail_id', true );
						$image				= '';
						if ( $thumbnail_id ) {
								$thumb = wp_get_attachment_thumb_url( $thumbnail_id );
								$image = '<img src="' . esc_url( $thumb ) . '" class="wp-post-image" height="48" width="48" />';
						}
						$columns .= $image;
				}

				return $columns;
		}

}


Play_Post_Type::instance();





// シングルページのメタ情報表示
// add_action( 'wp_head', 'my_spotify_plugin_display_spotify_track_id' );
// function my_spotify_plugin_display_spotify_track_id() {
// 		if ( is_singular( 'station' ) ) { // 'station' 投稿タイプであることを確認
// 				$spotify_track_id = get_post_meta( get_the_ID(), 'spotify_track_id', true );
// 				if ( $spotify_track_id ) {
// 						echo '<meta name="spotify:track:id" content="' . esc_attr( $spotify_track_id ) . '">';
// 				}
// 		}
// }


// 投稿のシングルページでSpotifyのトラックIDを表示
add_action( 'wp_head', 'my_spotify_plugin_display_spotify_track_id' );
function my_spotify_plugin_display_spotify_track_id() {
	if ( is_singular( 'post' ) ) { // 'post' 投稿タイプであることを確認
		$spotify_track_id = get_post_meta( get_the_ID(), 'spotify_track_id', true );
		if ( $spotify_track_id ) {
			echo '<meta name="spotify:track:id" content="' . esc_attr( $spotify_track_id ) . '">';
		}
	}
}


// station投稿の保存時にフックする処理 20230508
function my_spotify_plugin_save_track_info( $post_id, $post ) {
	// カスタム投稿タイプ「station」以外は処理を終了する
	if ( $post->post_type !== 'station' ) {
		return;
	}

	// 「Spotify Track ID」を取得する
	$spotify_track_id = get_post_meta( $post_id, 'spotify_track_id', true );

	// 「Spotify Track ID」が存在しなければ処理を終了する
	if ( ! $spotify_track_id ) {
		return;
	}

	// Spotify APIにアクセスするためのURLを作成する
	$api_url = 'https://api.spotify.com/v1/tracks/' . $spotify_track_id;

	// Spotify APIにアクセスするためのトークンを取得する
	$access_token = my_spotify_plugin_get_access_token();

	// リクエストヘッダーを設定する
	$headers = array(
		'Authorization: Bearer ' . $access_token,
		'Content-Type: application/json',
	);

	// リクエストを送信する
	$response = wp_remote_get(
		$api_url,
		array(
			'headers' => $headers,
			'timeout' => 30,
		)
	);

	// レスポンスのボディを取得する
	$body = wp_remote_retrieve_body( $response );

	// レスポンスのボディが空でなければJSONをデコードする
	if ( ! empty( $body ) ) {
		$track_info = json_decode( $body );

		// カスタムフィールド「artist_name」「track_title」「album_title」「release_date」に曲情報を保存する
		update_post_meta( $post_id, 'artist_name', esc_html( $track_info->artists[0]->name ) );
		update_post_meta( $post_id, 'track_title', esc_html( $track_info->name ) );
		update_post_meta( $post_id, 'album_title', esc_html( $track_info->album->name ) );
		update_post_meta( $post_id, 'release_date', esc_html( $track_info->album->release_date ) );
	}
}
add_action( 'save_post', 'my_spotify_plugin_save_track_info' );
