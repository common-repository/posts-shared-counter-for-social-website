<?php
/*
Plugin Name: KS Total shared counter post
Plugin URI: http://makaleci.com/wp-konularinizin-ne-kadar-paylasildigini-gosterin-facebooktwittergoogle-v-s.html
Description: Track, Counter and Display your posts on your blog
Version: 2.3
Author: Selcuk kilic (Kuaza)
Author URI: http://kuaza.com
License: GPLv2 or later
*/

// gelistirici icindir: hatalari gormek icin (varsa) :)
//error_reporting(E_ALL); ini_set("display_errors", 1);

if ( ! defined( 'ABSPATH' ) ) exit; 

define( 'KUAZA_POST_SHARED_VER', '2.3' );
define( 'KUAZA_POST_SHARED_URI', plugin_dir_url( __FILE__ ) );
define( 'KUAZA_POST_SHARED_DIR', plugin_dir_path( __FILE__ ) );
define( 'KUAZA_POST_SHARED_PLUGIN', __FILE__ );
define( 'KUAZA_POST_SHARED_DIRNAME', dirname( plugin_basename( __FILE__ ) ) );

### Dil ozelligini aktif edelim.
### Admin giris yapmis ise bu alani yukletiriz.
if ( is_admin() ) {
		global $current_user;
			
			//Fix: http://david-coombes.com/wordpress-get-current-user-before-plugins-loaded/
			if(!function_exists('wp_get_current_user'))
				require_once(ABSPATH . "wp-includes/pluggable.php"); 
				
			wp_cookie_constants();
			$current_user = $user = wp_get_current_user();
			$user_roles = $current_user->roles;
			$kullanici_level = array_shift($user_roles);
			
			if ( $kullanici_level == "administrator" || $kullanici_level == "editor" ) {
			add_action( 'add_meta_boxes', 'kpst_add_meta_box'  );
			add_action( 'save_post', 'kpst_save_meta_options'  );
			}
			
			// kpost ayar ekleme yazi alanina
	function kpst_add_meta_box( $post_type ) {

            $post_types = array('post', 'page');     //limit meta box to certain post types
            if ( in_array( $post_type, $post_types )) {
		add_meta_box(
			'Kpst_options'
			,__( 'Kpst options', 'kuaza-post-shared-tracker' )
			,'render_kpst_konuda_goster'
			,$post_type
			,'advanced'
			,'high',
			array("kpst_konuda_goster")
		);
            }
	}



	/**
	 * Save the meta when the post is saved.
	 * 
	 * @param int $post_id The ID of the post being saved.
	 */
	function kpst_save_meta_options( $post_id ) {
	
		/*
		 * We need to verify this came from the our screen and with proper authorization,
		 * because save_post can be triggered at other times.
		 */

		// Check if our nonce is set.
		if ( ! isset( $_POST['kpst_inner_custom_box_nonce'] ) )
			return $post_id;

		$nonce = $_POST['kpst_inner_custom_box_nonce'];

		// Verify that the nonce is valid.
		if ( ! wp_verify_nonce( $nonce, 'kpst_inner_custom_box' ) )
			return $post_id;

		// If this is an autosave, our form has not been submitted,
                //     so we don't want to do anything.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) 
			return $post_id;

		// Check the user's permissions.
		if ( 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) )
				return $post_id;
	
		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) )
				return $post_id;
		}

		/* OK, its safe for us to save the data now. */

		// konuyla alakali wlops ayarlarini guncelleriz
		$kpst_data_1 = (!empty($_POST['kpst_konuda_goster']) ? sanitize_text_field( $_POST['kpst_konuda_goster'] ) : "");
		update_post_meta( $post_id, '_kpst_konuda_goster', isset($kpst_data_1) && $kpst_data_1 == "yes" ? "yes" : "no" );
	
	}


	/**
	 * konu duzenleme yada ekleme sayfasinda wlops options bolumunu gosterelim, ayiklayalim..
	 *
	 * @param WP_Post $post The post object.
	 */
	function render_kpst_konuda_goster( $post,$metabox ) {
	
		// Add an nonce field so we can check for it later.
		wp_nonce_field( 'kpst_inner_custom_box', 'kpst_inner_custom_box_nonce' );

		foreach($metabox['args'] as $kpst_meta){

			$value = get_post_meta( $post->ID, '_'.$kpst_meta, true );

			if($kpst_meta == "kpst_konuda_goster"){
			$kpst_aciklama_meta = __("Hidden this post kpst social tracker button","wlops");

				echo '<div class="yuzdeyuzyap">';
				echo '<input type="checkbox" id="'.$kpst_meta.'" name="'.$kpst_meta.'" size="25" value="yes" '.($value=="yes" ? "checked" : "").' />';
				echo '<label for="'.$kpst_meta.'" class="yuzdeyuzyap">'.$kpst_aciklama_meta.'</label></div><br>';
				
			}else{

			}

		}		

	}

		add_filter('manage_posts_columns', 'kpst_stun_ust');
		add_action('manage_posts_custom_column', 'kpst_stun_icerik', 10, 2);	

// ADD NEW COLUMN
function kpst_stun_ust($defaults) {
    $defaults['kpst_istatistikler'] = __('Kpst social tracker','wlops');
    return $defaults;
}
 
// SHOW THE FEATURED IMAGE
function kpst_stun_icerik($column_name, $post_ID) {
if ($column_name == 'kpst_istatistikler') {
        echo sayfadagoster_kpst();
    }
}
	
add_action( 'plugins_loaded', 'kpst_textdomain' );
function kpst_textdomain() {
	load_plugin_textdomain( 'kuaza-post-shared-tracker', false, KUAZA_POST_SHARED_DIRNAME."/languages" );
}
}

function kuazasocial_index__sayfa() {
	add_menu_page(__('WordPress Post social shared tracker'), __('Shared counter'), "manage_options", 'kuazasocial', 'kuazasocialadminindex');

}
add_action('admin_menu', 'kuazasocial_index__sayfa');

$kpst_ajax_status = get_option( 'kspt_enable_ajax' );
$kpst_konu_otomatik = get_option( 'kspt_konu_otomatik' );
$kspt_konu_temasi = get_option( 'kspt_konu_temasi' );
$kspt_konu_temasi_css_extra = get_option( 'kspt_konu_temasi_css' );
$kspt_enable_plugins = get_option( 'kspt_enable_plugins' );

// css var ise header kismina ekleriz.
if($kspt_enable_plugins == "yes"){
add_action('wp_head', 'kpst_header_css_ekle');
add_action('admin_head', 'kpst_header_css_ekle_admin');
function kpst_header_css_ekle(){
global $kspt_konu_temasi_css_extra;
echo stripslashes($kspt_konu_temasi_css_extra);
}

// admin icin ozel css
function kpst_header_css_ekle_admin(){
global $kspt_konu_temasi_css_extra;
?>
<link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">

<style>
.paylasimcevre{
margin-bottom:10px;
width:100%;
color:#fff
}

.btn {
  display: inline-block;
  padding: 2px 6px;
  margin: 0px;
  font-size: 14px;
  font-weight: normal;
  line-height: 1.42857143;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
  cursor: pointer;
  -webkit-user-select: none;
     -moz-user-select: none;
      -ms-user-select: none;
          user-select: none;
  background-image: none;
  border: 1px solid transparent;
  border-radius: 4px;
}
.btn:focus,
.btn:active:focus,
.btn.active:focus {
  outline: thin dotted;
  outline: 5px auto -webkit-focus-ring-color;
  outline-offset: -2px;
}
.btn:hover,
.btn:focus, .btn:visited {
  /*color: #fff;*/
  text-decoration: none;
}
.btn:active,
.btn.active {
  background-image: none;
  outline: 0;
  -webkit-box-shadow: inset 0 3px 5px rgba(0, 0, 0, .125);
          box-shadow: inset 0 3px 5px rgba(0, 0, 0, .125);
color: #fff;
}
.btn.disabled,
.btn[disabled],
fieldset[disabled] .btn {
  pointer-events: none;
  cursor: not-allowed;
  filter: alpha(opacity=65);
  -webkit-box-shadow: none;
          box-shadow: none;
  opacity: .65;
}
a.btn{
text-decoration:none;
margin: 0px;
color:#fff
}
.btn-toplampaylasim, button.btn-toplampaylasim {
	background: #ddd;
	border-radius: 0;
	color: #a9a9a9
}
.btn-toplampaylasim:link, .btn-toplampaylasim:visited {
	color: #a9a9a9
}
.btn-toplampaylasim:active, .btn-toplampaylasim:hover {
	background: #ccc;
	color: #a9a9a9
}
.btn-pinterest, button.btn-pinterest {
	background: #c8232c;
	border-radius: 0;
	color: #fff
}
.btn-pinterest:link, .btn-pinterest:visited {
	color: #fff
}
.btn-pinterest:active, .btn-pinterest:hover {
	background: #BB2128;
	color: #fff
}
.btn-twitterx, button.btn-twitterx {
	background: #00acee;
	border-radius: 0;
	color: #ffffff
}
.btn-twitterx:link, .btn-twitterx:visited {
	color: #ffffff
}
.btn-twitterx:active, .btn-twitterx:hover {
	background: #009FDE;
	color: #ffffff
}
.btn-facebook, button.btn-facebook {
	background: #3b5998;
	border-radius: 0;
	color: #fff
}
.btn-facebook:link, .btn-facebook:visited {
	color: #fff
}
.btn-facebook:active, .btn-facebook:hover {
	background: #30477a;
	color: #fff
}
.btn-googleplus, button.btn-googleplus {
	background: #e93f2e;
	border-radius: 0;
	color: #fff
}
.btn-googleplus:link, .btn-googleplus:visited {
	color: #fff
}
.btn-googleplus:active, .btn-googleplus:hover {
	background: #ba3225;
	color: #fff
}
.btn-stumbleupon, button.btn-stumbleupon {
	background: #f74425;
	border-radius: 0;
	color: #fff
}
.btn-stumbleupon:link, .btn-stumbleupon:visited {
	color: #fff
}
.btn-stumbleupon:active, .btn-stumbleupon:hover {
	background: #c7371e;
	color: #fff
}
.btn-linkedin, button.btn-linkedin {
	background: #0e76a8;
	border-radius: 0;
	color: #fff
}
.btn-linkedin:link, .btn-linkedin:visited {
	color: #fff
}
.btn-linkedin:active, .btn-linkedin:hover {
	background: #0b6087;
	color: #fff
}
/* twente twelve fix color for visited button link
.entry-content a:visited,
.comment-content a:visited {
	color: #fff;
} */


/* bilesen icin css alani */
.paylasimcevre_bilesen{
width:%100;
color:#fff
}
.paylasimcevre_bilesen a{
text-decoration:none;
}
.btn-tamgenis {
        width:100%;
}

.btn-toplampaylasim_bilesen, button.btn-toplampaylasim_bilesen{
	background: #dddddd;
	border-radius: 0;
	color: #a9a9a9;
        width:100%;
}
.btn-toplampaylasim_bilesen:link, .btn-toplampaylasim_bilesen:visited {
	color: #a9a9a9
}
.btn-toplampaylasim_bilesen:active, .btn-toplampaylasim_bilesen:hover {
	background: #ccc;
	color: #a9a9a9
}
.arkaplan_eeeeee {
	background: #eeeeee;
	border-radius: 0;
	color: #a9a9a9;
}
.arkaplan_ffffff {
	background: #ffffff;
	border-radius: 0;
	color: #a9a9a9;
}

.color_222222, .color_222222 a{
color:#222222
}
.kpst_social_counter_ajax{
display:inline-block;
}
.yukleniyorikonu{
display:inline-block;
float:left;
border:none;
}
.yukleniyorikonu img{
border:none;
}
</style>
<?php
}
}

