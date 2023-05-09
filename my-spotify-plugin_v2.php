<?php
/**
 * Plugin Name: My Spotify Plugin
 * Plugin URI: https://example.com/plugins/my-spotify-plugin/
 * Description: This plugin allows you to display Spotify track information on your WordPress site.
 * Version: 1.0.0
 * Author: John Doe
 * Author URI: https://example.com/
 * License: GPL2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || exit;

// トラックIDのメタフィールドを追加する
function my_spotify_plugin_add_meta_box() {
	add_meta_box(
		'my_spotify_plugin_track_info',
		'Spotify Track Info',
		'my_spotify_plugin_meta_box_callback',
		'post'
	);
}
add_action( 'add_meta_boxes', 'my_spotify_plugin_add_meta_box' );

// メタボックスの中身を出力する
function my_spotify_plugin_meta_box_callback( $post ) {
	// ワードプレスのセキュリティチェック
	wp_nonce_field( basename( __FILE__ ), 'my_spotify_plugin_nonce' );

	// 「Spotify Track ID」を取得する
	$spotify_track_id = get_post_meta( $post->ID, 'spotify_track_id', true );

	// フォームを出力する
	echo '<label for="my_spotify_plugin_track_id">Spotify Track ID:</label>';
	echo '<input type="text" id="my_spotify_plugin_track_id" name="my_spotify_plugin_track_id" value="' . esc_attr( $spotify_track_id ) . '">';
}

// トラックIDを保存する
function my_spotify_plugin_save_track_info( $post_id ) {
	// セキュリティチェック
	if ( ! isset( $_POST['my_spotify_plugin_nonce'] ) || ! wp_verify_nonce( $_POST['my_spotify_plugin_nonce'], basename( __FILE__ ) ) ) {
		return;
	}

	// 自動保存の場合は処理を終了する
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	// 権限のチェック
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	// リクエストの値を取得する
	if ( isset( $_POST['my_spotify_plugin_track_id'] ) ) {
		$spotify_track_id = sanitize_text_field( $_POST['my_spotify_plugin_track_id'] );
	} else {
		$spotify_track_id = '';
	}

	// メタデータを保存する
	update_post_meta( $post_id, 'spotify_track_id', $spotify_track_id );
}
add_action( 'save_post', 'my_spotify_plugin_save_track_info' );

// Spotify APIにアクセスするためのトークンを取得する関数
function my_spotify_plugin_get_access_token() {
	// 設定ページで入力された情報を取得する
	$client_id = get_option( 'my_spotify_plugin_client_id' );
	$client_secret = get_option( 'my_spotify_plugin_client_secret' );
	$refresh_token = get_option( 'my_spotify_plugin_refresh_token' );

	// トークン取得用のエンドポイント
$token_url = 'https://accounts.spotify.com/api/token';

// POST送信するデータを用意する
$data = array(
	'grant_type' => 'refresh_token',
	'refresh_token' => $refresh_token,
);

// クライアントIDとシークレットをAuthorizationヘッダーに追加する
$headers = array(
	'Authorization' => 'Basic ' . base64_encode( $client_id . ':' . $client_secret ),
);

// cURLを初期化する
$curl = curl_init();

// cURLオプションを設定する
curl_setopt( $curl, CURLOPT_URL, $token_url ); // URLを設定する
curl_setopt( $curl, CURLOPT_POST, true ); // POSTリクエストを設定する
curl_setopt( $curl, CURLOPT_POSTFIELDS, http_build_query( $data ) ); // 送信するデータを設定する
curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers ); // ヘッダー情報を設定する
curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true ); // レスポンスを文字列で返すように設定する

// cURLリクエストを実行する
$response = curl_exec( $curl );

// cURLリクエストが失敗した場合はエラーを出力する
if ( curl_errno( $curl ) ) {
	error_log( 'cURL Error: ' . curl_error( $curl ) );
	return false;
}

// cURLセッションをクローズする
curl_close( $curl );

// レスポンスをJSON形式から配列に変換する
$data = json_decode( $response, true );

// アクセストークンが含まれていなければエラーを出力する
if ( empty( $data['access_token'] ) ) {
	error_log( 'Spotify Access Token Not Found.' );
	return false;
}

return $data['access_token'];
}

// SpotifyのAPIを使用してトラック情報を取得する関数
function my_spotify_plugin_get_track_info( $access_token, $spotify_track_id ) {
// トラック情報取得用のエンドポイント
$track_url = 'https://api.spotify.com/v1/tracks/' . $spotify_track_id;
// Authorizationヘッダーを設定する
$headers = array(
	'Authorization' => 'Bearer ' . $access_token,
);

// cURLを初期化する
$curl = curl_init();

// cURLオプションを設定する
curl_setopt( $curl, CURLOPT_URL, $track_url ); // URLを設定する
curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers ); // ヘッダー情報を設定する
curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true ); // レスポンスを文字列で返すように設定する

// cURLリクエストを実行する
$response = curl_exec( $curl );

// cURLリクエストが失敗

return null;
}

// レスポンスボディからトークンを取得する
$response_body = wp_remote_retrieve_body( $response );
$data = json_decode( $response_body, true );
$access_token = isset( $data['access_token'] ) ? $data['access_token'] : null;

// トークンを返す
return $access_token;

}

// Spotify APIからトラック情報を取得する関数
function my_spotify_plugin_get_track_info( $track_id ) {
// Spotify APIのエンドポイントURLを作成する
$url = 'https://api.spotify.com/v1/tracks/' . $track_id;

// ヘッダーにAuthorization情報を追加する
$access_token = my_spotify_plugin_get_access_token();
if ( ! $access_token ) {
	return null;
}
$headers = array(
	'Authorization' => 'Bearer ' . $access_token,
);

// cURLリクエストを送信する
$response = wp_remote_get( $url, array(
	'headers' => $headers,
) );

// cURLリクエストが失敗した場合はnullを返す
if ( is_wp_error( $response ) ) {
	return null;
}

// レスポンスボディを取得する
$response_body = wp_remote_retrieve_body( $response );

// レスポンスボディをJSON形式から配列に変換する
$data = json_decode( $response_body, true );

// 配列から必要な情報を取得する
$track_info = array(
	'title' => isset( $data['name'] ) ? $data['name'] : '',
	'artist' => '',
	'album_image' => '',
);
if ( isset( $data['artists'] ) && is_array( $data['artists'] ) ) {
	foreach ( $data['artists'] as $artist ) {
		if ( $track_info['artist'] ) {
			$track_info['artist'] .= ', ';
		}
		$track_info['artist'] .= $artist['name'];
	}
}
if ( isset( $data['album'] ) && isset( $data['album']['images'] ) && is_array( $data['album']['images'] ) ) {
	$track_info['album_image'] = $data['album']['images'][0]['url'];
}

// トラック情報を返す
return $track_info;

}

// 投稿画面にSpotifyの曲情報のメタボックスを追加する
function my_spotify_plugin_add_meta_box() {
add_meta_box(
'my_spotify_plugin_track_info',
'Spotify Track Info',
'my_spotify_plugin_display_track_info',
'post',
'side'
);
}
add_action( 'add_meta_boxes', 'my_spotify_plugin_add_meta_box' );

// Spotifyの曲情報のメタボックスに表示するコンテンツを生成する
function my_spotify_plugin_display_track_info() {
global $post;
// 「Spotify Track ID」を取得する
$spotify_track_id = get_post_meta( $post->ID, 'spotify_track_id', true );
// 「

function my_spotify_plugin_save_track_info( $post_id ) {
    // 投稿が保存されたときに実行する処理

    // 保存された投稿の種類が「Station」でなければ処理を終了する
    if ( get_post_type( $post_id ) !== 'station' ) {
        return;
    }

    // 「Spotify Track ID」を取得する
    $spotify_track_id = get_post_meta( $post_id, 'spotify_track_id', true );

    // 「Spotify Track ID」が存在しなければ処理を終了する
    if ( ! $spotify_track_id ) {
        return;
    }

    // Spotify APIにアクセスするためのトークンを取得する
    $access_token = my_spotify_plugin_get_access_token();

    // Spotify APIにアクセスするためのリクエストURLを設定する
    $url = 'https://api.spotify.com/v1/tracks/' . $spotify_track_id;

    // cURLリクエストを送信する
    $ch = curl_init();
    curl_setopt( $ch, CURLOPT_URL, $url );
    curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
    curl_setopt( $ch, CURLOPT_HTTPHEADER, array(
        'Authorization: Bearer ' . $access_token,
        'Content-Type: application/json'
    ) );
    $response = curl_exec( $ch );
    $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
    curl_close( $ch );

    // cURLリクエストが失敗した場合は処理を終了する
    if ( $http_code !== 200 ) {
        return;
    }

    // 取得したトラック情報をJSON形式から配列に変換する
    $track_info = json_decode( $response, true );

    // トラック情報から必要な情報を取り出す
    $name = $track_info['name'];
    $artists = array();
    foreach ( $track_info['artists'] as $artist ) {
        $artists[] = $artist['name'];
    }
    $album_name = $track_info['album']['name'];
    $album_image_url = $track_info['album']['images'][0]['url'];
    $spotify_url = $track_info['external_urls']['spotify'];

// カスタムフィールドにトラック情報を保存する
update_post_meta( $post_id, 'spotify_track_id', $track_id );
update_post_meta( $post_id, 'spotify_track_name', $track_name );
update_post_meta( $post_id, 'spotify_track_artist', $track_artist );
update_post_meta( $post_id, 'spotify_track_image_url', $track_image_url );
}
add_action( 'save_post', 'my_spotify_plugin_save_track_info' );

// Spotify APIにアクセスするためのトークンを取得する関数
function my_spotify_plugin_get_access_token() {
// 設定ページで入力された情報を取得する
$client_id = get_option( 'my_spotify_plugin_client_id' );
$client_secret = get_option( 'my_spotify_plugin_client_secret' );
$refresh_token = get_option( 'my_spotify_plugin_refresh_token' );

}
add_action( 'save_post', 'my_spotify_plugin_save_track_info' );

// Spotify APIにアクセスするためのトークンを取得する関数
function my_spotify_plugin_get_access_token() {
// 設定ページで入力された情報を取得する
$client_id = get_option( 'my_spotify_plugin_client_id' );
$client_secret = get_option( 'my_spotify_plugin_client_secret' );
$refresh_token = get_option( 'my_spotify_plugin_refresh_token' );
}

// カスタムフィールドにトラック情報を保存する
function my_spotify_plugin_save_track_info( $post_id ) {
// オートセーブ中は処理を終了する
if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
return;
}

// 投稿のタイプが「Station」でなければ処理を終了する
if ( get_post_type( $post_id ) !== 'station' ) {
	return;
}

// 「Spotify Track ID」を取得する
$spotify_track_id = filter_input( INPUT_POST, 'spotify_track_id', FILTER_SANITIZE_STRING );

// 「Spotify Track ID」が存在しなければ処理を終了する
if ( ! $spotify_track_id ) {
	return;
}

// トラック情報を取得する
$track_info = my_spotify_plugin_get_track_info( $spotify_track_id );

// カスタムフィールドにトラック情報を保存する
update_post_meta( $post_id, 'spotify_track_id', $spotify_track_id );
update_post_meta( $post_id, 'spotify_track_name', $track_info['name'] );
update_post_meta( $post_id, 'spotify_artist_name', $track_info['artist_name'] );
update_post_meta( $post_id, 'spotify_preview_url', $track_info['preview_url'] );
}

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

add_action( 'save_post', 'my_spotify_plugin_save_track_info' );

// 投稿を保存する前にSpotifyのトラック情報を取得してカスタムフィールドに保存する
function my_spotify_plugin_save_track_info( $post_id ) {
	// nonceチェック
	if ( ! isset( $_POST['my_spotify_plugin_meta_box_nonce'] ) || ! wp_verify_nonce( $_POST['my_spotify_plugin_meta_box_nonce'], basename( __FILE__ ) ) ) {
		return $post_id;
	}

	// 自動保存時は処理を終了する
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return $post_id;
	}

	// 投稿のタイプが「station」でなければ処理を終了する
	if ( get_post_type( $post_id ) !== 'station' ) {
		return $post_id;
	}

	// Spotify APIにアクセスするためのトークンを取得する
	$access_token = my_spotify_plugin_get_access_token();

	// 「Spotify Track ID」を取得する
	$spotify_track_id = isset( $_POST['my_spotify_plugin_spotify_track_id'] ) ? sanitize_text_field( $_POST['my_spotify_plugin_spotify_track_id'] ) : '';

	// 「Spotify Track ID」が存在する場合は、Spotify APIから曲情報を取得してカスタムフィールドに保存する
	if ( $spotify_track_id ) {
		// cURLリクエストで曲情報を取得する
		$url = "https://api.spotify.com/v1/tracks/{$spotify_track_id}";
		$args = array(
			'headers' => array(
				'Authorization' => "Bearer {$access_token}",
			),
		);
		$response = wp_remote_get( $url, $args );

		// cURLリクエストが失敗した場合は処理を終了する
		if ( is_wp_error( $response ) ) {
			return;
		}

		// レスポンスを解析して曲情報を取得する
		$data = json_decode( wp_remote_retrieve_body( $response ) );
		$track_info = array(
			'artist' => $data->artists[0]->name,
			'track_name' => $data->name,
			'album_image' => $data->album->images[0]->url,
		);

		// カスタムフィールドにトラック情報を保存する
		update_post_meta( $post_id, 'track_info', $track_info );
	}
}
add_action( 'save_post', 'my_spotify_plugin_save_track_info', 10, 2 );
// カスタムフィールドにトラック情報を保存する
    update_post_meta( $post_id, 'spotify_track_id', $spotify_track_id );
    update_post_meta( $post_id, 'spotify_track_name', $track_name );
    update_post_meta( $post_id, 'spotify_artist_name', $artist_name );
    update_post_meta( $post_id, 'spotify_album_name', $album_name );
}

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
    echo '<div><strong>Track Name:</strong> ' . esc_html( get_post_meta( $post_id, 'spotify_track_name', true ) ) . '</div>';
    echo '<div><strong>Artist Name:</strong> ' . esc_html( get_post_meta( $post_id, 'spotify_artist_name', true ) ) . '</div>';
    echo '<div><strong>Album Name:</strong> ' . esc_html( get_post_meta( $post_id, 'spotify_album_name', true ) ) . '</div>';
}

