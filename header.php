<?php
/**
 * Display station header
 */
defined( 'ABSPATH' ) || exit;
?>

	<header class="entry-header" id="station_single">

	<div class="wp-block-columns">
		<div class="wp-block-column" style="flex-basis:300px;">
		<?php the_post_thumbnail('medium'); ?>
		</div>
		<div class="wp-block-column">
			<div class="entry-term">
				<dl>
					<dt><?php the_title( '<h1 class="#entry-title">', '</h1>' ); ?></dt>
					<dd>
						<h3>
							<div class="artistlist">
									<?php global $post; ?>
<span class="artistname"><?php echo get_the_term_list($post->ID, 'artist','<span>','</span><span>','</span>'); ?></span>
<span class="<?php $terms = get_the_terms($post->ID, 'vocal'); if ( $terms ) { echo $terms[0]->name; } ?>"></span>



								</span>
							</div>
						</h3>
					</dd>
					<dd><span class="collabolist"><?php $terms = get_the_terms($post->ID, 'collabo'); if ( $terms ) { echo $terms[0]->name; } ?></span>	
					<span class="featuringlist"><?php echo get_the_term_list($post->ID, 'artist','<span>','</span><span>','</span>'); ?></span></dd>
					<dd><div class="originlist"><?php if ($terms = get_the_terms($post->ID, 'artist')) { foreach ( $terms as $term ) { echo '<span>' .$term->description. '</span>'; } } ?></div></dd>
					<dd><?php the_time('Y');?></dd>
					<dd><div class="genrelist"><?php echo get_the_term_list($post->ID, 'genre','<span>','</span><span>','</span>'); ?></div></dd>
					<dd>
<?php $text_field = get_field('stream'); ?>
<?php echo $text_field; ?>
<hr>
<?php
	 $str = get_field('stream'); //置換前
	 $search = array('https://www.youtube.com/watch?v='); //置換対象候補
	 $new_str = str_replace($search, '', $str);
	 print $new_str;
?>
					</dd>
				</dl>



			</div>
			<?php do_action( 'play_single_header_start'); ?>
			
			<?php do_action( 'play_after_single_title'); ?>
			<div class="entry-meta">
				<?php do_action( 'play_single_meta'); ?>
			</div>
		<?php do_action( 'play_single_header_end'); ?>
			<div class="song_ex">
<hr>
<p><?php
	$cat = get_field('song_sample_01');
		if ($cat == 'original') {
			echo '<em class="originalmark"><i class="fas fa-record-vinyl"></i> Original</em>';
		} elseif ($cat == 'sampled') {
			echo '<em class="sampledmark"><i class="fas fa-record-vinyl"></i> Sampled</em>';
			} elseif ($cat == 'remixed') {
			echo '<em class="remixeddmark"><i class="fas fa-record-vinyl"></i> Remixed</em>';
		} else {
			echo '';
		}
 ?><br>
<?php $text = get_field('sample_artist_01'); if( $text ){ echo $text; }?>
<?php if(get_field('sample_song_01')): ?> - <?php the_field('sample_song_01'); ?><?php endif; ?>
<?php if(get_field('sample_origine_01')): ?> / <?php the_field('sample_origine_01'); ?><?php endif; ?><?php if(get_field('sample_origine_01')): ?> <?php the_field('sample_rereased_01'); ?><?php endif; ?><?php if(get_field('sample_origine_01')): ?> (<?php the_field('sample_genre_01'); ?>)<?php endif; ?>
<?php if(get_field('sample_url_01')): ?> <a href="<?php the_field('sample_url_01'); ?>" target=""_blank><i class="fas fa-external-link-alt"></i></a><?php endif; ?>
</p>
<?php if(get_field('sample_text_01')): ?><p><?php the_field('sample_text_01'); ?></p><?php endif; ?>
<hr>
			</div>
		</div>
	</div>
		
	</header>

<hr>
<hr>
<hr>

<?php
// シングルページでSpotifyトラックIDを取得し、APIにアクセスするためのURLを作成する
function my_spotify_plugin_get_track_info() {
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

    // 取得した曲情報を出力する
    echo '<div><strong>Artist:</strong> ' . esc_html( $track_info->artists[0]->name ) . '</div>';
    echo '<div><strong>Title:</strong> ' . esc_html( $track_info->name ) . '</div>';
    echo '<div><strong>Album:</strong> ' . esc_html( $track_info->album->name ) . '</div>';
    echo '<div><strong>Release date:</strong> ' . esc_html( $track_info->album->release_date ) . '</div>';
  }
}
?>

<hr>
<hr>
<hr>


	<?php do_action( 'play_after_single_header'); ?>


<style>

.featuringlist span { display:none; }

.artistlist span:nth-child(n+<?php $terms = get_the_terms($post->ID, 'featuring'); if ( $terms ) { echo $terms[0]->name; } ?>) {
	display:none;
	}

.featuringlist span:nth-child(n+<?php $terms = get_the_terms($post->ID, 'featuring'); if ( $terms ) { echo $terms[0]->name; } ?>) {
	display:inline-block;
	}

.artistname span:not(:first-of-type) {
	!color: green;
	padding: 0 7px 0 7px;
	content: "｜";
	 }

.originlist span:not(:first-child)::before {
	content: "｜";
}

.genrelist span:not(:first-child)::before {
	content: "｜";
}

em {font-style: normal; color:#006e97; margin: 0 1em 0 0}

</style>


	</div><!-- .entry-content -->

	<footer class="entry-footer">
		<?php your_theme_entry_footer(); ?>
	</footer><!-- .entry-footer -->
</article><!-- #post-<?php the_ID(); ?> -->
</div>