// islemleri yonlendiren fonksiyonumuz..		
function kuazasocialadminindex(){
$islem = isset($_GET["islem"]) ? $_GET["islem"] : "";

switch($islem):

	case 'icerikleriguncelle':
	kuaza_social_icerikleriguncelle();
	break;
	
	case 'ayarlariguncelle':
	kuaza_social_ayarlariguncelle();
	break;
	/* sonraki versiyonlar icin
	case 'tablosil':
	echo "tablosil ffddfdfddf";
	break;
	
	case 'sayaclariguncelle':
	echo "sayaclariguncelle dfdf";
	break;
	*/	
	default;
		kuaza_social_index();
	break;
endswitch;
}


/*
 * @desc	kuaza social / index Sayfası
 * @author	selcuk kilic
*/
function kuaza_social_index(){
echo kuaza_social_menuolustur();
?>

<div>

<div style="margin-right:30px;padding-right:10px;float:left;border-right:1px solid #ccc;width:30%">
<?php _e("<h3>Plugin info</h3>","kuaza-post-shared-tracker"); ?>
<?php _e("<strong>Plugin name:</strong> Social website posts Shared counter","kuaza-post-shared-tracker"); ?><br /><br />
<?php _e("<strong>Plugin description:</strong> All posts how many shared social web site (facebook, twitter, google+,linkedin,pinterest,stumbleupon and total) counter display and save.","kuaza-post-shared-tracker"); ?><br /><br />
<?php _e("<strong>Plugin version:</strong> v1.0 (first version)","kuaza-post-shared-tracker"); ?><br /><br />
<?php _e("<strong>Plugin Release and support:</strong> 10/07/2014 / <a href='http://makaleci.com/wp-konularinizin-ne-kadar-paylasildigini-gosterin-facebooktwittergoogle-v-s.html' target='_blank'>Release and support page</a>","kuaza-post-shared-tracker"); ?><br /><br />
<?php _e("<strong>Plugin author:</strong> Selcuk kilic (kuaza)","kuaza-post-shared-tracker"); ?><br /><br />
<?php _e("<strong>Plugin Widget support:</strong> Yes 2 widget support (like this)","kuaza-post-shared-tracker"); ?><br /><br />
<?php _e("<strong>Plugin support:</strong> <a href='http://makaleci.com/wp-konularinizin-ne-kadar-paylasildigini-gosterin-facebooktwittergoogle-v-s.html' target='_blank'>Support plugins release page (comments)</a> or this email: kuaza.ca@gmail.com","kuaza-post-shared-tracker"); ?><br /><br />
<?php _e("<strong>Author social profiles:</strong>","kuaza-post-shared-tracker"); ?><br />
<a href="https://www.facebook.com/kuaza.ca" target="_blank">Facebook</a>,
<a href="https://plus.google.com/u/0/+Kuaza61" target="_blank">Google</a>,
<a href="https://twitter.com/kuaza" target="_blank">Twitter</a>,
<a href="https://www.linkedin.com/profile/view?id=111819421&trk=nav_responsive_tab_profile" target="_blank">LinkedIn</a>,
<a href="http://piclect.com/kuaza" target="_blank">Piclect</a>
</div>

<div style="margin-right:30px;padding-right:10px;float:left;border-right:1px solid #ccc;width:30%">
<?php _e("<h3>Donate for support</h3>","kuaza-post-shared-tracker"); ?>
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="RJVUX7HSHHHMG">
<input type="image" src="https://www.paypalobjects.com/en_US/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
<img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
</form>
<hr />
<form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
<input type="hidden" name="cmd" value="_s-xclick">
<input type="hidden" name="hosted_button_id" value="JR2BL8Y7QU2P8">
<input type="image" src="https://www.paypalobjects.com/tr_TR/TR/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - Online ödeme yapmanın daha güvenli ve kolay yolu!">
<img alt="" border="0" src="https://www.paypalobjects.com/fr_FR/i/scr/pixel.gif" width="1" height="1">
</form>

<?php _e("<h4>Thanks for support donation..</h4>","kuaza-post-shared-tracker"); ?>

<?php _e("<h3><em>Please support add my project link your website</em></h3>","kuaza-post-shared-tracker"); ?>

<?php _e("<h3>My other project</h3>","kuaza-post-shared-tracker"); ?>
<?php _e("<a href='http://piclect.com'>Image upload, create collections and share</a>","kuaza-post-shared-tracker"); ?><hr />
<?php _e("<a href='http://makaleci.com'>Latest news and articles. (Turkish)</a>","kuaza-post-shared-tracker"); ?><hr />
<?php _e("<a href='http://www.ressim.net'>Only image upload and shared. Simple and basic</a>","kuaza-post-shared-tracker"); ?>
</div>

<div style="float:left;width:30%">
<?php _e("<h3>Change log</h3>","kuaza-post-shared-tracker"); ?>
<?php _e("Please look plugin page","kuaza-post-shared-tracker"); ?>
</div>

</div>


<?php
}


/*
 * menu olusturma
 *
*/
function kuaza_social_menuolustur(){
?>
<a href="<?php echo get_site_url(); ?>/wp-admin/edit.php?page=kuazasocial">
<?php _e("<h3>Plugins index</h3>","kuaza-post-shared-tracker"); ?>
<a href="<?php echo get_site_url(); ?>/wp-admin/edit.php?page=kuazasocial&islem=ayarlariguncelle">
<?php _e("<h3>Settings</h3>","kuaza-post-shared-tracker"); ?>
</a> <a href="<?php echo get_site_url(); ?>/wp-admin/edit.php?page=kuazasocial&islem=icerikleriguncelle">
<?php _e("<h3>Re-generator istatistik</h3>","kuaza-post-shared-tracker"); ?>
</a>
<hr />
<?php
}


 
 /**
 * Bir icerik silindiginde ona ait olan tabloda silinir.
 */
function kuaza_post_shared_counter_sil( $post_id = '' ){
	global $wpdb;
	$table_name = $wpdb->prefix . "kuazasocialtracker";
	
	if( empty( $post_id ) && isset( $GLOBALS['post'] ) ){
		$post_id = $GLOBALS['post'];
		$post_id = $post_id->ID;
	}
	
	if ( $wpdb->get_var( $wpdb->prepare( 'SELECT ku_postid FROM '.$table_name.' WHERE ku_postid = %d', $post_id ) ) ) {
        return $wpdb->query( $wpdb->prepare( 'DELETE FROM '.$table_name.' WHERE ku_postid = %d', $post_id ) );
    }
    return true;
	
	return $wpdb->delete( $table_name, array( 'ku_postid' => $post_id ) );
}

