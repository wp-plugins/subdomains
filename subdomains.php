<?php

/*
  Plugin Name: Subdomains
  Plugin URI: http://pankajanupam.in/wordpress-plugins/subdomains/
  Description: Use selecttive categories as subdomain
  Version: 1.0.4
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

// include wp-admin options page
require_once 'subdomain-options.php';

class PA_Subdomain extends PA_Subdomain_Options { //extends option class for settings

    /**
     * requested texonomy term slug
     * @var string 
     */
    public $categor_slug;
    
    /**
     * texonomy query var
     * @var string
     */
    public $query_var;
    
    public $rules;

    public function __construct() {
        parent::__construct();
        
        $this->rules = array();
        $this->query_var = 'category_name';
        
        // add action 
        $this->add_actions();

        $this->add_filters();
    }

    /**
     * hook wordpress init action & flush rewite rules
     */
    public function add_actions() {
        add_action('init', array(&$this, 'flush_rewite_rules'), 2);
    }

    /**
     * hook requied filters
     */
    public function add_filters() {

        add_filter( 'post_rewrite_rules', array(&$this,'post_rewrite_rules') );
        
        //return all rewite rules
        add_filter('rewrite_rules_array', array(&$this, 'rewrite_rules_array'));
        
        //return category rewite rules
        // add_filter('category_rewrite_rules', array(&$this, 'category_rewrite_rules'));

        add_filter('category_link', array(&$this, 'category_link'), 10, 2);

        //add_filter( 'root_rewrite_rules', array(&$this,'root_rewrite_rules') );
        
        add_filter( 'post_link', array(&$this, 'post_link'), 10, 2 );
    }

    /**
     * flush rewite rules
     */
    public function flush_rewite_rules() {
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

    /**
     * Game changer
     * Replace rewrite rules if category page
     * @param array $rules wordpress genrated rewrite rules
     * @return array Final rewrite rules
     */
    public function rewrite_rules_array($rules) {

        if ($this->current_subdomain_is_category()) {
            $rules = $this->category_rewrite_rules($this->rules);
        }
        
        return $rules;
    }

    public function root_rewrite_rules($rules) {
        return array();
    }

    public function category_link($category_link, $term_id) {
        
        if( ! @in_array($term_id, $this->options['subdomain_cat'] ) ) {
            return $category_link;
        }
        
        $category = get_category($term_id);
        $link = $this->get_subodmin_link($category->slug);
        
        return $link;
    }

    function category_rewrite_rules($rules = array()) {

        $rules["feed/(feed|rdf|rss|rss2|atom)/?$"] = "index.php?" . $this->query_var . "=" . $this->categor_slug . "&feed=\$matches[1]";
        $rules["(feed|rdf|rss|rss2|atom)/?$"] = "index.php?" . $this->query_var . "=" . $this->categor_slug . "&feed=\$matches[1]";
        $rules["page/?([0-9]{1,})/?$"] = "index.php?" . $this->query_var . "=" . $this->categor_slug . "&paged=\$matches[1]";
        $rules["$"] = "index.php?" . $this->query_var . "=" . $this->categor_slug;

        return $rules;
    }
    
    public function post_link($post_link, $post) {        
          
        $category = get_the_category(); 
        
        if( !$category[0]->slug ) return $post_link;
       
        if( ! @in_array($category[0]->cat_ID, $this->options['subdomain_cat'] ) ) {
            return $post_link;
        }
        
        $link = $this->get_subodmin_link($category[0]->slug);
        $link = $link.'/'.$post->post_name;

        return $link;
    }
    
    function post_rewrite_rules($rules) {
        $this->rules = $rules;
        return $rules;
    }

    public function current_subdomain_is_category() {

        $url = getenv('HTTP_HOST');

        $domain = explode(".", $url);
        $this->categor_slug = $domain[0];

        // return false if not a category
        return get_category_by_slug($this->categor_slug);
    }
    
    public function get_subodmin_link($category_slug) {
        
        $site_url = home_url();

        $link = str_replace('www.', '', $site_url);

        $link = str_replace('http://', 'http://' . $category_slug . '.', $link);
        $link = str_replace('https://', 'https://' . $category_slug . '.', $link); 
        
        return $link;
    }

}

new PA_Subdomain();
?>
