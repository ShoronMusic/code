<?php
/*
Plugin Name: My Spotify Plugin
Plugin URI: https://example.com/
Description: Spotify APIから曲情報を取得し、WordPressのカスタム投稿に付加情報を追加するプラグイン
Version: 1.0.0
Author: Your Name
Author URI: https://example.com/
License: GPLv2 or later
Text Domain: my-spotify-plugin
*/

// ここからプラグインの実装

require_once( plugin_dir_path( __FILE__ ) . 'includes/spotify-api.php' );

// STEP02: 管理画面にSpotify APIの認証を行うための入力フォーム
// Render the settings page
function my_spotify_plugin_settings_page_callback() {
	$options = get_option( 'my_spotify_plugin_settings' );
	?>
	<div class="wrap">
		<h1><?php _e( 'My Spotify Plugin Settings', 'my_spotify_plugin' ); ?></h1>
		<form method="post" action="options.php">
			<?php settings_fields( 'my_spotify_plugin_settings' ); ?>
			<?php do_settings_sections( 'my_spotify_plugin_settings' ); ?>
			<table class="form-table">
				<tr>burokku
					<th scope="row"><?php _e( 'Client ID', 'my_spotify_plugin' ); ?></th>
					<td><input type="text" name="my_spotify_plugin_client_id" value="<?php echo esc_attr( $options['my_spotify_plugin_client_id'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Client Secret', 'my_spotify_plugin' ); ?></th>
					<td><input type="text" name="my_spotify_plugin_client_secret" value="<?php echo esc_attr( $options['my_spotify_plugin_client_secret'] ); ?>" /></td>
				</tr>
				<tr>
					<th scope="row"><?php _e( 'Refresh Token', 'my_spotify_plugin' ); ?></th>
					<td><input type="text" id="my_spotify_plugin_refresh_token" name="my_spotify_plugin_refresh_token" value="<?php echo esc_attr( get_option( 'my_spotify_plugin_refresh_token' ) ); ?>"></td>
				</tr>
			</table>
			<?php submit_button(); ?>
		</form>
	</div>
	<?php
}



// Add the settings page
function my_spotify_plugin_settings_page() {
		add_submenu_page(
				'options-general.php',
				__( 'My Spotify Plugin Settings', 'my_spotify_plugin' ),
				__( 'My Spotify Plugin', 'my_spotify_plugin' ),
				'manage_options',
				'my_spotify_plugin_settings',
				'my_spotify_plugin_settings_page_callback'
		);
}




// Render the settings page
// Render the settings page
function my_spotify_plugin_render_settings_page() {
	?>
	<div class="wrap">
		<h2><?php _e( 'My Spotify Plugin Settings', 'my_spotify_plugin' ); ?></h2>
		<form method="post" action="options.php">
			<?php settings_fields( 'my_spotify_plugin_settings' ); ?>
			<table class="form-table">
				<tbody>
					<tr>
						<th scope="row"><?php _e( 'Client ID', 'my_spotify_plugin' ); ?></th>
						<td>
							<input type="text" name="my_spotify_plugin_client_id" value="<?php echo esc_attr( get_option( 'my_spotify_plugin_client_id' ) ); ?>" />

						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Client Secret', 'my_spotify_plugin' ); ?></th>
						<td>
							<input type="text" name="my_spotify_plugin_client_secret" value="<?php echo esc_attr( get_option( 'my_spotify_plugin_client_secret' ) ); ?>" />

						</td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Refresh Token', 'my_spotify_plugin' ); ?></th>
						<td>
							<input type="text" name="my_spotify_plugin_settings[refresh_token]" value="<?php echo isset( $refresh_token ) ? esc_attr( $refresh_token ) : ''; ?>" />
						</td>
					</tr>
				</tbody>
			</table>
			<?php submit_button( __( 'Save Settings', 'my_spotify_plugin' ) ); ?>
		</form>
	</div>
	<?php
}
add_action( 'admin_menu', 'my_spotify_plugin_add_options_page' );
function my_spotify_plugin_add_options_page() {
		add_options_page(
				'My Spotify Plugin Settings',
				'My Spotify Plugin',
				'manage_options',
				'my_spotify_plugin_settings',
				'my_spotify_plugin_render_settings_page'
		);
}