###
###
###
### Ajax ile gosterme acik ise burdaki alan aktif olur.
###
###
###
###
if(isset($kpst_ajax_status) && $kpst_ajax_status == "yes" && $kspt_enable_plugins == "yes"){
### ajax ile gosterme alani
add_action('wp_enqueue_scripts', 'wp_kpst_cache_count_enqueue');
function wp_kpst_cache_count_enqueue() {
	global $user_ID, $posts,$post;
	
			$kpst_settieout = get_option( 'kspt_settimetime' );
				wp_enqueue_script( 'wp-kuaza-social-counter-JS', plugins_url( 'kuaza_kpst_cache.js', __FILE__ ), array( 'jquery' ), '1.0', false );
	
	$postidcik = array();
	foreach($posts as $postcuk){
	$postidcik[] = $postcuk->ID;		
	}

	$parcala = json_encode($postidcik);
	wp_localize_script( 'wp-kuaza-social-counter-JS', 'gosterjskpst', array( 'admin_ajax_url' => admin_url( 'admin-ajax.php', ( is_ssl() ? 'https' : 'http' ) ), 'post_id' => $parcala, 'settimeouttime' => $kpst_settieout, 'eklentiyolu' => KUAZA_POST_SHARED_URI ) );
	
	
	}
	
	add_action( 'wp_ajax_kpst_social_counter_ajax', 'sayfadagoster_kpst_ajax' );
	add_action( 'wp_ajax_nopriv_kpst_social_counter_ajax', 'sayfadagoster_kpst_ajax' );
	function sayfadagoster_kpst_ajax() {
		global $kpst_ajax_status;

		if( empty( $_GET['post_id'] ) )
			return;

		if( isset( $kpst_ajax_status ) && $kpst_ajax_status == "no" )
			return;

		$post_id = intval( $_GET['post_id'] );

		if( $post_id > 0 ) {
			$sonucgoster_kpst = kuaza_post_shared_counter($post_id);
			echo sayfadagoster_kpst(false,$sonucgoster_kpst);
			exit();
		}
	}
### ajax ile gosterme alani /bitis
}
###
###
###
###
###
###
###
###



/*
*
*
paylasim ikonlarini otomatik olarak yazi ustune yada altina ekler.
*
*
*/
if($kpst_konu_otomatik == "yes" && $kspt_enable_plugins == "yes"){

	add_filter('the_content', 'kpst_yazi_ici_otomatik');
	function kpst_yazi_ici_otomatik($content){
	global $post;
	
	$kpst_gosterilmesin_mi = get_post_meta( $post->ID, '_kpst_konuda_goster', true );
	
	if($kpst_gosterilmesin_mi == "yes")
	return $content;
	
	$kpst_konudagoster_neresi = get_option( 'kspt_konudagoster_neresi' );
	$kpst_sayfagosterme = sayfadagoster_kpst();

 
		if($kpst_konudagoster_neresi == "cont_before"){
			return $kpst_sayfagosterme."
			".$content;
		}
		
		if($kpst_konudagoster_neresi == "cont_after"){
			return $content."
			".$kpst_sayfagosterme;
		}
		
	return $content;
	
	}
}	
//bitis
	


// nasil goruntulenecegini belirleyen fonksiyon
// sonucun array seklinde donmesini isterseniz: sayfadagoster_kpst(true);
function sayfadagoster_kpst($arrayiste=false,$arrayvarsa = '') {
global $kpst_ajax_status, $kspt_konu_temasi, $kspt_enable_plugins, $post;

if($kspt_enable_plugins != "yes" && (!empty($_POST['post_status']) && $_POST['post_status'] != 'publish') )
return false;

	$kpst_gosterilmesin_mi = get_post_meta( $post->ID, '_kpst_konuda_goster', true );
	
	if($kpst_gosterilmesin_mi == "yes")
	return false;

if(!empty($arrayvarsa) && is_array($arrayvarsa)){
$kspt_konu_temasi_yedek = $kspt_konu_temasi;

	$gelsinnevarsaarray = $arrayvarsa;
	$kpst_degiskenler = $gelsinnevarsaarray["toplam"];

		// wlops varmi yokmu kontrol eder, yoksa tema icerisinden cikartiriz
		if(empty($kpst_degiskenler["wlops_mu_acaba"]) || $kpst_degiskenler["wlops_mu_acaba"] == "0"){
			$kspt_konu_temasi_yedek = preg_replace( '|<a.*>{{wlops_out_click}}.*</a>|i', '', $kspt_konu_temasi_yedek );
				}
				
		if(!$arrayiste){
			if ( !empty($kpst_degiskenler) && is_array($kpst_degiskenler) ) :

			foreach ($kpst_degiskenler as $code => $value)
				$kspt_konu_temasi_yedek = str_replace('{{'.$code.'}}', $value, $kspt_konu_temasi_yedek);
			
			endif;
		}
	return !$arrayiste ? stripslashes($kspt_konu_temasi_yedek) : $kpst_degiskenler;
}

// ajax gosterme alani
if($kpst_ajax_status == "yes" && !is_admin()){
$postidcik = !empty($post->ID) ? $post->ID : "0";
return '<div class="kpst_social_counter_ajax" id="kpst_social_counter_'.$postidcik.'"></div>';

}else{

$kspt_konu_temasi_yedek = $kspt_konu_temasi;

	$gelsinnevarsa = kuaza_post_shared_counter();
	$kpst_degiskenler = $gelsinnevarsa["toplam"];
	
		// wlops varmi yokmu kontrol eder, yoksa tema icerisinden cikartiriz
		if(empty($kpst_degiskenler["wlops_mu_acaba"]) || $kpst_degiskenler["wlops_mu_acaba"] == "0"){
			$kspt_konu_temasi_yedek = preg_replace( '|<a.*>{{wlops_out_click}}.*</a>|i', '', $kspt_konu_temasi_yedek );
				}
		
		
		
		if(!$arrayiste){
			if ( !empty($kpst_degiskenler) && is_array($kpst_degiskenler) ) :

			foreach ($kpst_degiskenler as $code => $value)
				$kspt_konu_temasi_yedek = str_replace('{{'.$code.'}}', $value, $kspt_konu_temasi_yedek);
			
			endif;
			  
		}
 
	return !$arrayiste ? stripslashes($kspt_konu_temasi_yedek) : $kpst_degiskenler;

}

}

/**
 * verilen linkin gecerli olup olmadigini kontrol etmek icin ugrasir :)
 */
function kps_url_kontrol($link)
	{
		if(empty($link))
		return false;
		
			if (@fclose(@fopen( $link,  "r "))) {
			 return true;
			} else {
			 return false;
			}

	}
	
/**
 * konu icerisinde gostermek icin kullanabileceginiz fonksiyon: sayfadagoster_kpst()
 * konuya girildiginde tablo islemleri yapar - guncelleme, yoksa olusturma, varsa ve cache dolmamisa
 * konuya girildiginde otomatik olusturmasi icin tabiki eklenmis olmasi lazim yukaridaki kodun, yada otomatik eklenmesi lazim.
 */
