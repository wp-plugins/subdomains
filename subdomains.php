<?php
/*
Plugin Name: Subdomains
Plugin URI: http://pankajanupam.in/wordpress-plugins/subdomains/
Description: Use selecttive categories as subdomain
Version: 0.5
Author: PANKAJ ANUPAM
Author URI: http://pankajanupam.in

* LICENSE
    Copyright 2011 PANKAJ ANUPAM  (email : mymail.anupam@gmail.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/
?>
<?php
class subSubdomain{
    var $slug;
    var $field;
    function  __construct() {
        $this->field ='category_name';
    }

    function getSubdomain(){
        $url = getenv( 'HTTP_HOST' );
	$domain = explode( ".", $url );
        $this->slug = $domain[0];
        return get_category_by_slug($this->slug);
    }

    function getRewriteRules(){
        $rules = array();
	$rules["feed/(feed|rdf|rss|rss2|atom)/?$"] = "index.php?" . $this->field . "=" . $this->slug . "&feed=\$matches[1]";
	$rules["(feed|rdf|rss|rss2|atom)/?$"] = "index.php?" . $this->field . "=" . $this->slug . "&feed=\$matches[1]";
	$rules["page/?([0-9]{1,})/?$"] = "index.php?" . $this->field . "=" . $this->slug . "&paged=\$matches[1]";
	$rules["$"] = "index.php?" . $this->field . "=" . $this->slug;
        return $rules;
    }
}

class initPlugin extends subSubdomain{
    function __construct(){
        parent::__construct();
    }
    function addActions() {
		add_action( 'init', 'wps_init', 2 );
	}
    function addFilters(){
     //   add_filter( 'rewrite_rules_array', 'wps_rewrite_rules' );
        add_filter( 'category_rewrite_rules', 'sub_category_rewrite_rules' );
        add_filter( 'post_rewrite_rules', 'sub_post_rewrite_rules' );

        add_filter( 'category_link', 'sub_category_link', 10, 2 );
        add_filter( 'post_link', 'sub_post_link', 10, 2 );
    }
}

$obj_sub = new initPlugin;
$obj_sub->addActions();
$obj_sub->addFilters();

function wps_init () {
	if (!is_admin()) {
		// Stuff changed in WP 2.8
		if (function_exists('set_transient')) {
			set_transient('rewrite_rules', "");
			update_option('rewrite_rules', "");
		} else {
			update_option('rewrite_rules', "");
		}
	}
}

add_filter( 'root_rewrite_rules', 'wps_root_rewrite_rules' );
function wps_root_rewrite_rules( $rules ) {
        //$rules = array();
return $rules;
}
function sub_category_rewrite_rules($rules){
    global $obj_sub;
  if($domain = $obj_sub->getSubdomain()){
        $rules = $obj_sub->getRewriteRules();
  }
  return $rules;
}

function sub_post_rewrite_rules($rules){
$rules = array ();
    $rules['[^/]+/attachment/([^/]+)/?$']='index.php?attachment=$matches[1]';
    $rules['[^/]+/attachment/([^/]+)/trackback/?$'] ='index.php?attachment=$matches[1]&tb=1';
    $rules['[^/]+/attachment/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$'] ='index.php?attachment=$matches[1]&feed=$matches[2]';
    $rules['[^/]+/attachment/([^/]+)/(feed|rdf|rss|rss2|atom)/?$'] ='index.php?attachment=$matches[1]&feed=$matches[2]';
    $rules['[^/]+/attachment/([^/]+)/comment-page-([0-9]{1,})/?$'] ='index.php?attachment=$matches[1]&cpage=$matches[2]';
    $rules['([^/]+)/trackback/?$'] ='index.php?name=$matches[1]&tb=1';
    $rules['([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?name=$matches[1]&feed=$matches[2]';
    $rules['([^/]+)/(feed|rdf|rss|rss2|atom)/?$'] ='index.php?name=$matches[1]&feed=$matches[2]';
    $rules['([^/]+)/page/?([0-9]{1,})/?$'] ='index.php?name=$matches[1]&paged=$matches[2]';
    $rules['([^/]+)/comment-page-([0-9]{1,})/?$'] ='index.php?name=$matches[1]&cpage=$matches[2]';
    $rules['([^/]+)(/[0-9]+)?/?$']='index.php?name=$matches[1]&page=$matches[2]';
    $rules['[^/]+/([^/]+)/?$'] ='index.php?attachment=$matches[1]';
    $rules['[^/]+/([^/]+)/trackback/?$'] ='index.php?attachment=$matches[1]&tb=1';
    $rules['[^/]+/([^/]+)/feed/(feed|rdf|rss|rss2|atom)/?$'] ='index.php?attachment=$matches[1]&feed=$matches[2]';
    $rules['[^/]+/([^/]+)/(feed|rdf|rss|rss2|atom)/?$'] = 'index.php?attachment=$matches[1]&feed=$matches[2]';
    $rules['[^/]+/([^/]+)/comment-page-([0-9]{1,})/?$']= 'index.php?attachment=$matches[1]&cpage=$matches[2]';
    return $rules;
}

function sub_category_link( $link, $term_id ) {
    $link = str_replace('www.','',$link);
    $link = preg_replace('/(?<=http\:\/\/)([a-z0-9_\-\.]+)\/category(.*)\/([a-z0-9_\-]+)/','$3.$1', $link);
    return $link;
}

function sub_post_link( $link, $id ){
   $link = str_replace('www.','',$link);
   $link = preg_replace('/(?<=http\:\/\/)([a-z0-9_\-\.]+)\/(.*)\/([a-z0-9\-\_]+)\/([a-z0-9\-\_]+)/','$3.$1/$4', $link);
   $link = preg_replace('/(?<=http\:\/\/)([a-z0-9_\-\.]+)\/([a-z0-9\-\_]+)\/([a-z0-9\-\_]+)/','$2.$1/$3', $link);
   return $link;
}
?>