<?php
/**
 * @package KPST
 * Sitedeki toplam istatistikleri dondurur.
 */
class Kpst_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'Kpst_Widget',
			__( 'KPST shared counter Widget' , 'kuaza-post-shared-tracker'),
			array( 'description' => __( 'Display Shared counter widget (KPST)' , 'kuaza-post-shared-tracker') )
		);

	}

	function form( $instance ) {
		if ( $instance ) {
			$title = $instance['title'];
			$text_cache = esc_attr($instance['text_cache']);
			$textarea_widgetthemes_kpst = esc_textarea($instance['kpstwgtheme']);
			$cachecek_ismi = $this->get_field_id("cache_serialize");
			$cache_time = $this->get_field_id("cache_time");
		}
		else {
			$title = __( 'Total shared counter' , 'kuaza-post-shared-tracker');
			$text_cache = "60";
			$textarea_widgetthemes_kpst = '<div class="paylasimcevre_bilesen" id="">

					<!-- total shared -->
					<button class="btn btn-toplampaylasim_bilesen"><i class="fa fa-share"></i> {{hepsi}}</button>

					<!-- Twitter -->
					<button class="btn btn-twitterx btn-tamgenis"><i class="fa fa-twitter"></i> {{twitter}}</button>

					 <!-- Facebook -->
					<button class="btn btn-facebook btn-tamgenis"><i class="fa fa-facebook"></i> {{facebook}}</button>

					<!-- Google+ -->
					<button class="btn btn-googleplus btn-tamgenis"><i class="fa fa-google-plus"></i> {{google}}</button>

					<!-- StumbleUpon -->
					<button class="btn btn-stumbleupon btn-tamgenis"><i class="fa fa-stumbleupon"></i> {{stumbleupon}}</button>

					<!-- LinkedIn --> 
					<button class="btn btn-linkedin btn-tamgenis"><i class="fa fa-linkedin"></i> {{linkedin}}</button>

					<!-- pinterest --> 
					<button class="btn btn-pinterest btn-tamgenis"><i class="fa fa-pinterest"></i> {{pinterest}}</button>
					</div>

					';
			$cachecek_ismi = $this->get_field_id("cache_serialize");
			$cache_time = $this->get_field_id("cache_time");
		}
?>

		<div>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:' , 'kuaza-post-shared-tracker'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo !empty($title) ? esc_attr( $title ) : ""; ?>" />
		
<p>
<label for="<?php echo $this->get_field_id('text_cache'); ?>"><?php esc_html_e( 'Cache time:' , 'kuaza-post-shared-tracker'); ?></label>
<input class="widefat" id="<?php echo $this->get_field_id('text_cache'); ?>" name="<?php echo $this->get_field_name('text_cache'); ?>" type="text" value="<?php echo !empty($text_cache) ? $text_cache : "60"; ?>" />
</p>

<p>
<label for="<?php echo $this->get_field_id('kpstwgtheme'); ?>"><?php _e('Templates for widget:', 'kuaza-post-shared-tracker'); ?></label>
<textarea class="widefat" id="<?php echo $this->get_field_id('kpstwgtheme'); ?>" name="<?php echo $this->get_field_name('kpstwgtheme'); ?>"><?php echo !empty($textarea_widgetthemes_kpst) ? $textarea_widgetthemes_kpst : ""; ?></textarea>
</p>
	
<p>
<?php _e('Templates for Parameters', 'kuaza-post-shared-tracker'); ?>
<ol>
<li>{{facebook}} : <?php _e("facebook share count:","kuaza-post-shared-tracker"); ?></li>
<li>{{twitter}} : <?php _e("twitter share count:","kuaza-post-shared-tracker"); ?></li>
<li>{{google}} : <?php _e("google share count:","kuaza-post-shared-tracker"); ?></li>
<li>{{linkedin}} : <?php _e("linkedin share count:","kuaza-post-shared-tracker"); ?></li>
<li>{{stumbleupon}} : <?php _e("stumbleupon share count:","kuaza-post-shared-tracker"); ?></li>
<li>{{pinterest}} : <?php _e("Pinterest share count:","kuaza-post-shared-tracker"); ?></li>
<li>{{hepsi}} : <?php _e("All share count (total):","kuaza-post-shared-tracker"); ?></li>
</ol>
</p>
<p>
<?php _e('Example: (same demo)', 'kuaza-post-shared-tracker'); ?>
<textarea class="widefat">

<div class="paylasimcevre_bilesen" id="paylasimcevre_bilesen">

<!-- total shared -->
<button class="btn btn-toplampaylasim_bilesen"><i class="fa fa-share"></i> {{hepsi}}</button>

<!-- Twitter -->
<button class="btn btn-twitterx btn-tamgenis"><i class="fa fa-twitter"></i> {{twitter}}</button>

 <!-- Facebook -->
<button class="btn btn-facebook btn-tamgenis"><i class="fa fa-facebook"></i> {{facebook}}</button>

<!-- Google+ -->
<button class="btn btn-googleplus btn-tamgenis"><i class="fa fa-google-plus"></i> {{google}}</button>

<!-- StumbleUpon -->
<button class="btn btn-stumbleupon btn-tamgenis"><i class="fa fa-stumbleupon"></i> {{stumbleupon}}</button>

<!-- LinkedIn --> 
<button class="btn btn-linkedin btn-tamgenis"><i class="fa fa-linkedin"></i> {{linkedin}}</button>

<!-- pinterest --> 
<button class="btn btn-pinterest btn-tamgenis"><i class="fa fa-pinterest"></i> {{pinterest}}</button>
</div>

</textarea>	
</p>	
		</div>

<?php
	}

	function update( $new_instance, $old_instance="" ) {
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['text_cache'] = !empty($new_instance['text_cache']) ? intval( $new_instance['text_cache'] ) : "60";
		$instance['kpstwgtheme'] = !empty($new_instance['kpstwgtheme']) ? $new_instance['kpstwgtheme'] : null;
		$cachecek_ismi = $new_instance['cachecek_ismi'];
		$cache_time = $new_instance['cache_time'];
		update_option( $cache_time, current_time('mysql') );
		
	/* sorgu sonucunu kaydedelim cache icin */
	global $wpdb;
		$table_name = $wpdb->prefix . "kuazasocialtracker";	
			$toplam_paylasim_kpst = $wpdb->get_results(  
				"SELECT 
				SUM(ku_facebook) as facebook, 
				SUM(ku_google) as google, 
				SUM(ku_twitter) as twitter, 
				SUM(ku_linkedin) as linkedin, 
				SUM(ku_pinterest) as pinterest, 
				SUM(ku_stumble) as stumbleupon, 
				SUM(ku_hepsi) as hepsi
				
				FROM ".$table_name."
				", ARRAY_A );
				$kpstwgcache = serialize($toplam_paylasim_kpst[0]);	
					update_option( $cachecek_ismi, $kpstwgcache );

	/* bitis / sorgu sonucunu kaydedelim cache icin */
		return $instance;
	}

	function widget( $args, $instance ) {

// bilesen guncellendiginde yada eklendiginde eklenen otomatik zaman damgasi	
$cache_time = !empty($instance['cache_time']) ? $instance['cache_time'] : $this->get_field_id("cache_time");
$cache_time_db = get_site_option($cache_time);
$cachecek_ismi = !empty($instance['cache_serialize']) ? $instance['cache_serialize'] : $this->get_field_id("cache_serialize");

// kullanicinin bileseni eklerken belirledigi cache araligi default 60 saniye
$cachearaligi = !empty($instance['text_cache']) ? $instance['text_cache'] : "60";

			$limitzaman = (60*$cachearaligi); // 60*1 dakika
				$suanki_zaman = strtotime(current_time('mysql'));
					$tablo_cache_zamani = strtotime($cache_time_db);
						$tablo_cache_zamani_yeni = $tablo_cache_zamani + $limitzaman;
 		
			if($suanki_zaman > $tablo_cache_zamani_yeni){ // cache suresi bittiyse yeni cache aliriz
			global $wpdb;

				/* sorgu sonucunu kaydedelim cache icin */
					$table_name = $wpdb->prefix . "kuazasocialtracker";	
						$toplam_paylasim_kpst = $wpdb->get_results(
							"SELECT 
							SUM(ku_facebook) as facebook, 
							SUM(ku_google) as google, 
							SUM(ku_twitter) as twitter, 
							SUM(ku_linkedin) as linkedin, 
							SUM(ku_pinterest) as pinterest, 
							SUM(ku_stumble) as stumbleupon, 
							SUM(ku_hepsi) as hepsi
							FROM ".$table_name."
							", ARRAY_A );
							$guncelsonuclar = serialize($toplam_paylasim_kpst[0]);				
				/* bitis / sorgu sonucunu kaydedelim cache icin */
				
					update_option( $cache_time, current_time('mysql') );
								update_option( $cachecek_ismi, $guncelsonuclar );

				$toplam_paylasim_kpst = unserialize($guncelsonuclar);
				
			}else{ // cache suresi bitmemis ise eski cacheden verileri cekeriz

				$toplam_paylasim_kpst = unserialize(get_site_option($cachecek_ismi));

			}

		$kspt_widget_temasi = !empty($instance['kpstwgtheme']) ? $instance['kpstwgtheme'] : null;
			if ( !empty($toplam_paylasim_kpst)  ) :

			foreach ($toplam_paylasim_kpst as $code => $value)
				$kspt_widget_temasi = str_replace('{{'.$code.'}}', $value, $kspt_widget_temasi);
			
			endif;

		$yenitasarim_bilesen_kpst = stripslashes($kspt_widget_temasi);
		
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'];
			echo esc_html( $instance['title'] );
			echo $args['after_title'];
		}
?>

	<div class="kpst-counter">
	<?php echo $yenitasarim_bilesen_kpst; ?>
	</div>

<?php
		echo $args['after_widget'];
	}
}