function kuaza_post_shared_counter( $post_id = '' ){
global $wpdb, $post, $kspt_enable_plugins;

	if($kspt_enable_plugins != "yes" && (!empty($_POST['post_status']) && $_POST['post_status'] != 'publish') )
	return false;
	
	$table_name = $wpdb->prefix . "kuazasocialtracker";	
	
	if( empty( $post_id ) && isset( $GLOBALS['post'] ) ){
		$post = $GLOBALS['post'];
		$post_id = $post->ID;
	}

		// paylasim istatistiklerini arama motorlarindan gizlesin mi ? yes/no
		
		if(get_option( 'kspt_disable_search' ) == "yes" && (isset($_GET["action"]) && $_GET["action"] != "kpst_social_counter_ajax")) {
			// fix for publish and update post
			if(!empty($_POST['post_status']) && ( $_POST['post_status'] == 'publish' )){
			$supported = true;
			
			}else{
				$bots = array( 	'wordpress', 'googlebot', 'google', 'msnbot', 'ia_archiver', 'lycos', 'jeeves', 'scooter', 'fast-webcrawler', 'slurp@inktomi', 'turnitinbot', 'technorati', 'yahoo', 'findexa', 'findlinks', 'gaisbo', 'zyborg', 'surveybot', 'bloglines', 'blogsearch', 'pubsub', 'syndic8', 'userland', 'gigabot', 'become.com' );

				$supported = !empty( $_SERVER['HTTP_USER_AGENT'] ) 
					&& is_singular( 'post' ) 
					&& !preg_match( '/' . implode( '|', $bots ) . '/i', $_SERVER['HTTP_USER_AGENT'] );
			}
		}else{
		$supported = true;
		}
	
	if( $supported ){
 
	$paylasimbilgileri = $wpdb->get_row( "SELECT * FROM ".$table_name." where ku_postid='".$post_id."'", ARRAY_A );
	$wlops_direk_link_kontrol = get_post_meta( $post_id, '_wlops_link', true ); // i love you kuaza :)
	$sayfalinkipermalink = get_permalink( $post_id );
	
	
	// wlops cikis linki varsa bu sayfa ait tracker bilgilerini almak icin linki yapilandiririz.
	// link gecerlilik kontrolu yapmak istersen:
	// kps_url_kontrol($wlops_direk_link_kontrol)
	// ancak bu sayfanin gec yuklenmesine neden oluyor. Cunku her konu icin ayri kontrol ve zaman aliyor.
	// bu loop da oluyor, tekli sayfalarda sorun olmuyor..
	if($wlops_direk_link_kontrol){
	
	$wlops_linki_cikis = $wlops_direk_link_kontrol;
	$wlops_cikis_linki = add_query_arg( 'wlops_out', $post_id, $sayfalinkipermalink );//get_site_url().'/?wlops_out='.$post_id;
	$wlops_cikis_sayisi = get_post_meta( $post_id, '_wlops_views', true ); // linke tiklayarak kac kere sayfa ziyaret edilmis?
	$wlops_mu_acaba = "1";
	// eger wlops cikis linki yoksa normal sayfa linkini yapilandiririz.
	}else{
	
	$wlops_linki_cikis = $sayfalinkipermalink;
	$wlops_cikis_linki = $sayfalinkipermalink;
	$wlops_cikis_sayisi = "";
	$wlops_mu_acaba = "0";
	}
	
	// fix: wlops da singledeki konu basligini degistirip link ekledigimiz icin normal baslik cekme fonksiyonu da degisiyor, 
	// bu yuzden bu kisim degistirilmistir.
	$postcuk = get_post($post_id); 
	$sayfalinkititle = $postcuk->post_title;
	
	if(!$paylasimbilgileri){ // Daha onceden konuya ait bir sosyal paylasim tablosu olusturulmamis ise
			/* konuya ait sosyal paylasim sayilarini cekiyoruz */
						include_once("class.sharecount.php");
						$obj=new shareCount_kpst($wlops_linki_cikis);  // konu linki
						$get_tweets = $obj->get_tweets(); //tweet sayisi
						$get_fb = $obj->get_fb(); //facebook toplam sayi (likes+shares+comments)
						$get_linkedin = $obj->get_linkedin(); //linkedin paylasim
						$get_plusones = $obj->get_plusones(); //google plus
						$get_stumble = $obj->get_stumble(); //Stumbleupon goruntulenme
						$get_pinterest = $obj->get_pinterest(); //pinterest pin
						$get_hepsi = ($get_fb+$get_tweets+$get_plusones+$get_linkedin+$get_stumble+$get_pinterest); //hepsinin toplami

		$icerik = array(
		'ku_postid' => $post_id,
		'ku_cache_time' => current_time('mysql'),
		'ku_time' => current_time('mysql'),
		'ku_cache_time' => current_time('mysql'),
		'ku_facebook' => $get_fb,
		'ku_twitter' => $get_tweets,
		'ku_google' => $get_plusones,
		'ku_linkedin' => $get_linkedin,
		'ku_stumble' => $get_stumble,
		'ku_pinterest' => $get_pinterest,
		'ku_hepsi' => $get_hepsi		
		);
	
		$tabloolustur = kspt_post_tracker_olustur($icerik);
			
			if($tabloolustur){ //tablo eklendiyse linke ait yeni paylasim sayilarini dondeririz.

					$array_sonuc["toplam"] = array(
					"facebook" =>$get_fb,
					"twitter" =>$get_tweets,
					"google" =>$get_plusones,
					"linkedin" =>$get_linkedin,
					"stumbleupon" =>$get_stumble,
					"pinterest" =>$get_pinterest,
					"hepsi" =>$get_hepsi,
					'post_id' => $post_id,
					'post_url' => $sayfalinkipermalink,
					'post_title' => $sayfalinkititle,
					'wlops_direkt_url' => $wlops_linki_cikis,
					'wlops_out_url' => $wlops_cikis_linki,
					'wlops_mu_acaba' => $wlops_mu_acaba,
					'wlops_out_click' => $wlops_cikis_sayisi
					);
					return $array_sonuc;
			}else{ // tablo eklemede sorun cikarsa guncel verileri yansitiriz sonuclara
					$array_sonuc["toplam"] = array(
					"facebook" =>$get_fb,
					"twitter" =>$get_tweets,
					"google" =>$get_plusones,
					"linkedin" =>$get_linkedin,
					"stumbleupon" =>$get_stumble,
					"pinterest" =>$get_pinterest,
					"hepsi" =>$get_hepsi,
					'post_id' => $post_id,
					'post_url' => $sayfalinkipermalink,
					'post_title' => $sayfalinkititle,
					'wlops_direkt_url' => $wlops_linki_cikis,
					'wlops_out_url' => $wlops_cikis_linki,
					'wlops_mu_acaba' => $wlops_mu_acaba,
					'wlops_out_click' => $wlops_cikis_sayisi
					);
					return $array_sonuc;
			}
			
	}else{ // tablo varsa kontrol kismina geciyoruz. belliki daha once tablo olusturulmus :) cache kontrol islemide var asagida.

		$cache_araligi_dakika = get_option( 'kspt_cache_time' );
			$limitzaman = (60*$cache_araligi_dakika); // 60*1 dakika
				$suanki_zaman = strtotime(current_time('mysql'));
					$tablo_cache_zamani = strtotime($paylasimbilgileri["ku_cache_time"]);
						$tablo_cache_zamani_yeni = $tablo_cache_zamani + $limitzaman;
		
			if($tablo_cache_zamani_yeni > $suanki_zaman){ // cache suresi bitmediyse db deki verileri listeleriz
			
					$array_sonuc["toplam"] = array(
					"facebook" =>$paylasimbilgileri["ku_facebook"],
					"twitter" =>$paylasimbilgileri["ku_twitter"],
					"google" =>$paylasimbilgileri["ku_google"],
					"linkedin" =>$paylasimbilgileri["ku_linkedin"],
					"stumbleupon" =>$paylasimbilgileri["ku_stumble"],
					"pinterest" =>$paylasimbilgileri["ku_pinterest"],
					"hepsi" =>$paylasimbilgileri["ku_hepsi"],
					'post_id' => $post_id,
					'post_url' => $sayfalinkipermalink,
					'post_title' => $sayfalinkititle,
					'wlops_direkt_url' => $wlops_linki_cikis,
					'wlops_out_url' => $wlops_cikis_linki,
					'wlops_mu_acaba' => $wlops_mu_acaba,
					'wlops_out_click' => $wlops_cikis_sayisi
					);
				return $array_sonuc;
			
			}else{ //  cache suresi bittiyse tekrar kontrol ederiz ve db yi guncelleriz.

						/* konuya ait sosyal paylasim sayilarini cekiyoruz */
						include_once("class.sharecount.php");
						$obj=new shareCount_kpst($wlops_linki_cikis);  // $wlops_linki_cikis
						$get_tweets = $obj->get_tweets(); //to get tweets
						$get_fb = $obj->get_fb(); //to get facebook total count (likes+shares+comments)
						$get_linkedin = $obj->get_linkedin(); //to get linkedin shares
						$get_plusones = $obj->get_plusones(); //to get google plusones
						$get_stumble = $obj->get_stumble(); //to get Stumbleupon views
						$get_pinterest = $obj->get_pinterest(); //to get pinterest pins
						$get_hepsi = ($get_fb+$get_tweets+$get_plusones+$get_linkedin+$get_stumble+$get_pinterest); //to get pinterest pins
								
					$icerik_guncelle = array(
					'ku_cache_time' => current_time('mysql'),
					'ku_facebook' => $get_fb,
					'ku_twitter' => $get_tweets,
					'ku_google' => $get_plusones,
					'ku_linkedin' => $get_linkedin,
					'ku_stumble' => $get_stumble,
					'ku_pinterest' => $get_pinterest,
					'ku_hepsi' => $get_hepsi
					);
 
				$tablo_guncelle = $wpdb->update( $table_name, $icerik_guncelle, array( 'ku_postid' => $post_id ) );
				
				if($tablo_guncelle){ // guncelleme basarili olursa yeni bilgileri dondeririz.
				
					$array_sonuc["toplam"] = array(
					"facebook" =>$get_fb,
					"twitter" =>$get_tweets,
					"google" =>$get_plusones,
					"linkedin" =>$get_linkedin,
					"stumbleupon" =>$get_stumble,
					"pinterest" =>$get_pinterest,
					"hepsi" =>$get_hepsi,
					'post_id' => $post_id,
					'post_url' => $sayfalinkipermalink,
					'post_title' => $sayfalinkititle,
					'wlops_direkt_url' => $wlops_linki_cikis,
					'wlops_out_url' => $wlops_cikis_linki,
					'wlops_mu_acaba' => $wlops_mu_acaba,
					'wlops_out_click' => $wlops_cikis_sayisi
					);
					return $array_sonuc;
					
				}else{ // yeni bilgileri eklerken sorun cikarsa DB dekileri gosteririz.
				
					$array_sonuc["toplam"] = array(
					"facebook" =>$paylasimbilgileri["ku_facebook"],
					"twitter" =>$paylasimbilgileri["ku_twitter"],
					"google" =>$paylasimbilgileri["ku_google"],
					"linkedin" =>$paylasimbilgileri["ku_linkedin"],
					"stumbleupon" =>$paylasimbilgileri["ku_stumble"],
					"pinterest" =>$paylasimbilgileri["ku_pinterest"],
					"hepsi" =>$paylasimbilgileri["ku_hepsi"],
					'post_id' => $post_id,
					'post_url' => $sayfalinkipermalink,
					'post_title' => $sayfalinkititle,
					'wlops_direkt_url' => $wlops_linki_cikis,
					'wlops_out_url' => $wlops_cikis_linki,
					'wlops_mu_acaba' => $wlops_mu_acaba,
					'wlops_out_click' => $wlops_cikis_sayisi
					);
					return $array_sonuc;
			
				}
			
			}
	
	}

	}
	
	return false;
}


