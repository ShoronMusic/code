<?php

class Spotify_API {
		private $access_token;

		public function __construct( $access_token ) {
				$this->access_token = $access_token;
		}

		public function get_track( $track_id ) {
				$url = 'https://api.spotify.com/v1/tracks/' . $track_id;
				$response = $this->send_request( $url );
				return $response;
		}

		private function send_request( $url ) {
				$args = array(
						'headers' => array(
								'Authorization' => 'Bearer ' . $this->access_token,
								'Content-Type' => 'application/json',
						),
				);
				$response = wp_remote_get( $url, $args );
				if ( is_wp_error( $response ) ) {
						$error_message = $response->get_error_message();
						return false;
				} else {
						return json_decode( wp_remote_retrieve_body( $response ), true );
				}
		}
}