/**
 * @package KPST
 * Sitedeki populer yazilari listeler.
 */
class Kpst2_Widget extends WP_Widget {

	function __construct() {
		parent::__construct(
			'Kpst2_Widget',
			__( 'KPST most shared posts' , 'kuaza-post-shared-tracker'),
			array( 'description' => __( 'Display most popular shared posts (KPST)' , 'kuaza-post-shared-tracker') )
		);

	}

	function form( $instance ) {
		if ( $instance ) {
			$title = $instance['title'];
			$text_cache = esc_attr($instance['text_cache']);
			$text_limit = esc_attr($instance['text_limit']);
			$textarea_widgetthemes_kpst = esc_attr($instance['kpstwgtheme']);
			$kpstwgtheme_once = esc_attr($instance['kpstwgtheme_once']);
			$kpstwgtheme_sonra = esc_attr($instance['kpstwgtheme_sonra']);
			$select_order = !empty($instance['select_order']) ? esc_attr($instance['select_order']) : "ku_hepsi";
			$cachecek_ismi = $this->get_field_id("cachecek_ismi");
			$cache_time_ismi = $this->get_field_id("cache_time_ismi");
		}
		else {
			$title = __( 'Popular shared posts' , 'kuaza-post-shared-tracker');
			$text_cache = "60";
			$textarea_widgetthemes_kpst = '';
			$kpstwgtheme_once = '';
			$kpstwgtheme_sonra = '';
			$text_limit = '5';
			$select_order = "ku_hepsi";
			$cachecek_ismi = $this->get_field_id("cachecek_ismi");
			$cache_time_ismi = $this->get_field_id("cache_time_ismi");
		}
?>

		<div>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php esc_html_e( 'Title:' , 'kuaza-post-shared-tracker'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
				
		<p>
		<label for="<?php echo $this->get_field_id('text_cache'); ?>"><?php esc_html_e( 'Cache time:' , 'kuaza-post-shared-tracker'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('text_cache'); ?>" name="<?php echo $this->get_field_name('text_cache'); ?>" type="text" value="<?php echo !empty($text_cache) ? $text_cache : "60"; ?>" />
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('select_order'); ?>"><?php esc_html_e( 'Order by:' , 'kuaza-post-shared-tracker'); ?></label>

		<select id="<?php echo $this->get_field_id('select_order'); ?>" name="<?php echo $this->get_field_name('select_order'); ?>">
		<option value="ku_hepsi"<?php echo (($select_order == "ku_hepsi") ? " selected" : ""); ?>><?php esc_html_e( 'Total (All)' , 'kuaza-post-shared-tracker'); ?></option>
		<option value="ku_facebook"<?php echo (($select_order == "ku_facebook") ? " selected" : ""); ?>><?php esc_html_e( 'facebook' , 'kuaza-post-shared-tracker'); ?></option>
		<option value="ku_twitter"<?php echo (($select_order == "ku_twitter") ? " selected" : ""); ?>><?php esc_html_e( 'Twitter' , 'kuaza-post-shared-tracker'); ?></option>
		<option value="ku_google"<?php echo (($select_order == "ku_google") ? " selected" : ""); ?>><?php esc_html_e( 'Google' , 'kuaza-post-shared-tracker'); ?></option>
		<option value="ku_linkedin"<?php echo (($select_order == "ku_linkedin") ? " selected" : ""); ?>><?php esc_html_e( 'Linkedin' , 'kuaza-post-shared-tracker'); ?></option>
		<option value="ku_pinterest"<?php echo (($select_order == "ku_pinterest") ? " selected" : ""); ?>><?php esc_html_e( 'Pinterest' , 'kuaza-post-shared-tracker'); ?></option>
		<option value="ku_stumble"<?php echo (($select_order == "ku_stumble") ? " selected" : ""); ?>><?php esc_html_e( 'Stumble' , 'kuaza-post-shared-tracker'); ?></option>
		</select>
		</p>

		<p>
		<label for="<?php echo $this->get_field_id('text_limit'); ?>"><?php esc_html_e( 'Limit (default:5):' , 'kuaza-post-shared-tracker'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('text_limit'); ?>" name="<?php echo $this->get_field_name('text_limit'); ?>" type="text" value="<?php echo !empty($text_limit) ? $text_limit : "5"; ?>" />
		</p>

		<p>
		<label for="<?php echo $this->get_field_id('kpstwgtheme_once'); ?>"><?php _e('Templates for loop Before:', 'kuaza-post-shared-tracker'); ?></label>
		<textarea class="widefat" id="<?php echo $this->get_field_id('kpstwgtheme_once'); ?>" name="<?php echo $this->get_field_name('kpstwgtheme_once'); ?>"><?php echo !empty($kpstwgtheme_once) ? $kpstwgtheme_once : ""; ?></textarea>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('kpstwgtheme'); ?>"><?php _e('Templates for loop: (loop)', 'kuaza-post-shared-tracker'); ?></label>
		<textarea class="widefat" id="<?php echo $this->get_field_id('kpstwgtheme'); ?>" name="<?php echo $this->get_field_name('kpstwgtheme'); ?>"><?php echo !empty($textarea_widgetthemes_kpst) ? $textarea_widgetthemes_kpst : ""; ?></textarea>
		</p>
		<p>
		<label for="<?php echo $this->get_field_id('kpstwgtheme_sonra'); ?>"><?php _e('Templates for loop After:', 'kuaza-post-shared-tracker'); ?></label>
		<textarea class="widefat" id="<?php echo $this->get_field_id('kpstwgtheme_sonra'); ?>" name="<?php echo $this->get_field_name('kpstwgtheme_sonra'); ?>"><?php echo !empty($kpstwgtheme_sonra) ? $kpstwgtheme_sonra : ""; ?></textarea>
		</p>	
		<p>
		<?php _e('Templates for Parameters (only loop)', 'kuaza-post-shared-tracker'); ?>
		<ol>
		<li>{{post_title}} : <?php _e("Post title","kuaza-post-shared-tracker"); ?></li>
		<li>{{permalink}} : <?php _e("Post link (direct url)","kuaza-post-shared-tracker"); ?></li>
		<li>{{comment_count}} : <?php _e("Total comments","kuaza-post-shared-tracker"); ?></li>
		<li>{{facebook}} : <?php _e("facebook share count","kuaza-post-shared-tracker"); ?></li>
		<li>{{twitter}} : <?php _e("twitter share count","kuaza-post-shared-tracker"); ?></li>
		<li>{{google}} : <?php _e("google share count","kuaza-post-shared-tracker"); ?></li>
		<li>{{linkedin}} : <?php _e("linkedin share count","kuaza-post-shared-tracker"); ?></li>
		<li>{{stumbleupon}} : <?php _e("stumbleupon share count","kuaza-post-shared-tracker"); ?></li>
		<li>{{pinterest}} : <?php _e("Pinterest share count","kuaza-post-shared-tracker"); ?></li>
		<li>{{hepsi}} : <?php _e("All share count (total)","kuaza-post-shared-tracker"); ?></li>
		</ol>
		</p>
		<p>
		<?php _e('Example: (same demo)', 'kuaza-post-shared-tracker'); ?>
		<textarea class="widefat">
<!-- Before -->
<div class="paylasimcevre_bilesen" id="">

<!-- Loop / Parameters -->
<div class="clearfix arkaplan_eeeeee">
 <a href="{{permalink}}" title="{{post_title}}">
 <button class="btn btn-toplampaylasim"><i class="fa fa-share"></i> {{hepsi}}</button> {{post_title}}</a>
</div>

<!-- After -->
</div>

		</textarea>	
		</p>	
		</div>

<?php
	}

	function update( $new_instance, $old_instance="" ) {
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['text_cache'] = $new_instance['text_cache'] ? intval( $new_instance['text_cache'] ) : "60";
		$instance['kpstwgtheme'] = $new_instance['kpstwgtheme'];
		$instance['kpstwgtheme_once'] = $new_instance['kpstwgtheme_once'];
		$instance['kpstwgtheme_sonra'] = $new_instance['kpstwgtheme_sonra'];
		$instance['text_limit'] = $new_instance['text_limit'];
		$instance['cachecek_ismi'] = $instance['cachecek_ismi'] ? $instance['cachecek_ismi'] : $this->get_field_id("cachecek_ismi");
		$instance['cache_time_ismi'] = $instance['cache_time_ismi'] ? $instance['cache_time_ismi'] : $this->get_field_id("cache_time_ismi");
		$instance['select_order'] = $new_instance['select_order'] ? $new_instance['select_order'] : "ku_hepsi";

			/*
			* Bilesen yenilendiginde cacheleri sileriz
			*/
				//cache sil	
			   delete_option( $instance['cache_time_ismi'] );
			   delete_site_option( $instance['cache_time_ismi'] );
			   
			   // cache zamanini sil
			   delete_option( $instance['cachecek_ismi'] );
			   delete_site_option( $instance['cachecek_ismi'] );
			/*
			* BITIS /Bilesen yenilendiginde cacheleri sileriz
			*/

				/* sorgu sonucunu kaydedelim cache icin */
					global $wpdb;
					$table_name = $wpdb->prefix . "kuazasocialtracker";	
					$toplam_paylasim_kpst = $wpdb->get_results( "select 
					ku_postid as post_id,
					ku_facebook as facebook,
					ku_google as google,
					ku_twitter as twitter,
					ku_linkedin as linkedin,
					ku_pinterest as pinterest,
					ku_stumble as stumbleupon,
					ku_hepsi as hepsi
					FROM ".$table_name." order by ".$instance['select_order']." desc LIMIT 0,".$instance['text_limit'], ARRAY_A );

	$hepsinitopla = array();
	foreach($toplam_paylasim_kpst as $post){
	$postbilgi = get_post($post["post_id"], ARRAY_A);
	$permalink = array("permalink" =>get_permalink( $post["post_id"] ));
	$post_title = array("post_title" => $postbilgi["post_title"]);
	$comment_count = array("comment_count" => $postbilgi["comment_count"]);
	$post_author = array("post_author" => $postbilgi["post_author"]);
	$hepsinitopla[] = $post+$post_title+$permalink+$comment_count+$post_author;
	}

		if ( !empty($toplam_paylasim_kpst)  ) :
			$i = 0;
				foreach($hepsinitopla as $code => $cevir){
					$i++;
						$kspt_widget_temasi = $instance['kpstwgtheme'];

							foreach ($cevir as $code => $value){
								$kspt_widget_temasi = str_replace('{{'.$code.'}}', $value, $kspt_widget_temasi);
									$yedekson[$i] = $kspt_widget_temasi;
								}
				}
				
				add_option( $instance['cachecek_ismi'], serialize($yedekson) );
				add_option( $instance['cache_time_ismi'], current_time('mysql') );
		endif;
	/* bitis / sorgu sonucunu kaydedelim cache icin */
		return $instance;
	}

	function widget( $args, $instance ) {

	$instance['select_order'] = !empty($instance['select_order']) ? $instance['select_order'] : "ku_hepsi";
	$instance['text_cache'] = !empty($instance['text_cache']) ? $instance['text_cache'] : "60";
	$instance['text_limit'] = !empty($instance['text_limit']) ? $instance['text_limit'] : "5";
	$instance['kpstwgtheme'] = !empty($instance['kpstwgtheme']) ? $instance['kpstwgtheme'] : null;

		// bilesen guncellendiginde yada eklendiginde eklenen otomatik zaman damgasi	
		$cache_time_ismi = !empty($instance['cache_time_ismi']) ? $instance['cache_time_ismi'] : $this->get_field_id("cachecek_ismi");
		$cache_time = get_site_option($cache_time_ismi);
		
		$cachecek_ismi = !empty($instance['cachecek_ismi']) ? $instance['cachecek_ismi'] : $this->get_field_id("cachecek_ismi");
 
		// kullanicinin bileseni eklerken belirledigi cache araligi default 60 saniye
		$cachearaligi = $instance['text_cache'];

			$limitzaman = (60*$cachearaligi); // 60*1 dakika
				$suanki_zaman = strtotime(current_time('mysql'));
					$tablo_cache_zamani = strtotime($cache_time);
						$tablo_cache_zamani_yeni = $tablo_cache_zamani + $limitzaman;
	
			if($suanki_zaman > $tablo_cache_zamani_yeni){ // cache suresi bittiyse yeni cache aliriz
			global $wpdb;
			
				/* sorgu sonucunu kaydedelim cache icin */
					$table_name = $wpdb->prefix . "kuazasocialtracker";	
					$toplam_paylasim_kpst = $wpdb->get_results( "select 
					ku_postid as post_id,
					ku_facebook as facebook,
					ku_google as google,
					ku_twitter as twitter,
					ku_linkedin as linkedin,
					ku_pinterest as pinterest,
					ku_stumble as stumbleupon,
					ku_hepsi as hepsi
					FROM ".$table_name." order by ".$instance['select_order']." desc LIMIT 0,".$instance['text_limit'], ARRAY_A );

	$hepsinitopla = array();
	foreach($toplam_paylasim_kpst as $post){
	$postbilgi = get_post($post["post_id"], ARRAY_A);
	$permalink = array("permalink" =>get_permalink( $post["post_id"] ));
	$post_title = array("post_title" => $postbilgi["post_title"]);
	$comment_count = array("comment_count" => $postbilgi["comment_count"]);
	$post_author = array("post_author" => $postbilgi["post_author"]);
	$hepsinitopla[] = $post+$post_title+$permalink+$comment_count+$post_author;
	}

		if ( !empty($toplam_paylasim_kpst)  ) :
			$i = 0;
				foreach($hepsinitopla as $code => $cevir){
					$i++;
						$kspt_widget_temasi = $instance['kpstwgtheme'];

							foreach ($cevir as $code => $value){
								$kspt_widget_temasi = str_replace('{{'.$code.'}}', $value, $kspt_widget_temasi);
									$yedekson[$i] = $kspt_widget_temasi;
								}
				}
				
				update_option( $cachecek_ismi, serialize($yedekson) );
				update_option( $cache_time_ismi, current_time('mysql') );
		endif;
				
			}else{ // cache suresi bitmemis ise eski cacheden verileri cekeriz

				$yedekson = unserialize(get_site_option($cachecek_ismi));
				
			}
		
		echo $args['before_widget'];
		if ( ! empty( $instance['title'] ) ) {
			echo $args['before_title'];
			echo esc_html( $instance['title'] );
			echo $args['after_title'];
		}

?>

	<div class="kpst-counter-post">
	<?php
	if(isset($yedekson)){
		echo !empty($instance['kpstwgtheme_once']) ? $instance['kpstwgtheme_once'] : "";
		
			foreach($yedekson as $postcuk){
				echo stripslashes($postcuk);
			}
		echo !empty($instance['kpstwgtheme_sonra']) ? $instance['kpstwgtheme_sonra'] : "";
	}
	?>
	</div>

<?php
		echo $args['after_widget'];
	}
}


function kpst_counter_register_widgets() {
	register_widget( 'Kpst_Widget' );
	register_widget( 'Kpst2_Widget' );
}

add_action( 'widgets_init', 'kpst_counter_register_widgets' );