/*
 * ayarlari duzeltme Sayfası
 * 
*/
function kuaza_social_ayarlariguncelle(){

echo kuaza_social_menuolustur();

if(isset($_POST["ayarlariguncelle"]) && $_POST["ayarlariguncelle"] == "evet"){

$cachetime_kpst = $_POST["cachetimefor_kpst"];
$searchfordisable_kpst = !empty($_POST["disabeaearch_kpst"]) ? "yes" : "no";
$enableplugins_kpst = !empty($_POST["enableeklenti_kpst"]) ? "yes" : "no";
$ajaxyes_kpst = !empty($_POST["eklentiajax_kpst"]) ? "yes" : "no";
$settimetime_kpst = $_POST["settimetimefor_kpst"];
$kspt_konu_otomatik = !empty($_POST["kspt_konu_otomatik"]) ? "yes" : "no";
$kspt_konudagoster_neresi = $_POST["kspt_konudagoster_neresi"];
$kspt_konu_temasi = $_POST["kspt_konu_temasi"];
$kspt_konu_temasi_css = $_POST["kspt_konu_temasi_css"];

update_option( "kspt_cache_time", $cachetime_kpst );
update_option( "kspt_disable_search", $searchfordisable_kpst );
update_option( "kspt_enable_plugins", $enableplugins_kpst );
update_option( "kspt_enable_ajax", $ajaxyes_kpst );
update_option( "kspt_settimetime", $settimetime_kpst );
update_option( "kspt_konu_otomatik", $kspt_konu_otomatik );
update_option( "kspt_konudagoster_neresi", $kspt_konudagoster_neresi );
update_option( "kspt_konu_temasi", $kspt_konu_temasi );
update_option( "kspt_konu_temasi_css", $kspt_konu_temasi_css );

echo "<div style='color:green'>".__('Update settings for plugins')."</div>";
}

?>


<table class="form-table">
<form method="POST" action="">
<tr valign="top">
	<th><label for="enableeklenti_kpst"><?php _e("Enable plugin","kuaza-post-shared-tracker"); ?></label></th>
	<td><input type="checkbox" id="enableeklenti_kpst" name="enableeklenti_kpst" <?php if(get_option( 'kspt_enable_plugins' ) == 'yes'){ echo "checked='checked'"; }; ?>/></td>
<td><?php _e("Default 'yes'","kuaza-post-shared-tracker"); ?></td>
</tr>


<tr valign="top">
	<th><label for="cachetimefor_kpst"><?php _e("Cache time for check (minutes)","kuaza-post-shared-tracker"); ?></label></th>
	<td><input id="cachetimefor_kpst" name="cachetimefor_kpst" type="text" value="<?php echo get_option( 'kspt_cache_time' ); ?>" /></td>
<td><?php _e("Default 60 minutes (1 hours)","kuaza-post-shared-tracker"); ?></td>
</tr>

<tr valign="top">
	<th><label for="disabeaearch_kpst"><?php _e("Disable for search bot counter","kuaza-post-shared-tracker"); ?></label></th>
	<td><input type="checkbox" id="disabeaearch_kpst" name="disabeaearch_kpst" <?php if(get_option( 'kspt_disable_search' ) == 'yes'){ echo "checked='checked'"; }; ?>/></td>
<td><?php _e("Default 'yes'","kuaza-post-shared-tracker"); ?></td>
</tr>

<tr valign="top">
	<th><label for="eklentiajax_kpst"><?php _e("Ajax show","kuaza-post-shared-tracker"); ?></label></th>
	<td><input type="checkbox" id="eklentiajax_kpst" name="eklentiajax_kpst" <?php if(get_option( 'kspt_enable_ajax' ) == 'yes'){ echo "checked='checked'"; }; ?>/></td>
<td><?php _e("Settimeout default '1000' (seconds)","kuaza-post-shared-tracker"); ?> <input id="settimetimefor_kpst" name="settimetimefor_kpst" type="text" value="<?php echo get_option( 'kspt_settimetime' ); ?>" /></td>
</tr>

<tr valign="top">
	<th><label for="kspt_konu_otomatik"><?php _e("Auto added post or v.s","kuaza-post-shared-tracker"); ?></label></th>
	<td><input type="checkbox" id="kspt_konu_otomatik" name="kspt_konu_otomatik" <?php if(get_option( 'kspt_konu_otomatik' ) == 'yes'){ echo "checked='checked'"; }; ?>/></td>
<td><?php _e("select where? default 'content Before'","kuaza-post-shared-tracker"); ?> 

<select id="kspt_konudagoster_neresi" name="kspt_konudagoster_neresi">
<option value="cont_before" <?php if(get_option( 'kspt_konudagoster_neresi' ) == 'cont_before'){ echo "selected"; }; ?>><?php _e("Content before","kuaza-post-shared-tracker"); ?></option>
<option value="cont_after" <?php if(get_option( 'kspt_konudagoster_neresi' ) == 'cont_after'){ echo "selected"; }; ?>><?php _e("Content after","kuaza-post-shared-tracker"); ?></option>
</select>
</td>
</tr>


<tr valign="top">
	<th><label for="kspt_konu_temasi"><?php _e("Template for custom","kuaza-post-shared-tracker"); ?></label></th>
	<td>
	1
	<textarea id="kspt_konu_temasi" name="kspt_konu_temasi" cols="45" rows="8"><?php echo stripslashes(get_option( 'kspt_konu_temasi' )); ?></textarea>
	</td>
<td><?php _e("Templates label:","kuaza-post-shared-tracker"); ?>
<ol>

<li>{{facebook}} : <?php _e("facebook share count.","kuaza-post-shared-tracker"); ?></li>
<li>{{twitter}} : <?php _e("twitter share count.","kuaza-post-shared-tracker"); ?></li>
<li>{{google}} : <?php _e("google share count.","kuaza-post-shared-tracker"); ?></li>
<li>{{linkedin}} : <?php _e("linkedin share count.","kuaza-post-shared-tracker"); ?></li>
<li>{{stumbleupon}} : <?php _e("stumbleupon share count.","kuaza-post-shared-tracker"); ?></li>
<li>{{pinterest}} : <?php _e("Pinterest share count.","kuaza-post-shared-tracker"); ?></li>
<li>{{hepsi}} : <?php _e("All share count (total).","kuaza-post-shared-tracker"); ?></li>
<li>{{post_id}} : <?php _e("post ID.","kuaza-post-shared-tracker"); ?></li>
<li>{{post_url}} : <?php _e("post url for link.","kuaza-post-shared-tracker"); ?></li>
<li>{{post_title}} : <?php _e("post title for link.","kuaza-post-shared-tracker"); ?></li>
<li>{{wlops_direkt_url}} : <?php _e("Wlops Direct link.","kuaza-post-shared-tracker"); ?></li>
<li>{{wlops_out_url}} : <?php _e("Wlops out link.","kuaza-post-shared-tracker"); ?></li>
<li>{{wlops_out_click}} : <?php _e("Wlops out click number.","kuaza-post-shared-tracker"); ?></li>

</ol></td>
</tr>



<tr valign="top">
	<th><label for="kspt_konu_temasi_css"><?php _e("Extra CSS/JS codes (add head)","kuaza-post-shared-tracker"); ?></label></th>
	<td>
	
	2
	<textarea id="kspt_konu_temasi_css" name="kspt_konu_temasi_css" cols="45" rows="8"><?php echo stripslashes(get_option( 'kspt_konu_temasi_css' )); ?></textarea>
	
	
	</td>
<td><?php _e("extra css/js or other codes added header","kuaza-post-shared-tracker"); ?></td>
</tr>

<tr>
<td>
<input id="ayarlariguncelle" name="ayarlariguncelle" type="hidden" value="evet" />
<p class="submit"><input type="submit" class="button-primary" value="<?php _e("Update settings","kuaza-post-shared-tracker"); ?>" /></p>
</form>
</td>
</tr>

<tr valign="top">
	<th><hr /></th>
	<td><hr /></td>
<td><hr /></td>
</tr>

<tr valign="top">
<th><?php _e("Manuel code for themes","kuaza-post-shared-tracker"); ?></th>
	<td>
<textarea cols="45" rows="8">
if(function_exists('sayfadagoster_kpst')){
echo sayfadagoster_kpst();
}
</textarea>
</td>
	<td>
<textarea cols="45" rows="8">
/**/
if(function_exists('sayfadagoster_kpst')){

//1. parametre: array olsunmu? true evet, false hayir (default) - false olursa adminden belirlediginiz tema gosterilir.
// ajax ile upload ozelligi acik ise array donderme aktif olamaz. 
// kodlarda degisiklik yapmak isterseniz adminden eklenti ayarlari kismina girerek tema kismini duzenleyebilirsiniz.
// Not 2: Otomatik ekle secenegini kullanmazsaniz asagidaki kodlari kullanmaniz ve istediginiz yere eklemeniz gerekli. (loop icinde yada tekil yazi sayfasina)
$manuelarray = sayfadagoster_kpst();
echo $manuelarray; // or array: var_dump($manuelarray);
}
</textarea>
</td>

</tr>

<tr valign="top">
	<th><hr /></th>
	<td><hr /></td>
<td><hr /></td>
</tr>
<tr valign="top">
	<th><?php _e("DEMO","kuaza-post-shared-tracker"); ?>
	<a href='http://piclect.com/174669' target='_blank' title='screenshot-4'><img src='http://sv102.piclect.com/4905be465/m/14/09/24/screenshot-4.png' alt='screenshot-4' /></a>
</th>
	<td>
	
	<label for="kspt_konu_temasi_tema_demo">1 - <?php _e("Example templates (same demo)","kuaza-post-shared-tracker"); ?></label><br>
	<textarea id="kspt_konu_temasi_tema_demo" cols="45" rows="8"><div class="paylasimcevre" id="">

<!-- total shared -->
<button class="btn btn-toplampaylasim"><i class="fa fa-share"></i> {{hepsi}}</button>

<!-- Twitter -->
<a href="https://twitter.com/intent/tweet?url={{post_url}}&title={{post_title}}" title="Share on Twitter" target="_blank" class="btn btn-twitterx"><i class="fa fa-twitter"></i> {{twitter}}</a>

 <!-- Facebook -->
<a href="https://www.facebook.com/sharer/sharer.php?u={{post_url}}" title="Share on Facebook" target="_blank" class="btn btn-facebook"><i class="fa fa-facebook"></i> {{facebook}}</a>

<!-- Google+ -->
<a href="https://plus.google.com/share?url={{post_url}}" title="Share on Google+" target="_blank" class="btn btn-googleplus"><i class="fa fa-google-plus"></i> {{google}}</a>

<!-- StumbleUpon -->
<a href="http://www.stumbleupon.com/submit?url={{post_url}}" title="Share on StumbleUpon" target="_blank" data-placement="top" class="btn btn-stumbleupon"><i class="fa fa-stumbleupon"></i> {{stumbleupon}}</a>
<!-- LinkedIn --> 
<a href="http://www.linkedin.com/shareArticle?mini=true&url={{post_url}}&title={{post_title}}&summary=" title="Share on LinkedIn" target="_blank" class="btn btn-linkedin"><i class="fa fa-linkedin"></i> {{linkedin}}</a>

<!-- pinterest --> 
<a href="#" title="Share on LinkedIn" target="_blank" class="btn btn-pinterest"><i class="fa fa-pinterest"></i> {{pinterest}}</a>

<!-- direkt cikis sayisi : toplam_cikis_sayisi mecburi.. --> 
<a id="toplam_cikis_sayisi" href="{{wlops_out_url}}" title="View more.." target="_blank" class="btn btn-toplampaylasim">{{wlops_out_click}} <i class="fa fa-sign-out"></i></a>

</div></textarea>
	
	
	</td>
<td>
<label for="kspt_konu_temasi_css_demo">2 - <?php _e("Example css or js (same demo)","kuaza-post-shared-tracker"); ?><br>
<textarea id="kspt_konu_temasi_css_demo" cols="45" rows="8">
<link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">

<style>
.paylasimcevre{
margin-bottom:10px;
width:700px;
color:#fff
}

.btn {
  display: inline-block;
  padding: 6px 12px;
  margin-bottom: 0;
  font-size: 14px;
  font-weight: normal;
  line-height: 1.42857143;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
  cursor: pointer;
  -webkit-user-select: none;
     -moz-user-select: none;
      -ms-user-select: none;
          user-select: none;
  background-image: none;
  border: 1px solid transparent;
  border-radius: 4px;
}
.btn:focus,
.btn:active:focus,
.btn.active:focus {
  outline: thin dotted;
  outline: 5px auto -webkit-focus-ring-color;
  outline-offset: -2px;
}
.btn:hover,
.btn:focus, .btn:visited {
  /*color: #fff;*/
  text-decoration: none;
}
.btn:active,
.btn.active {
  background-image: none;
  outline: 0;
  -webkit-box-shadow: inset 0 3px 5px rgba(0, 0, 0, .125);
          box-shadow: inset 0 3px 5px rgba(0, 0, 0, .125);
color: #fff;
}
.btn.disabled,
.btn[disabled],
fieldset[disabled] .btn {
  pointer-events: none;
  cursor: not-allowed;
  filter: alpha(opacity=65);
  -webkit-box-shadow: none;
          box-shadow: none;
  opacity: .65;
}
a.btn{
text-decoration:none;
color:#fff
}
.btn-toplampaylasim, button.btn-toplampaylasim {
	background: #ddd;
	border-radius: 0;
	color: #a9a9a9
}
.btn-toplampaylasim:link, .btn-toplampaylasim:visited {
	color: #a9a9a9
}
.btn-toplampaylasim:active, .btn-toplampaylasim:hover {
	background: #ccc;
	color: #a9a9a9
}
.btn-pinterest, button.btn-pinterest {
	background: #c8232c;
	border-radius: 0;
	color: #fff
}
.btn-pinterest:link, .btn-pinterest:visited {
	color: #fff
}
.btn-pinterest:active, .btn-pinterest:hover {
	background: #BB2128;
	color: #fff
}
.btn-twitterx, button.btn-twitterx {
	background: #00acee;
	border-radius: 0;
	color: #ffffff
}
.btn-twitterx:link, .btn-twitterx:visited {
	color: #ffffff
}
.btn-twitterx:active, .btn-twitterx:hover {
	background: #009FDE;
	color: #ffffff
}
.btn-facebook, button.btn-facebook {
	background: #3b5998;
	border-radius: 0;
	color: #fff
}
.btn-facebook:link, .btn-facebook:visited {
	color: #fff
}
.btn-facebook:active, .btn-facebook:hover {
	background: #30477a;
	color: #fff
}
.btn-googleplus, button.btn-googleplus {
	background: #e93f2e;
	border-radius: 0;
	color: #fff
}
.btn-googleplus:link, .btn-googleplus:visited {
	color: #fff
}
.btn-googleplus:active, .btn-googleplus:hover {
	background: #ba3225;
	color: #fff
}
.btn-stumbleupon, button.btn-stumbleupon {
	background: #f74425;
	border-radius: 0;
	color: #fff
}
.btn-stumbleupon:link, .btn-stumbleupon:visited {
	color: #fff
}
.btn-stumbleupon:active, .btn-stumbleupon:hover {
	background: #c7371e;
	color: #fff
}
.btn-linkedin, button.btn-linkedin {
	background: #0e76a8;
	border-radius: 0;
	color: #fff
}
.btn-linkedin:link, .btn-linkedin:visited {
	color: #fff
}
.btn-linkedin:active, .btn-linkedin:hover {
	background: #0b6087;
	color: #fff
}
/* twente twelve fix color for visited button link
.entry-content a:visited,
.comment-content a:visited {
	color: #fff;
} */


/* bilesen icin css alani */
.paylasimcevre_bilesen{
margin-bottom:10px;
width:%100;
color:#fff
}
.paylasimcevre_bilesen a{
text-decoration:none;
}
.btn-tamgenis {
        width:100%;
}

.btn-toplampaylasim_bilesen, button.btn-toplampaylasim_bilesen{
	background: #dddddd;
	border-radius: 0;
	color: #a9a9a9;
        width:100%;
}
.btn-toplampaylasim_bilesen:link, .btn-toplampaylasim_bilesen:visited {
	color: #a9a9a9
}
.btn-toplampaylasim_bilesen:active, .btn-toplampaylasim_bilesen:hover {
	background: #ccc;
	color: #a9a9a9
}
.arkaplan_eeeeee {
	background: #eeeeee;
	border-radius: 0;
	color: #a9a9a9;
margin-bottom:2px;
}
.arkaplan_ffffff {
	background: #ffffff;
	border-radius: 0;
	color: #a9a9a9;
margin-bottom:2px;
}

.color_222222, .color_222222 a{
color:#222222
}
.kpst_social_counter_ajax{
display:inline-block;
}
.yukleniyorikonu{
display:inline-block;
float:left;
border:none;
}
.yukleniyorikonu img{
border:none;
}
</style>
</textarea>
</td>
</tr>

<tr valign="top">
	<th><hr /></th>
	<td><hr /></td>
<td><hr /></td>
</tr>
<tr valign="top">
	<th><?php _e("DEMO 2","kuaza-post-shared-tracker"); ?>
<a href='http://piclect.com/174684' target='_blank' title='screenshot-5'><img src='http://sv102.piclect.com/b52752455/m/14/09/24/screenshot-5.png' alt='screenshot-5' /></a>
</th>
	<td>
	
	<label for="kspt_konu_temasi_tema_demo">1 - <?php _e("Example templates (same demo)","kuaza-post-shared-tracker"); ?></label><br>
	<textarea id="kspt_konu_temasi_tema_demo" cols="45" rows="8"><div class="clearfix paylasimcevre" id="">

<div class="solbuyuk">
	<span>{{hepsi}}</span>
		<small>Shares</small>
</div>

<div class="sagnormal">
<!-- Twitter -->
<a href="https://twitter.com/intent/tweet?url={{post_url}}&title={{post_title}}" title="Share on Twitter" target="_blank" class="btn btn-twitterx"><i class="fa fa-twitter"></i> {{twitter}}</a>

 <!-- Facebook -->
<a href="https://www.facebook.com/sharer/sharer.php?u={{post_url}}" title="Share on Facebook" target="_blank" class="btn btn-facebook"><i class="fa fa-facebook"></i> {{facebook}}</a>

<!-- Google+ -->
<a href="https://plus.google.com/share?url={{post_url}}" title="Share on Google+" target="_blank" class="btn btn-googleplus"><i class="fa fa-google-plus"></i> {{google}}</a>

<!-- StumbleUpon -->
<a href="http://www.stumbleupon.com/submit?url={{post_url}}" title="Share on StumbleUpon" target="_blank" data-placement="top" class="btn btn-stumbleupon"><i class="fa fa-stumbleupon"></i> {{stumbleupon}}</a>
<!-- LinkedIn --> 
<a href="http://www.linkedin.com/shareArticle?mini=true&url={{post_url}}&title={{post_title}}&summary=" title="Share on LinkedIn" target="_blank" class="btn btn-linkedin"><i class="fa fa-linkedin"></i> {{linkedin}}</a>

<!-- pinterest --> 
<a href="#" title="Share on LinkedIn" target="_blank" class="btn btn-pinterest"><i class="fa fa-pinterest"></i> {{pinterest}}</a>

<!-- direkt cikis sayisi : toplam_cikis_sayisi mecburi.. --> 
<a id="toplam_cikis_sayisi" href="{{wlops_out_url}}" title="View more.." target="_blank" class="btn btn-toplampaylasim">{{wlops_out_click}} <i class="fa fa-sign-out"></i></a>

</div>

</div></textarea>
	
	
	</td>
<td>
<label for="kspt_konu_temasi_css_demo">2 - <?php _e("Example css or js (same demo)","kuaza-post-shared-tracker"); ?><br>
<textarea id="kspt_konu_temasi_css_demo" cols="45" rows="8">
<link href="//netdna.bootstrapcdn.com/font-awesome/4.1.0/css/font-awesome.min.css" rel="stylesheet">

<style>
.solbuyuk{
float:left;
text-align:center;
border-right:1px solid #ccc;
padding-right:15px;
height:100%
}

.solbuyuk span{
font-size:40px;
font-weight:bold;
margin-top: -15px;
display: inline-block;
}

.solbuyuk small{
display:block;
margin-top:-20px;
font-size:15px;
width:100%;
height:100%
}

.sagnormal{
padding-left:15px;
padding-top:12px;
float:left;
}

.paylasimcevre,.clearfix:before,.clearfix:after{
display:table;content:" ";
/*background:#eee;*/
margin-bottom:10px;
width:100%
}

.btn {
  display: inline-block;
  padding: 6px 12px;
  margin-bottom: 0;
  font-size: 14px;
  font-weight: normal;
  line-height: 1.42857143;
  text-align: center;
  white-space: nowrap;
  vertical-align: middle;
  cursor: pointer;
  -webkit-user-select: none;
     -moz-user-select: none;
      -ms-user-select: none;
          user-select: none;
  background-image: none;
  border: 1px solid transparent;
  border-radius: 4px;
}
.btn:focus,
.btn:active:focus,
.btn.active:focus {
  outline: thin dotted;
  outline: 5px auto -webkit-focus-ring-color;
  outline-offset: -2px;
}
.btn:hover,
.btn:focus, .btn:visited {
  /*color: #fff;*/
  text-decoration: none;
}
.btn:active,
.btn.active {
  background-image: none;
  outline: 0;
  -webkit-box-shadow: inset 0 3px 5px rgba(0, 0, 0, .125);
          box-shadow: inset 0 3px 5px rgba(0, 0, 0, .125);
color: #fff;
}
.btn.disabled,
.btn[disabled],
fieldset[disabled] .btn {
  pointer-events: none;
  cursor: not-allowed;
  filter: alpha(opacity=65);
  -webkit-box-shadow: none;
          box-shadow: none;
  opacity: .65;
}
a.btn{
text-decoration:none;
color:#fff
}
.btn-toplampaylasim, button.btn-toplampaylasim {
	background: #ddd;
	border-radius: 0;
	color: #a9a9a9
}
.btn-toplampaylasim:link, .btn-toplampaylasim:visited {
	color: #a9a9a9
}
.btn-toplampaylasim:active, .btn-toplampaylasim:hover {
	background: #ccc;
	color: #a9a9a9
}
.btn-pinterest, button.btn-pinterest {
	background: #c8232c;
	border-radius: 0;
	color: #fff
}
.btn-pinterest:link, .btn-pinterest:visited {
	color: #fff
}
.btn-pinterest:active, .btn-pinterest:hover {
	background: #BB2128;
	color: #fff
}
.btn-twitterx, button.btn-twitterx {
	background: #00acee;
	border-radius: 0;
	color: #ffffff
}
.btn-twitterx:link, .btn-twitterx:visited {
	color: #ffffff
}
.btn-twitterx:active, .btn-twitterx:hover {
	background: #009FDE;
	color: #ffffff
}
.btn-facebook, button.btn-facebook {
	background: #3b5998;
	border-radius: 0;
	color: #fff
}
.btn-facebook:link, .btn-facebook:visited {
	color: #fff
}
.btn-facebook:active, .btn-facebook:hover {
	background: #30477a;
	color: #fff
}
.btn-googleplus, button.btn-googleplus {
	background: #e93f2e;
	border-radius: 0;
	color: #fff
}
.btn-googleplus:link, .btn-googleplus:visited {
	color: #fff
}
.btn-googleplus:active, .btn-googleplus:hover {
	background: #ba3225;
	color: #fff
}
.btn-stumbleupon, button.btn-stumbleupon {
	background: #f74425;
	border-radius: 0;
	color: #fff
}
.btn-stumbleupon:link, .btn-stumbleupon:visited {
	color: #fff
}
.btn-stumbleupon:active, .btn-stumbleupon:hover {
	background: #c7371e;
	color: #fff
}
.btn-linkedin, button.btn-linkedin {
	background: #0e76a8;
	border-radius: 0;
	color: #fff
}
.btn-linkedin:link, .btn-linkedin:visited {
	color: #fff
}
.btn-linkedin:active, .btn-linkedin:hover {
	background: #0b6087;
	color: #fff
}
/* twente twelve fix color for visited button link
.entry-content a:visited,
.comment-content a:visited {
	color: #fff;
} */


/* bilesen icin css alani */
.paylasimcevre_bilesen{
margin-bottom:10px;
width:%100;
color:#fff
}
.paylasimcevre_bilesen a{
text-decoration:none;
}
.btn-tamgenis {
        width:100%;
}

.btn-toplampaylasim_bilesen, button.btn-toplampaylasim_bilesen{
	background: #dddddd;
	border-radius: 0;
	color: #a9a9a9;
        width:100%;
}
.btn-toplampaylasim_bilesen:link, .btn-toplampaylasim_bilesen:visited {
	color: #a9a9a9
}
.btn-toplampaylasim_bilesen:active, .btn-toplampaylasim_bilesen:hover {
	background: #ccc;
	color: #a9a9a9
}
.arkaplan_eeeeee {
	background: #eeeeee;
	border-radius: 0;
	color: #a9a9a9;
margin-bottom:2px;
}
.arkaplan_ffffff {
	background: #ffffff;
	border-radius: 0;
	color: #a9a9a9;
margin-bottom:2px;
}

.color_222222, .color_222222 a{
color:#222222
}
.kpst_social_counter_ajax{
display:inline-block;
}
.yukleniyorikonu{
display:inline-block;
float:left;
border:none;
}
.yukleniyorikonu img{
border:none;
}
</style>
</textarea>
</td>
</tr>

</table>

<?php
}

