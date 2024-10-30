<?php

/**
 * Plugin Name: Mobile App Converter
 * Plugin URI: http://www.tidiochat.com
 * Description: Discover Mobile App Converter a plugin that will turn your WordPress into a fully functional Mobile Application in just few minutes!
 * Version: 1.0.2
 * Author: Tidio Ltd.
 * Author URI: http://www.tidiochat.com
 * License: GPL2
 */
 
class TidioLiveGoApp {

    private $scriptUrl = '//www.tidiogoapp.com/redirect/';

    public function __construct() {
        add_action('admin_menu', array($this, 'addAdminMenuLink'));
        add_action('admin_footer', array($this, 'adminJS'));

        self::getPrivateKey();
        
        if(!is_admin()){
            add_action('wp_enqueue_scripts', array($this, 'enqueueScripts'));
        }
        
        add_action('deactivate_'.plugin_basename(__FILE__), array($this, 'uninstall'));	

        add_action('wp_ajax_tidio_goapp_redirect', array($this, 'ajaxTidioGoAppRedirect'));	

    }
	
	// Ajax - Create New Project
	
	public function ajaxTidioGoAppRedirect(){
		
		if(!empty($_GET['access_status']) && !empty($_GET['private_key']) && !empty($_GET['public_key'])){
			
			update_option('tidio-goapp-external-public-key', $_GET['public_key']);
			update_option('tidio-goapp-external-private-key', $_GET['private_key']);
			
			$view = array(
				'mode' => 'redirect',
				'redirect_url' => self::getRedirectUrl($_GET['private_key'])
			);
			
		} else {
		
			$view = array(
				'mode' => 'access_request',
				'access_url' => self::getAccessUrl()
			);
							
		}
		
		require "views/ajax-tidio-goapp-redirect.php";

		exit;
		
	}
	    
    // Front End Scripts
    
    public function enqueueScripts(){
    	wp_enqueue_script('tidio-goapp', '//www.tidiogoapp.com/client/tidio-goapp-loader.js', array(), '1.0.0', true);
    }

    // Admin JavaScript

    public function adminJS() {
		
		$privateKey = self::getPrivateKey();
		$redirectUrl = '';	
		
		if(self::isLocalHost()){
			return false;
		} else if(!$privateKey || $privateKey=='false'){
			$redirectUrl = admin_url('admin-ajax.php?action=tidio_goapp_redirect');
		} else {
			$redirectUrl = self::getRedirectUrl($privateKey);
		}
		
		echo "<script> jQuery('a[href=\"admin.php?page=tidio-goapp\"]').attr('href', '".$redirectUrl."').attr('target', '_blank') </script>";
		
	}

    // Menu Pages

    public function addAdminMenuLink() {

        $optionPage = add_menu_page(
			'WP2Mobile',
			'WP2Mobile',
			'manage_options',
			'tidio-goapp',
			array($this, 'addAdminPage'), plugin_dir_url( __FILE__ ).'media/img/icon.png'
        );
    }

    public function addAdminPage() {
        // Set class property
        $dir = plugin_dir_path(__FILE__);
        include $dir . 'options.php';
    }
    
    // Uninstall
	
    public function uninstall(){
    }

    // Get Private Key

    public static function getPrivateKey() {
		
		if(self::isLocalHost()){
			return false;
		}
		
        $privateKey = get_option('tidio-goapp-external-private-key');

        if ($privateKey) {
            return $privateKey;
        }

        @$data = file_get_contents(self::getAccessUrl());
        if (!$data) {
            update_option('tidio-goapp-external-private-key', 'false');
            return false;
        }

        @$data = json_decode($data, true);
        if (!$data || !$data['status']) {
            update_option('tidio-goapp-external-private-key', 'false');
            return false;
        }

        update_option('tidio-goapp-external-private-key', $data['value']['private_key']);
        update_option('tidio-goapp-external-public-key', $data['value']['public_key']);

        return $data['value']['private_key'];
    }
	
	// Get Access Url
	
	public static function getAccessUrl(){
		
		return 'http://www.tidiogoapp.com/access/create?url='.urlencode(site_url()).'&platform=wordpress&email='.urlencode('auto@tidio.net').'&_ip='.$_SERVER['REMOTE_ADDR'];
		
	}
	
	public static function getRedirectUrl($privateKey){
		
		return 'http://external.tidiogoapp.com/access?privateKey='.$privateKey;
		
	}
	
	// Get Public Key

    public static function getPublicKey() {

        $publicKey = get_option('tidio-goapp-external-public-key');

        if ($publicKey) {
            return $publicKey;
        }

        self::getPrivateKey();

        return get_option('tidio-goapp-external-public-key');
    }
	
	public static function isLocalHost(){
				
		if(isset($_SERVER['HTTP_HOST']) && $_SERVER['HTTP_HOST']=='localhost'){
			return true;
		}
		
		return false;
		
	}
    
    

}

$tidioLiveGoApp = new TidioLiveGoApp();

