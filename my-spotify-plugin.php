<?php
/*
Plugin Name: My Spotify Plugin
Plugin URI: https://example.com/
Description: A plugin to get information from Spotify API and save to custom fields.
Version: 1.0
Author: Your Name
Author URI: https://example.com/
*/

// SpotifyAPI情報を扱うクラスを定義したファイルを読み込む
require_once( plugin_dir_path( __FILE__ ) . 'includes/spotify-api.php' );

// 「SPOTIFY」ボタンを追加する
function my_spotify_plugin_add_button() {
		// カスタム投稿タイプ「station」にのみボタンを表示する
		global $post_type;
		if ( 'station' !== $post_type ) {
				return;
		}
		?>
		<div id="my-spotify-plugin-container">
				<button id="my-spotify-plugin-button" class="button button-primary">
						<?php esc_html_e( 'SPOTIFY', 'my-spotify-plugin' ); ?>
				</button>
				<div id="my-spotify-plugin-loader" class="spinner"></div>
		</div>
		<?php
}
add_action( 'post_submitbox_misc_actions', 'my_spotify_plugin_add_button' );

// 「SPOTIFY」ボタンがクリックされたときのJavaScriptを追加する
function my_spotify_plugin_add_script() {
		// カスタム投稿タイプ「station」にのみスクリプトを追加する
		global $post_type;
		if ( 'station' !== $post_type ) {
				return;
		}
		?>
		<script>
		jQuery(document).ready(function($) {
				// 「SPOTIFY」ボタンがクリックされたときの処理
				$('#my-spotify-plugin-button').on('click', function(e) {
						e.preventDefault();
						var $this = $(this);
						var $container = $('#my-spotify-plugin-container');
						var $loader = $('#my-spotify-plugin-loader');

						// ボタンを無効化する
						$this.prop('disabled', true);

						// ローダーを表示する
						$loader.addClass('is-active');

						// SpotifyAPIから曲情報を取得する
						var url = '<?php echo esc_url_raw( rest_url( 'my-spotify-plugin/v1/track-info' ) ); ?>';
						var track_id = $('#spotify_track_id').val();
						var data = {
								'track_id': track_id,
						};
						$.ajax({
								url: url,
								method: 'POST',
								beforeSend: function(xhr) {
										xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ); ?>');
								},
								data: data,
						}).done(function(response) {
								// カスタムフィールドに曲情報を保存する
								$('#artist_name').val(response.artist_name);
								$('#track_title').val(response.track_title);
								$('#album_title').val(response.album_title);
								$('#release_date').val(response.release_date);

								// ローダーを非表示にする
								$loader.removeClass('is-active');

								// ボタンを有効化する
$this.prop('disabled', false);

// ボタンがクリックされた時の処理
$this.click(function() {
// スピナーを表示する
$spinner.show();


// code02
// Spotify APIからトラック情報を取得する
var access_token = '<?php echo esc_html( my_spotify_plugin_get_access_token() ); ?>';
var track_id = '<?php echo esc_html( get_post_meta( $post->ID, 'spotify_track_id', true ) ); ?>';
var api_url = 'https://api.spotify.com/v1/tracks/' + track_id;
var headers = {
'Authorization': 'Bearer ' + access_token,
'Content-Type': 'application/json'
};
$.ajax({
url: api_url,
type: 'GET',
headers: headers,
dataType: 'json',
success: function(response) {
// 取得したトラック情報をカスタムフィールドに保存する
$.ajax({
url: '<?php echo esc_url( rest_url( 'my-spotify-plugin/v1/station/' . $post->ID ) ); ?>',
type: 'POST',
data: {
'artist_name': response.artists[0].name,
'track_title': response.name,
'album_title': response.album.name,
'release_date': response.album.release_date
},
beforeSend: function(xhr) {
xhr.setRequestHeader('X-WP-Nonce', '<?php echo wp_create_nonce( 'wp_rest' ); ?>');
},
success: function() {
// カスタムフィールドを更新するために、投稿を保存する
$('#publish').click();
},
error: function() {
alert('曲情報を保存できませんでした。');
},
complete: function() {
// スピナーを非表示にする
$spinner.hide();
}
});
},
error: function() {
alert('トラック情報を取得できませんでした。');
// スピナーを非表示にする
$spinner.hide();
}
});
});

})(jQuery);
</script>

<?php
}
add_action('admin_footer', 'my_spotify_plugin_add_button');


// code03
// 「SPOTIFY」ボタンがクリックされた時のイベントハンドラー
function my_spotify_plugin_button_click() {
// nonceのチェック
check_ajax_referer( 'my_spotify_plugin', 'security' );

// カスタムフィールドの値を取得する
$station_id = intval( $_POST['station_id'] );
$spotify_track_id = get_post_meta( $station_id, 'spotify_track_id', true );

// 「Spotify Track ID」が存在する場合は、曲情報を取得して保存する
if ( $spotify_track_id ) {
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
		update_post_meta( $station_id, 'artist_name', esc_html( $track_info->artists[0]->name ) );
		update_post_meta( $station_id, 'track_title', esc_html( $track_info->name ) );
		update_post_meta( $station_id, 'album_title', esc_html( $track_info->album->name ) );
		update_post_meta( $station_id, 'release_date', esc_html( $track_info->album->release_date ) );

		// レスポンスを返す
		wp_send_json_success( array(
			'artist_name' => $track_info->artists[0]->name,
			'track_title' => $track_info->name,
			'album_title' => $track_info->album->name,
			'release_date' => $track_info->album->release_date,
		) );
	}
}


// code04
// 「Spotify Track ID」が存在しない場合は、エラーレスポンスを返す
wp_send_json_error( __( 'Spotify Track ID not found', 'my-spotify-plugin' ) );
}
add_action( 'wp_ajax_my_spotify_plugin_button_click', 'my_spotify_plugin_button_click' );



// カスタム投稿タイプ「station」の場合のみ、カスタムフィールドを表示する
if ( $post->post_type === 'station' ) {
		// カスタムフィールドに保存された値を取得する
		$spotify_track_id = get_post_meta( $post->ID, 'spotify_track_id', true );
		$artist_name = get_post_meta( $post->ID, 'artist_name', true );
		$track_title = get_post_meta( $post->ID, 'track_title', true );
		$album_title = get_post_meta( $post->ID, 'album_title', true );
		$release_date = get_post_meta( $post->ID, 'release_date', true );

		// カスタムフィールドのHTMLを出力する
		?>
		<div class="misc-pub-section misc-pub-spotify">
				<span class="spotify-label">Spotify Track ID:</span>
				<span class="spotify-value"><?php echo esc_html( $spotify_track_id ); ?></span>
				<br>
				<span class="spotify-label">Artist:</span>
				<span class="spotify-value"><?php echo esc_html( $artist_name ); ?></span>
				<br>
				<span class="spotify-label">Track Title:</span>
				<span class="spotify-value"><?php echo esc_html( $track_title ); ?></span>
				<br>
				<span class="spotify-label">Album Title:</span>
				<span class="spotify-value"><?php echo esc_html( $album_title ); ?></span>
				<br>
				<span class="spotify-label">Release Date:</span>
				<span class="spotify-value"><?php echo esc_html( $release_date ); ?></span>
		</div>
		<?php
}
add_action( 'post_submitbox_misc_actions', 'my_spotify_plugin_add_custom_field' );


// code05
// プラグイン用のCSS,JavaScriptを読み込む
function my_spotify_plugin_enqueue_scripts( $hook_suffix ) {
    global $post_type;
    if ( ( $post_type == 'station' ) && ( in_array( $hook_suffix, array( 'post.php', 'post-new.php' ) ) ) ) {
        wp_enqueue_style( 'my-spotify-plugin-style', plugin_dir_url( __FILE__ ) . 'my-spotify-plugin.css' );
        wp_enqueue_script(
            'my-spotify-plugin',
            plugin_dir_url( __FILE__ ) . 'js/my-spotify-plugin.js',
            array( 'jquery' ),
            filemtime( plugin_dir_path( __FILE__ ) . 'js/my-spotify-plugin.js' ),
            true
        );
        wp_enqueue_script( 'wp-api' );
    }
}
add_action( 'admin_enqueue_scripts', 'my_spotify_plugin_enqueue_scripts' );


// 06
/**
 * カスタムフィールドの値を更新する
 *
 * @param int $post_id 投稿ID
 */
function my_spotify_plugin_update_custom_field( $post_id ) {
	// nonceチェック
	if ( ! isset( $_POST['my_spotify_plugin_custom_field_nonce'] ) ||
			 ! wp_verify_nonce( $_POST['my_spotify_plugin_custom_field_nonce'], 'my_spotify_plugin_custom_field' )
	) {
		return;
	}

	// 自動保存時には処理を終了する
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// ユーザーに投稿を編集する権限があるかチェックする
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// カスタムフィールドの値を更新する
	if ( isset( $_POST['my_spotify_plugin_track_info'] ) ) {
		$track_info = json_decode( stripslashes( $_POST['my_spotify_plugin_track_info'] ) );

		update_post_meta( $post_id, 'artist_name', esc_html( $track_info->artist_name ) );
		update_post_meta( $post_id, 'track_title', esc_html( $track_info->track_title ) );
		update_post_meta( $post_id, 'album_title', esc_html( $track_info->album_title ) );
		update_post_meta( $post_id, 'release_date', esc_html( $track_info->release_date ) );
	}
}
add_action( 'wp_ajax_my_spotify_plugin_save_track_info', 'my_spotify_plugin_update_custom_field' );
add_action( 'wp_ajax_nopriv_my_spotify_plugin_save_track_info', 'my_spotify_plugin_update_custom_field' );


// code07
// POSTリクエストが送信された場合のみ処理を実行する
if ( ! isset( $_POST['my_spotify_plugin_nonce'] ) || ! wp_verify_nonce( $_POST['my_spotify_plugin_nonce'], 'my_spotify_plugin_update_custom_field' ) ) {
	return;
}

// カスタムフィールド「spotify_track_id」の値を取得する
$spotify_track_id = sanitize_text_field( $_POST['spotify_track_id'] );

// カスタムフィールド「artist_name」の値を取得する
$artist_name = sanitize_text_field( $_POST['artist_name'] );

// カスタムフィールド「track_title」の値を取得する
$track_title = sanitize_text_field( $_POST['track_title'] );

// カスタムフィールド「album_title」の値を取得する
$album_title = sanitize_text_field( $_POST['album_title'] );

// カスタムフィールド「release_date」の値を取得する
$release_date = sanitize_text_field( $_POST['release_date'] );

// カスタムフィールドに値を保存する
update_post_meta( $post_ID, 'spotify_track_id', $spotify_track_id );
update_post_meta( $post_ID, 'artist_name', $artist_name );
update_post_meta( $post_ID, 'track_title', $track_title );
update_post_meta( $post_ID, 'album_title', $album_title );
update_post_meta( $post_ID, 'release_date', $release_date );

add_action( 'save_post', 'my_spotify_plugin_update_custom_field' );


// code08
// REST APIエンドポイントを登録する
function my_spotify_plugin_register_rest_route() {
    // エンドポイントのURLを定義する
    $namespace = 'my-spotify-plugin/v1';
    $route1 = '/station/(?P<id>\d+)/track-info';
    $route2 = '/save-track-info';
    $url1 = '/' . $namespace . $route1;
    $url2 = '/' . $namespace . $route2;

    // エンドポイントのコールバック関数を定義する
    register_rest_route(
        $namespace,
        $route1,
        array(
            'methods'  => 'POST',
            'callback' => 'my_spotify_plugin_update_custom_field',
            'args'     => array(
                'id' => array(
                    'validate_callback' => 'is_numeric',
                ),
            ),
        )
    );

    register_rest_route(
        $namespace,
        $route2,
        array(
            'methods' => 'POST',
            'callback' => 'my_spotify_plugin_save_track_info',
            'permission_callback' => function () {
                return current_user_can( 'edit_posts' );
            },
        )
    );
}

add_action( 'rest_api_init', 'my_spotify_plugin_register_rest_route' );


// code09
function my_spotify_plugin_register_post_type() {
		$labels = array(
				'name'							 => 'ステーション',
				'singular_name' 		 => 'ステーション',
				// 省略
		);

		$args = array(
				'labels'						 => $labels,
				'public'						 => true,
				'show_ui' 					 => true,
				'show_in_menu'			 => true,
				'query_var' 				 => true,
				'rewrite' 					 => array( 'slug' => 'station' ),
				'capability_type' 	 => 'post',
				'has_archive' 			 => true,
				'hierarchical'			 => false,
				'menu_position' 		 => null,
				'supports'					 => array( 'title', 'editor', 'thumbnail', 'custom-fields' ),
		);

		register_post_type( 'station', $args );
}
add_action( 'init', 'my_spotify_plugin_register_post_type' );


// code10
function my_spotify_plugin_add_meta_box() {
    add_meta_box(
        'my_spotify_plugin_track_info',
        'Spotify Track Info',
        'my_spotify_plugin_track_info_meta_box',
        'station',
        'normal',
        'high'
    );
}
add_action( 'add_meta_boxes_station', 'my_spotify_plugin_add_meta_box' );