/*
 * Icerikleri admin sayfasindan gunceller
 *
*/
function kuaza_social_icerikleriguncelle(){
global $kspt_enable_plugins;

echo kuaza_social_menuolustur();

if(isset($_GET["icerikleriguncelle_kpst"]) && $_GET["icerikleriguncelle_kpst"] == "regenerator_kpst"){
$kspt_konu_temasi_css_extra = get_option( 'kspt_konu_temasi_css' );

if($kspt_konu_temasi_css_extra){
echo stripslashes($kspt_konu_temasi_css_extra);

}

 
// yazilari tek tek listeleyip o sekilde islem yaptiriyoruz.
$paged = !empty($_GET['paged']) ? $_GET['paged'] : 1;
$args = array('post_status' => "publish",'sort_order' => "asc",'posts_per_page' => 1, 'paged' => $paged );
query_posts($args); ?>

<!-- the loop -->
<?php if ( have_posts() ) { while (have_posts()) : the_post(); ?>
<a href="<?php the_permalink(); ?>" target="_blank"><?php the_title(); ?></a>

<?php
$gelsinnevarsa = sayfadagoster_kpst();
echo $gelsinnevarsa;
?>

<?php endwhile; ?>

 <br><br>
<?php
$sonraki_sayfa = get_admin_url()."edit.php?icerikleriguncelle_kpst=regenerator_kpst&page=kuazasocial&islem=icerikleriguncelle&paged=".($paged+1);
$onceki_sayfa = get_admin_url()."edit.php?icerikleriguncelle_kpst=regenerator_kpst&page=kuazasocial&islem=icerikleriguncelle&paged=".($paged-1);

if($paged >= 1){
  if (headers_sent()){
	echo "Wait 10 seconds - Guncellendi ve yonlendirme bekleniyor <em>(10 saniye)</em> - <a href='".$sonraki_sayfa."'>Sonraki sayfa <em>icin yonlendirme islemi baslamaz ise el ile yapin</em></a>..<hr>";
      echo ('<script type="text/javascript">setTimeout(function(){window.location.href="' . $sonraki_sayfa . '";}, 10000);</script>');
    }else{
      echo "Wait 10 seconds - Guncellendi ve yonlendirme bekleniyor <em>(10 saniye)</em> - <a href='".$sonraki_sayfa."'>Sonraki sayfa <em>icin yonlendirme islemi baslamaz ise el ile yapin</em></a>..<hr>";
	  header('Refresh: 10; URL='.$sonraki_sayfa);

    } 
}

if($paged > 1){
echo '<a href="'.$onceki_sayfa.'">'.__("Before page","kuaza-post-shared-tracker").'</a> - ';
}
if($paged >= 1){
echo '<a href="'.$sonraki_sayfa.'">'.__("Next page","kuaza-post-shared-tracker").'</a>';
}
 }else{ ?>
<?php _e("Finish ;)","kuaza-post-shared-tracker"); ?>
<?php }
}


if(isset($_POST["icerikleriguncelle_kpst"]) && $_POST["icerikleriguncelle_kpst"] == "listallpost_kpst"){
echo '.__("Coming soon :)","kuaza-post-shared-tracker").';
}
?>


<form method="GET" action="">
<table class="form-table">

<tr valign="top">
	<th scope="row"><label for="aciklamaevetmi"><?php _e("Regenerator new istatistik","kuaza-post-shared-tracker"); ?></label></th>
	<td><select id="icerikleriguncelle_kpst" name="icerikleriguncelle_kpst">
<option value="listallpost_kpst" disabled><?php _e("List al post","kuaza-post-shared-tracker"); ?></option>
<option value="regenerator_kpst" selected><?php _e("Regenerator counter all posts","kuaza-post-shared-tracker"); ?></option>
</select></td>
<td><?php _e("Warning: At a time when the structure of your visitors that you at least.","kuaza-post-shared-tracker"); ?><hr />
<?php _e("Before change cache time area = 0","kuaza-post-shared-tracker"); ?>
</td>
</tr>

</table>
<input id="page" name="page" type="hidden" value="kuazasocial" />
<input id="islem" name="islem" type="hidden" value="icerikleriguncelle" />
<p class="submit"><input type="submit" class="button-primary" value="<?php _e("Update all posts","kuaza-post-shared-tracker"); ?>" /></p>
</form>
<?php
}