function my_spotify_plugin_register_settings() {
	register_setting( 'my_spotify_plugin_settings', 'my_spotify_plugin_settings' );
}

add_action( 'admin_init', 'my_spotify_plugin_register_settings' );






//STEP03: 曲情報取得フォームの表示と、入力値の処理

// Display the form for retrieving Spotify track information
function my_spotify_plugin_display_form() {
	?>
	<div class="wrap">
		<h1><?php _e( 'Get Spotify Track Information', 'my_spotify_plugin' ); ?></h1>
		<form method="post" action="">
			<label for="spotify_track_id"><?php _e( 'Spotify Track ID', 'my_spotify_plugin' ); ?>:</label>
			<input type="text" name="spotify_track_id" id="spotify_track_id" />
			<input type="submit" value="<?php _e( 'Get Information', 'my_spotify_plugin' ); ?>" />
		</form>
	</div>
	<?php
}
add_shortcode( 'my_spotify_plugin_form', 'my_spotify_plugin_display_form' );

// Handle the form submission
function my_spotify_plugin_handle_form_submission() {
	if ( isset( $_POST['spotify_track_id'] ) ) {
		$spotify_track_id = sanitize_text_field( $_POST['spotify_track_id'] );
		// do something with the Spotify Track ID
	}
}
add_action( 'init', 'my_spotify_plugin_handle_form_submission' );

// Custom template for displaying Spotify Track ID
add_filter( 'single_template', 'my_spotify_plugin_custom_single_template' );
function my_spotify_plugin_custom_single_template( $single_template ) {
		global $post;
		if ( isset($post) && 'station' === $post->post_type ) {
				$theme_template = locate_template( array( 'my-spotify-plugin/single-station.php' ) );
				if ( ! empty( $theme_template ) ) {
						$single_template = $theme_template;
				} elseif ( file_exists( plugin_dir_path( __FILE__ ) . 'templates/single-station.php' ) ) {
						$single_template = plugin_dir_path( __FILE__ ) . 'templates/single-station.php';
				}
		}
		return $single_template;
}






//STEP04: 曲情報を取得するための関数を実装

function my_spotify_plugin_get_track_data( $track_id ) {
	// Get the Spotify API credentials from the options table
	$client_id = get_option( 'my_spotify_plugin_client_id' );
	$client_secret = get_option( 'my_spotify_plugin_client_secret' );
	$refresh_token = get_option( 'my_spotify_plugin_refresh_token' );
	
	// Check if the credentials are valid
	if ( ! $client_id || ! $client_secret || ! $refresh_token ) {
		return new WP_Error( 'spotify_api_error', __( 'Please enter valid Spotify API credentials', 'my_spotify_plugin' ) );
	}
	
	// Get a new access token using the refresh token
	$access_token = my_spotify_plugin_get_access_token( $client_id, $client_secret, $refresh_token );
	
	// Check if the access token is valid
	if ( is_wp_error( $access_token ) ) {
		return $access_token;
	}
	
	// Make the API request to get the track data
	$endpoint = "https://api.spotify.com/v1/tracks/{$track_id}";
	$args = array(
		'headers' => array(
			'Authorization' => "Bearer {$access_token}",
		),
	);
	$response = wp_remote_get( $endpoint, $args );
	
	// Check if the API request was successful
	if ( is_wp_error( $response ) ) {
		return $response;
	}
	
	// Decode the response body
	$body = wp_remote_retrieve_body( $response );
	$data = json_decode( $body );
	
	// Check if the response body was valid JSON
	if ( ! $data ) {
		return new WP_Error( 'spotify_api_error', __( 'The response from the Spotify API was invalid', 'my_spotify_plugin' ) );
	}
	
	// Parse the data and return it as an array
	$track_data = array(
		'spotify_track_id' => $data->id,
		'spotify_track_title' => $data->name,
		'spotify_artist' => $data->artists[0]->name,
		'spotify_album' => $data->album->name,
		'spotify_url' => $data->external_urls->spotify,
		'spotify_duration' => $data->duration_ms,
		'spotify_release_date' => $data->album->release_date,
		'spotify_popularity' => $data->popularity,
		'spotify_danceability' => $data->audio_features->danceability,
		'spotify_energy' => $data->audio_features->energy,
		'spotify_key' => $data->audio_features->key,
		'spotify_loudness' => $data->audio_features->loudness,
		'spotify_mode' => $data->audio_features->mode,
		'spotify_speechiness' => $data->audio_features->speechiness,
		'spotify_tempo' => $data->audio_features->tempo,
		'spotify_valence' => $data->audio_features->valence,
		'spotify_acousticness' => $data->audio_features->acousticness,
		'spotify_instrumentalness' => $data->audio_features->instrumentalness,
	);
	
	return $track_data;
}


