<?php 
/*
Plugin Name: WS7 Weather Widget
Plugin URI: http://philipjohn.co.uki/category/plugins/ws7-weather
Description: Provides a pretty widget for WordPress sites to display today's weather, as forecast by Kevin Jones' WS7 Weather
Version: 0.1
Author: Philip John
Author URI: http://philipjohn.co.uk

Copyright 2012 Philip John Ltd

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
*/

// Initial sanity check
if (! defined('ABSPATH'))
	die('Please do not directly access this file');

// Localise
load_plugin_textdomain('ws7weather');

class WS7_Weather {

	function __construct(){
		register_activation_hook(__FILE__, array(&$this, 'activate'));
		add_action('ws7_update', array(&$this, 'update_forecast'));
		register_deactivation_hook(__FILE__, array(&$this, 'deactivate'));
	}
	
	function activate(){
		$time = (time() == current_time('timestamp')) ? strtotime("tomorrow 7:10am") : current_time('timestamp');
		wp_schedule_event( $time, 'daily', 'ws7_update');
		$this->update_forecast(); //first time
	}
	
	function deactivate(){
		wp_clear_scheduled_hook('ws7_update');
	}
	
	function update_forecast(){
		$report = $this->fetch_rss();
		$keyword = $this->get_keyword($report->get_categories());
		
		$today = array(
			'title' => $report->get_title(),
			'summary' => $report->get_description(),
			'keyword' => $keyword,
			'permalink' => $report->get_permalink()
		);
		update_option('ws7_weather', $today);
	}
	
	function fetch_rss(){
		$url = 'http://kevinjones21.wordpress.com/category/news-and-politics/weather/feed';
		include_once(ABSPATH.WPINC.'/feed.php');
		$feed = fetch_feed($url);
		if (!is_wp_error($feed)):
			$item = $feed->get_item(0);
			return $item; // send back the first item
		else:
			die(false);
		endif;
	}
	
	function get_keyword($categories){
		$find = array(
			'cloudy', 'rain', 'snow', 'storm', 'light rain', 'sunny', 'partly cloudy',
			'sunny showers', 'rain and snow', 'sleet', 'rain and storm', 'windy'
		);
		
		$cats = array();
		foreach ($categories as $cat){
			if (in_array($cat->get_label(), $find)){
				return trim($cat->get_label());
			}
		}
	}
	
	/**
	 * Debug logging
	 */
	private function log($msg){
		$path = trailingslashit(ABSPATH);
		
		if (is_array($msg)){
			$output = "Array (\r\n";
			foreach ($msg as $key => $value){
				$output .= "	$key => $value,\r\n";
			}
			$output .= ')';
		}
		else {
			$output = $msg;
		}
		
		file_put_contents($path.'ws7.log', $output."\r\n", FILE_APPEND);
	}
}
new WS7_Weather;

// Trigger the Widget class now
require('class.widget.php');

?>