if($kspt_enable_plugins == "yes"){
/*
 * Yeni yazi yayinlandiginda, veya duzenlendiginde istatistik bilgilerini gunceller.
 *
*/
add_action('publish_post', 'kuaza_post_shared_counter');
add_action('edit_post', 'kuaza_post_shared_counter');

/*
 * Bir yazi silindiginde kpst tablosundaki kendisine ait alanida sileriz
 *
*/
add_action( 'admin_init', 'codex_init' );
function codex_init() {
    if ( current_user_can( 'delete_posts' ) )
        add_action( 'delete_post', 'kuaza_post_shared_counter_sil', 10 );
}


/*
* Konu icin takip tablosunda yeni alan olusturma (eger konu icin kpst istatistik tablosu olusturulmamis ise)
*/
function kspt_post_tracker_olustur($icerik) {
   global $wpdb;

   $table_name = $wpdb->prefix . "kuazasocialtracker";
   $rows_affected = $wpdb->insert( $table_name, $icerik );
   return $rows_affected ? true : false;
}

/*
 * Eklentiye ozel bilesen bolumunu include ederiz
 *
*/
require_once( KUAZA_POST_SHARED_DIR . 'class.kpst-counter-widget.php' );
}


function kpst_convert_array_theme($array = false){
global $kspt_konu_temasi, $kpst_gosterilmesin_mi;

if($kpst_gosterilmesin_mi == "yes")
	return false;
	
if(!$array)
	return null;

$kspt_konu_temasi_yedek = $kspt_konu_temasi;

		// wlops varmi yokmu kontrol eder, yoksa tema icerisinden cikartiriz
		if(empty($array["wlops_mu_acaba"]) || $array["wlops_mu_acaba"] == "0"){
			$kspt_konu_temasi_yedek = preg_replace( '|<a.*>{{wlops_out_click}}.*</a>|i', '', $kspt_konu_temasi_yedek );
				}
		
			if ( !empty($array) && is_array($array) ) :

			foreach ($array as $code => $value)
				$kspt_konu_temasi_yedek = str_replace('{{'.$code.'}}', $value, $kspt_konu_temasi_yedek);
			
			endif;
			  
	return stripslashes($kspt_konu_temasi_yedek);
}