// STEP05: stationカスタム投稿タイプのメタボックスに、Spotify Track IDの入力フォームを追加し、投稿時にデータを保存する
// add_meta_box の呼び出し
add_action( 'add_meta_boxes', 'my_spotify_plugin_add_station_meta_box' );
function my_spotify_plugin_add_station_meta_box() {
		global $post; // $post変数をグローバル化する
		// 'station' 投稿タイプ以外では処理を中断
		if ( 'station' !== get_post_type( $post->ID ) ) {
				return;
		}
		//add_meta_box( $id, $title, $callback, $screen = null, $context = 'advanced', $priority = 'default', $callback_args = null );
		add_meta_box(
				'my_spotify_plugin_station_meta_box',
				__( 'Spotify Track ID', 'my_spotify_plugin' ),
				'my_spotify_plugin_station_meta_box_callback',
				'station',
				'normal',
				'high'
		);
}



function my_spotify_plugin_meta_box_callback( $post ) {
		wp_nonce_field( basename( __FILE__ ), 'my_spotify_plugin_nonce' );
		$spotify_track_id = get_post_meta( $post->ID, 'spotify_track_id', true );
		echo '<label for="my_spotify_plugin_spotify_track_id">';
		_e( 'Enter the Spotify Track ID for this station:', 'my_spotify_plugin' );
		echo '</label> ';
		echo '<input type="text" id="my_spotify_plugin_spotify_track_id" name="my_spotify_plugin_spotify_track_id" value="' . esc_attr( $spotify_track_id ) . '" size="25" />';
}

