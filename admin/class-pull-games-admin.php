<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.fiverr.com/junaidzx90
 * @since      1.0.0
 *
 * @package    Pull_Games
 * @subpackage Pull_Games/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Pull_Games
 * @subpackage Pull_Games/admin
 * @author     Developer Junayed <admin@easeare.com>
 */
class Pull_Games_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pull_Games_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pull_Games_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( "selectize-plugin", plugin_dir_url( __FILE__ ) . 'css/selectize.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/pull-games-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Pull_Games_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Pull_Games_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( "selectize-plugin", plugin_dir_url( __FILE__ ) . 'js/selectize.min.js', array( 'jquery' ), $this->version, false );
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/pull-games-admin.js', array( 'selectize-plugin' ), $this->version, true );
		wp_localize_script($this->plugin_name, "pullgames",array(
			"ajaxurl" => admin_url("admin-ajax.php")
		) );

	}

	function admin_menu_page(){
		add_menu_page("Pull Games","Pull Games","manage_options", "pull-games",[$this, "pullgames_menu_html"], "dashicons-cloud-saved",45 );
	}

	function pullgames_menu_html(){
		require_once plugin_dir_path(__FILE__ )."partials/pull-games-admin-display.php";
	}

	function get_recommendations_results(){
		if(isset($_GET['experiences'])){
			$experiences = $_GET['experiences'];
			$gamesData = [];
			if(is_array($experiences)){
				foreach($experiences as $universeId){
					set_time_limit(0);

					$recommendations = $this->get_recommendations_by_universeid(intval($universeId));
					if(is_array($recommendations) && sizeof($recommendations) > 0){
						foreach($recommendations as $recommendation){
							$gamesData[] = array(
								"name" => $recommendation['name'],
								"id" => $recommendation['universeId'],
								"creator" => $recommendation['creatorName']
							);
						}
					}
				}
			}

			echo json_encode(array("success" => $gamesData));
			die;
		}
	}

	function get_search_results(){
		if(isset($_GET['filter'])){
			$filter = $_GET['filter'];
			
			switch ($filter) {
				case 'id':
					$ids = ((isset($_GET['ids']))?$_GET['ids']:null);
					$results = $this->get_result_by_ids($ids);
					$storedata = [];
					
					if(!empty($results)){

						if(is_array($results) && sizeof($results)> 0){
							foreach($results as $game){
								set_time_limit(0);
								
								$storedata[] = array(
									"name" => $game['name'],
									"id" => $game['id'],
									"creator" => $game['creator']['name']
								);

							}
						}
						
						echo json_encode(array("success" => $storedata));
						die;
					}
					break;
				
				case 'keyword':
					$keyword = ((isset($_GET['keyword']))?sanitize_text_field($_GET['keyword'] ):null);
					$maxrows = ((isset($_GET['maxrows']))?intval($_GET['maxrows']):40);
					$page = ((isset($_GET['page']))?intval($_GET['page']):0);

					$url = "https://games.roblox.com/v1/games/list?model.keyword=$keyword&model.maxRows=$maxrows&model.startRows=$page";
	
					$curl = curl_init($url);
					curl_setopt($curl, CURLOPT_URL, $url);
					curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
	
					//for debug only!
					curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
					curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
	
					$resp = curl_exec($curl);
					curl_close($curl);
	
					$resp = json_decode($resp, true);
					
					if(is_array($resp['games']) && sizeof($resp['games'])>0){
						$games = $resp['games'];
	
						$storedata = [];
						if(is_array($games) && sizeof($games)> 0){
							foreach($games as $game){
								set_time_limit(0);
								
								$storedata[] = array(
									"name" => $game['name'],
									"id" => $game['universeId'],
									"creator" => $game['creatorName'],
								);
								
							}
						}
						
						echo json_encode(array("success" => $storedata));
						die;
						
					}else{
						echo json_encode(array("norecord" => "No records found!"));
						die;
					}
					break;
			}

			echo json_encode(array("error" => "Faild!"));
			die;
		}
	}

	function get_image_url($placeid){
		$url = "https://thumbnails.roblox.com/v1/places/gameicons?placeIds=$placeid&size=512x512&format=Png&isCircular=false";

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		$headers = array(
		"Accept: application/json",
		);
		curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
		//for debug only!
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$resp = curl_exec($curl);
		curl_close($curl);

		$resp = json_decode($resp, true);
		if(!empty($resp['data'])){
			return $resp['data'][0]['imageUrl'];
		}
	}

	function setfeatured_image( $image_url, $post_id  ){
		$image_name       = 'pull-game.png';
		$upload_dir       = wp_upload_dir();
		$image_data       = file_get_contents($image_url);
		$unique_file_name = wp_unique_filename( $upload_dir['path'], $image_name );
		$filename         = basename( $unique_file_name );

		if( wp_mkdir_p( $upload_dir['path'] ) ) {
			$file = $upload_dir['path'] . '/' . $filename;
		} else {
			$file = $upload_dir['basedir'] . '/' . $filename;
		}

		file_put_contents( $file, $image_data );
		$wp_filetype = wp_check_filetype( $filename, null );

		$attachment = array(
			'post_mime_type' => $wp_filetype['type'],
			'post_title'     => sanitize_file_name( $filename ),
			'post_content'   => '',
			'post_status'    => 'inherit'
		);

		$attach_id = wp_insert_attachment( $attachment, $file, $post_id );
		require_once(ABSPATH . 'wp-admin/includes/image.php');
		$attach_data = wp_generate_attachment_metadata( $attach_id, $file );
		wp_update_attachment_metadata( $attach_id, $attach_data );
		set_post_thumbnail( $post_id, $attach_id );
	}

	function get_result_by_ids($ids){
		$url = "https://games.roblox.com/v1/games?universeIds=$ids";
	
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		//for debug only!
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$resp = curl_exec($curl);
		curl_close($curl);

		$resp = json_decode($resp, true);
		return $resp['data'];
	}

	function get_recommendations_by_universeid($id){
		$url = "https://games.roblox.com/v1/games/recommendations/game/$id";
	
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_URL, $url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

		//for debug only!
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

		$resp = curl_exec($curl);
		curl_close($curl);

		$resp = json_decode($resp, true);
		return $resp['games'];
	}

	function get_post_id_by_metavalue($mval, $post_type){
		switch ($post_type) {
			case 'roblox-experiences':
				$posts = get_posts(array(
					'numberposts'   => 1,
					'post_type'     => $post_type,
					'meta_key'      => 'universe_id',
					'meta_value'    => $mval
				));

				return ((sizeof($posts)>0)?true: false);
				break;
			case 'roblox-creators':
				$posts = get_posts(array(
					'numberposts'   => 1,
					'post_type'     => $post_type,
					'meta_key'      => 'roblox_id',
					'meta_value'    => $mval
				));

				return ((sizeof($posts)>0)?true: false);
				break;
			default:
				return false;
				break;
		}
	}

	function create_experience_post($game, $creator_id){
		if(!$this->get_post_id_by_metavalue($game['id'], 'roblox-experiences')){
			$imageurl = $this->get_image_url($game['rootPlaceId']);
			$args = array(
				'post_type' => 'roblox-experiences',
				'post_title' => $game['name'],
				'post_status' => 'publish',
				'post_author' => 1,
				'post_content' => $game['description'],
				'post_category' => array(),
				'meta_input' => array(
					'place_id' => $game['rootPlaceId'],
					'universe_id' => $game['id'],
					'image_token' => $imageurl,
					'price' => $game['price'],
					'max_players' => $game['maxPlayers'],
					'creator_id' => $creator_id,
					'roblox_created' => $game['created'],
					'roblox_updated' => $game['updated'],
					'create_vip_servers_allowed' => $game['createVipServersAllowed']
				)
			);

			$post_id = wp_insert_post($args);
			$this->setfeatured_image($imageurl, $post_id);
			wp_set_object_terms( $post_id, $game['genre'], "genre", false );

			return $post_id;
		}
	}

	function create_creator_post($game){
		if(!$this->get_post_id_by_metavalue($game['creator']['id'], 'roblox-creators')){
			$args = array(
				'post_type' => 'roblox-creators',
				'post_title' => $game['creator']['name'],
				'post_status' => 'publish',
				'post_author' => 1,
				'post_content' => '',
				'meta_input' => array(
					'universe_id' => $game['id'],
					'roblox_id' => $game['creator']['id'],
					'creator_type' => $game['creator']['type'],
					'verified_creator' => (($game['creator']['hasVerifiedBadge'])? "True": "False")
				)
			);

			$post_id = wp_insert_post($args);
			return $post_id;
		}
	}

	function pullgames_imports(){
		if(isset($_POST['gamesIds'])){
			$gamesIds = $_POST['gamesIds'];
			$gamesIds = implode(",", $gamesIds);
			
			$games = $this->get_result_by_ids($gamesIds);
			
			if($games){
				foreach($games as $game){
					set_time_limit(0);
					$creator_id = $this->create_creator_post($game);
					$this->create_experience_post($game, $creator_id);
				}
			}

			echo json_encode(array("success" => "Success"));
			die;
		}
	}

	function get_experience_posts(){
		if(isset($_GET['query'])){
			$query = sanitize_text_field($_GET['query'] );
			$query = stripslashes($query);

			$experiences = get_posts([
				'post_type' => 'roblox-experiences',
				'numberposts' => -1,
				's' => $query
			]);

			$data = [];
			if($experiences){
				foreach($experiences as $experience){
					$data[] = [
						'id' => get_post_meta($experience->ID, 'universe_id', true),
						'title' => $experience->post_title
					];
				}
			}

			echo json_encode(array("success" => $data));
			die;
		}
	}

}