/*
 * Eklenti aktif edilirse tablo ve bilgileri ekleriz yada silinirse kpst iceriklerini sildiririz.
 *
*/
register_activation_hook( __FILE__, 'kspt_install' );
register_uninstall_hook( __FILE__, 'kspt_unstall' );

function kspt_install() {
   global $wpdb;
  
   $table_name = $wpdb->prefix . "kuazasocialtracker";
      
   $sql = "CREATE TABLE $table_name (
  ku_id int(11) NOT NULL AUTO_INCREMENT,
  ku_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  ku_postid int(11) NOT NULL,
  ku_facebook int(11) DEFAULT '0' NOT NULL,
  ku_twitter int(11) DEFAULT '0' NOT NULL,
  ku_google int(11) DEFAULT '0' NOT NULL,
  ku_linkedin int(11) DEFAULT '0' NOT NULL,
  ku_pinterest int(11) DEFAULT '0' NOT NULL,
  ku_stumble int(11) DEFAULT '0' NOT NULL,
  ku_hepsi int(11) DEFAULT '0' NOT NULL,
  ku_cache_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
  ku_goster int(1) DEFAULT '1' NOT NULL,
  PRIMARY KEY (`ku_id`),
  UNIQUE KEY `ku_postid` (`ku_postid`)
    );";

   require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
   $olustur = dbDelta( $sql );
   add_option( "kspt_db_version", KUAZA_POST_SHARED_VER );
   add_option( "kspt_cache_time", "60" );
   add_option( "kspt_disable_search", "yes" );
   add_option( "kspt_enable_plugins", "no" );
   add_option( "kspt_enable_ajax", "no" );
   add_option( "kspt_settimetime", "1000" );
   add_option( "kspt_konu_otomatik", "no" );   
   add_option( "kspt_konudagoster_neresi", "cont_before" ); 
   add_option( "kspt_konu_temasi", "please create themes (look right)" );
   add_option( "kspt_konu_temasi_css", "" );
   
   add_site_option( "kspt_db_version", KUAZA_POST_SHARED_VER );
   add_site_option( "kspt_cache_time", "60" );
   add_site_option( "kspt_disable_search", "yes" );
   add_site_option( "kspt_enable_plugins", "no" );
   add_site_option( "kspt_enable_ajax", "no" );
   add_site_option( "kspt_settimetime", "1000" );
   add_site_option( "kspt_konu_otomatik", "no" );   
   add_site_option( "kspt_konudagoster_neresi", "cont_before" ); 
   add_site_option( "kspt_konu_temasi", "please create themes (look right)" );
   add_site_option( "kspt_konu_temasi_css", "" );
   return $olustur ? true : false;
}
function kspt_unstall() {
   global $wpdb;
 
   $table_name = $wpdb->prefix . "kuazasocialtracker";
   delete_option( "kspt_db_version" );
   delete_option( "kspt_cache_time" );
   delete_option( "kspt_disable_search" );
   delete_option( "kspt_enable_plugins" );
   delete_option( "kspt_enable_ajax" );
   delete_option( "kspt_settimetime" );
   delete_option( "kspt_konu_otomatik" );
   delete_option( "kspt_konudagoster_neresi" );
   delete_option( "kspt_konu_temasi" );
   delete_option( "kspt_konu_temasi_css" );
   
   delete_site_option( "kspt_db_version" );
   delete_site_option( "kspt_cache_time" );
   delete_site_option( "kspt_disable_search" );
   delete_site_option( "kspt_enable_plugins" );  
   delete_site_option( "kspt_enable_ajax" );   
   delete_site_option( "kspt_settimetime" ); 
   delete_site_option( "kspt_konu_otomatik" );
   delete_site_option( "kspt_konudagoster_neresi" );
   delete_site_option( "kspt_konu_temasi" );
   delete_site_option( "kspt_konu_temasi_css" );
   $wpdb->query("DROP TABLE IF EXISTS ".$table_name);
}