function my_spotify_plugin_save_meta_box_data( $post_id ) {
		if ( ! isset( $_POST['my_spotify_plugin_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['my_spotify_plugin_meta_box_nonce'], 'my_spotify_plugin_save_meta_box_data' ) ) {
				return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
		}

		if ( 'station' !== get_post_type( $post_id ) ) {
				return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
		}

		if ( ! isset( $_POST['my_spotify_plugin_track_id_field'] ) ) {
				return;
		}

		$my_data = sanitize_text_field( $_POST['my_spotify_plugin_track_id_field'] );

		update_post_meta( $post_id, 'spotify_track_id', $my_data );
}
add_action( 'save_post_station', 'my_spotify_plugin_save_meta_box_data' );



// STEP05: 認証コードを使ってアクセストークンとリフレッシュトークンを取得する
function my_spotify_plugin_authenticate( $code ) {
		// 認証コードを使ってアクセストークンを取得する
		$token_url = 'https://accounts.spotify.com/api/token';
		$params = array(
				'grant_type' => 'authorization_code',
				'code' => $code,
				'redirect_uri' => MY_SPOTIFY_PLUGIN_REDIRECT_URI,
				'client_id' => MY_SPOTIFY_PLUGIN_CLIENT_ID,
				'client_secret' => MY_SPOTIFY_PLUGIN_CLIENT_SECRET
		);

		$response = wp_remote_post( $token_url, array(
				'body' => $params
		) );

		// レスポンスが正常な場合は、リフレッシュトークンを取得する
		if ( ! is_wp_error( $response ) && $response['response']['code'] == 200 ) {
				$body = json_decode( $response['body'], true );
				$access_token = $body['access_token'];
				$refresh_token = $body['refresh_token'];

				// 取得したリフレッシュトークンをカスタムフィールドに保存する
				my_spotify_plugin_update_refresh_token( $refresh_token );

				return array(
						'access_token' => $access_token,
						'refresh_token' => $refresh_token
				);
		}

		return null;
}


# 認証コードを使ってアクセストークンとリフレッシュトークンを取得する２
function my_spotify_plugin_request_tokens( $code ) {
		$client_id = get_option( 'my_spotify_plugin_client_id' );
		$client_secret = get_option( 'my_spotify_plugin_client_secret' );
		$redirect_uri = get_site_url() . '/wp-admin/admin.php?page=my_spotify_plugin_settings';
		$base_64_auth_string = base64_encode( $client_id . ':' . $client_secret );
		$token_endpoint = 'https://accounts.spotify.com/api/token';

		// Set headers and request body for POST request
		$headers = array(
				'Authorization: Basic ' . $base_64_auth_string,
				'Content-Type: application/x-www-form-urlencoded',
		);
		$body = array(
				'grant_type' => 'authorization_code',
				'code' => $code,
				'redirect_uri' => $redirect_uri,
		);
		$options = array(
				'http' => array(
						'method' => 'POST',
						'header' => implode( "\r\n", $headers ),
						'content' => http_build_query( $body ),
				),
		);
		$context = stream_context_create( $options );

		// Send request to token endpoint and get response
		$response = file_get_contents( $token_endpoint, false, $context );

		// Parse response JSON into array
		$tokens = json_decode( $response, true );

		// Save refresh token to options
		my_spotify_plugin_update_refresh_token( $tokens['refresh_token'] );

		return $tokens;
}



// STEP06
// メタボックスの追加
add_action( 'add_meta_boxes', 'my_spotify_plugin_add_program_meta_box' );
function my_spotify_plugin_add_program_meta_box() {
		add_meta_box(
				'my_spotify_plugin_program_meta_box',
				__( 'Spotify Track ID', 'my_spotify_plugin' ),
				'my_spotify_plugin_program_meta_box_callback',
				'program',
				'normal',
				'high'
		);
}


// メタボックスのコールバック関数
function my_spotify_plugin_station_meta_box_callback( $post ) {
	// ワンスインスタンスのノンスを作成する
	wp_nonce_field( 'my_spotify_plugin_save_meta_box_data', 'my_spotify_plugin_meta_box_nonce' );
	$value = get_post_meta( $post->ID, 'spotify_track_id', true );
	?>
	<label for="my_spotify_plugin_track_id_field"><?php _e( 'Track ID', 'my_spotify_plugin' ); ?></label>
	<input type="text" id="my_spotify_plugin_track_id_field" name="my_spotify_plugin_track_id_field" value="<?php echo esc_attr( $value ); ?>">
	<?php
}


// メタボックスの値を保存する関数
add_action( 'save_post_station', 'my_spotify_plugin_save_station_meta_box_data' );
function my_spotify_plugin_save_station_meta_box_data( $post_id ) {
		if ( ! isset( $_POST['my_spotify_plugin_meta_box_nonce'] ) ) {
				return;
		}
		if ( ! wp_verify_nonce( $_POST['my_spotify_plugin_meta_box_nonce'], 'my_spotify_plugin_save_meta_box_data' ) ) {
				return;
		}
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
		}
		if ( ! isset( $_POST['my_spotify_plugin_track_id_field'] ) ) {
				return;
		}
		$my_data = sanitize_text_field( $_POST['my_spotify_plugin_track_id_field'] );
		update_post_meta( $post_id, 'spotify_track_id', $my_data );
}

//STEP07: カスタムフィールドに保存された値を更新する
add_action( 'save_post_station', 'my_spotify_plugin_update_meta_box_data' );
function my_spotify_plugin_update_meta_box_data( $post_id ) {
		// ユーザーが編集権限を持っているかどうかを確認する
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
		}

		// 安全のためにnonceを確認する
		if ( ! isset( $_POST['my_spotify_plugin_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['my_spotify_plugin_meta_box_nonce'], 'my_spotify_plugin_save_meta_box' ) ) {
				return;
		}

		// 入力データを処理する
		if ( isset( $_POST['spotify_track_id'] ) ) {
				$spotify_track_id = sanitize_text_field( $_POST['spotify_track_id'] );
				update_post_meta( $post_id, 'spotify_track_id', $spotify_track_id );
		}
}

// STEP07: カスタムフィールドに保存された値を更新する2
add_action( 'save_post', 'my_spotify_plugin_update_post_meta_data' );
function my_spotify_plugin_update_post_meta_data( $post_id ) {
// ユーザーが編集権限を持っているかどうかを確認する
if ( ! current_user_can( 'edit_post', $post_id ) ) {
return;
}

// 安全のためにnonceを確認する
if ( ! isset( $_POST['my_spotify_plugin_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['my_spotify_plugin_meta_box_nonce'], 'my_spotify_plugin_save_meta_box' ) ) {
		return;
}

// 入力データを処理する
if ( isset( $_POST['spotify_track_id'] ) ) {
		$spotify_track_id = sanitize_text_field( $_POST['spotify_track_id'] );
		update_post_meta( $post_id, 'spotify_track_id', $spotify_track_id );
		// リフレッシュトークンをカスタムフィールドに保存する
		if ( isset( $_SESSION['my_spotify_plugin_refresh_token'] ) ) {
				$refresh_token = $_SESSION['my_spotify_plugin_refresh_token'];
				my_spotify_plugin_update_refresh_token( $refresh_token );
				unset( $_SESSION['my_spotify_plugin_refresh_token'] );
		}
}
}




//STEP08: Spotify APIでトラック情報を取得する
// include the Spotify API class
require_once plugin_dir_path( __FILE__ ) . 'includes/spotify-api.php';

// STEP08: Spotify APIでトラック情報を取得する
function my_spotify_plugin_get_track_info( $track_id ) {
		// アクセストークンを取得する
		$access_token = my_spotify_plugin_get_access_token();
		if ( ! $access_token ) {
				return false;
		}

		// Spotify APIに接続する
		$spotify_api = new Spotify_API( $access_token );
		$response = $spotify_api->get_track( $track_id );

		return $response;
}


// STEP09: カスタムフィールドを追加する
add_action( 'add_meta_boxes', 'my_spotify_plugin_add_meta_box' );
function my_spotify_plugin_add_meta_box() {
add_meta_box(
'my_spotify_plugin_meta_box',
__( 'Spotify Track ID', 'my_spotify_plugin' ),
'my_spotify_plugin_meta_box_callback',
'post',
'side'
);
}


// STEP11: テンプレートファイルをカスタマイズ
// Get Spotify Track ID for the current post
function my_spotify_plugin_get_track_id( $post_id ) {
		$spotify_track_id = get_post_meta( $post_id, '_spotify_track_id', true );
		return $spotify_track_id;
}


// STEP12: the_contentフィルターにフックする
function my_spotify_plugin_display_track_info( $content ) {
	// カスタムフィールドからSpotifyトラックIDを取得する
	$spotify_track_id = get_post_meta( get_the_ID(), 'spotify_track_id', true );

	// SpotifyトラックIDが存在しない場合はコンテンツをそのまま返す
	if ( ! $spotify_track_id ) {
		return $content;
	}

	// Spotify APIにアクセスするためのURLを作成する
	$api_url = 'https://api.spotify.com/v1/tracks/' . $spotify_track_id;

	// APIにアクセスするためのトークンを取得する
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

		// Spotify APIから取得したトラック情報がnullでない場合は表示する
		if ( $track_info !== null ) {
			// アーティスト名が定義されている場合に表示する
			if ( isset( $track_info->artists ) && ! empty( $track_info->artists ) && isset( $track_info->artists[0]->name ) ) {
				$content .= '<div><strong>Artist:</strong> ' . esc_html( $track_info->artists[0]->name ) . '</div>';
			}

			// 曲名が定義されている場合に表示する
			if ( isset( $track_info->name ) ) {
				$content .= '<div><strong>Title:</strong> ' . esc_html( $track_info->name ) . '</div>';
			}

			// アルバム名が定義されている場合に表示する
			if ( isset( $track_info->album ) && isset( $track_info->album->name ) ) {
				$content .= '<div><strong>Album:</strong> ' . esc_html( $track_info->album->name ) . '</div>';
			}

			// リリース日が定義されている場合に表示する
			if ( isset( $track_info->album ) && isset( $track_info->album->release_date ) ) {
				$content .= '<div><strong>Release date:</strong> ' . esc_html( $track_info->album->release_date ) . '</div>';
			}
		}
	}

	return $content;
}


// STEP13
/**
 * Spotify APIにアクセスするためのトークンを取得する関数
 */
function my_spotify_plugin_get_access_token() {
	// 設定ページで入力された情報を取得する
	$client_id = get_option( 'my_spotify_plugin_client_id' );
	$client_secret = get_option( 'my_spotify_plugin_client_secret' );
	$refresh_token = get_option( 'my_spotify_plugin_refresh_token' );

	// Spotify APIにアクセスするためのURLを作成する
	$api_url = 'https://accounts.spotify.com/api/token';

	// POSTデータを設定する
	$post_data = array(
		'grant_type' => 'refresh_token',
		'refresh_token' => $refresh_token,
	);

	// リクエストヘッダーを設定する
	$headers = array(
		'Authorization: Basic ' . base64_encode( $client_id . ':' . $client_secret ),
		'Content-Type: application/x-www-form-urlencoded',
	);

	// リクエストを送信する
	$response = wp_remote_post(
		$api_url,
		array(
			'headers' => $headers,
			'body' => $post_data,
			'timeout' => 30,
		)
	);

	// レスポンスのボディを取得する
	$body = wp_remote_retrieve_body( $response );

	// レスポンスのボディが空でなければJSONをデコードする
	if ( ! empty( $body ) ) {
		$access_token_info = json_decode( $body );

		// アクセストークンを返す
		if ( isset( $access_token_info->access_token ) ) {
			return $access_token_info->access_token;
		}
	}

	return '';
}

// STEP14
// シングルページでSpotifyトラックIDを取得して曲情報を表示する
function my_spotify_plugin_display_track_info_v2() {
	// シングルページでなければ処理を終了する
	if ( ! is_single() ) {
		return;
	}

	// 投稿のIDを取得する
	$post_id = get_the_ID();

	// 「Spotify Track ID」を取得する
	$spotify_track_id = get_post_meta( $post_id, 'spotify_track_id', true );

	// 「Spotify Track ID」が存在しなければ処理を終了する
	if ( ! $spotify_track_id ) {
		return;
	}

	// 取得した曲情報を出力する
	echo '<div><strong>Spotify Track ID:</strong> ' . esc_html( $spotify_track_id ) . '</div>';
}
;
