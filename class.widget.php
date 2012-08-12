<?php 
/*
Name: Widget Class for the WS7 Weather plugin
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

class WS7_Weather_Widget extends WP_Widget {

	/**
	 * Register widget with WordPress.
	 */
	public function __construct() {
		parent::__construct(
	 		'ws7_weather_widget', // Base ID
			'WS7 Weather Widget', // Name
			array( 'description' => __( 'Shows a pretty forecast based on today\'s forecast by Kevin Jones', 'ws7weather' ), ) // Args
		);
	}

	/**
	 * Front-end display of widget.
	 *
	 * @see WP_Widget::widget()
	 *
	 * @param array $args     Widget arguments.
	 * @param array $instance Saved values from database.
	 */
	public function widget( $args, $instance ) {
		extract( $args );
		$title = apply_filters( 'widget_title', $instance['title'] );
		$f = get_option('ws7_weather'); //today's forecast
		$img = self::get_image_url($f['keyword'], $instance['size']);

		echo $before_widget;
		if ( ! empty( $title ) )
			echo $before_title . $title . $after_title;
		echo '<a href="'.$f['permalink'].'" title="'.$f['title'].'"><img src="'.$img.'" alt="Today\'s forecast: '.$f['keyword'].'" /></a>';
		echo $after_widget;
	}
	
	/**
	 * Sanitize widget form values as they are saved.
	 *
	 * @see WP_Widget::update()
	 *
	 * @param array $new_instance Values just sent to be saved.
	 * @param array $old_instance Previously saved values from database.
	 *
	 * @return array Updated safe values to be saved.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['size'] = intval( $new_instance['size'] );

		return $instance;
	}
	
	/**
	 * Back-end widget form.
	 *
	 * @see WP_Widget::form()
	 *
	 * @param array $instance Previously saved values from database.
	 */
	public function form( $instance ) {
		$title = ($instance['title']) ? $instance['title'] : __( 'Today\'s Forecast', 'text_domain' ); 
		$size = ($instance['size']) ? $instance['size'] : 150; 
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
		<p><?php _e('Image size:') ?><br/>
			<label><input type="radio" name="<?php echo $this->get_field_name( 'size' ); ?>" id="<?php echo $this->get_field_id( 'size' ); ?>" value="50" <?php echo ($size==50) ? 'checked="checked" ':''; ?>/>50 x 50</label><br/>
			<label><input type="radio" name="<?php echo $this->get_field_name( 'size' ); ?>" id="<?php echo $this->get_field_id( 'size' ); ?>" value="150" <?php echo ($size==150) ? 'checked="checked" ':''; ?>/>150 x 150</label><br/>
			<label><input type="radio" name="<?php echo $this->get_field_name( 'size' ); ?>" id="<?php echo $this->get_field_id( 'size' ); ?>" value="250" <?php echo ($size==250) ? 'checked="checked" ':''; ?>/>250 x 250</label>
		</p>
		<?php 
	}

	/**
	 * Get the right image based on keyword
	 */
	private function get_image_url($keyword, $size){
		$keyword = str_replace(' ', '-', $keyword);
		$filename = $keyword.'_'.$size.'.png';
		return plugins_url('img/'.$filename, __FILE__);
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
// register widget
add_action( 'widgets_init', create_function( '', 'register_widget( "ws7_weather_widget" );' ) );


?>