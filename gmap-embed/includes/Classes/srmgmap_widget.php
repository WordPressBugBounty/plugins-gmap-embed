<?php

namespace WGMSRM\Classes;

use WP_Query;
use WP_Widget;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Creating widget for WP Google Map
 */
class srmgmap_widget extends WP_Widget
{


    public $base_id = 'srmgmap_widget';
    public $widget_name = 'WP Google Map';
    public $widget_options = array(
        'description' => 'Embed Google Map for your website', // widget description
    );

    public function __construct()
    {
        parent::__construct(
            $this->base_id,
            $this->widget_name,
            $this->widget_options
        );

        add_action(
            'widgets_init',
            function () {
                register_widget('WGMSRM\\Classes\\srmgmap_widget');
            }
        );
    }

    // Map display in front
    public function widget($args, $instance)
    {
        $title = apply_filters('widget_title', $instance['title']);

        extract($args);
        extract($instance);

        echo wp_kses_post(wp_unslash($before_widget));
        if (!empty($title)) {
            echo wp_kses_post(wp_unslash($before_title . esc_html($title) . $after_title));
        }
        echo do_shortcode($instance['srmgmap_shortcode']);
        echo wp_kses_post(wp_unslash($after_widget));
    }

    /**
     * Google Map Widget
     *
     * @return String $instance
     */
    public function form($instance)
    {
        $title = !empty($instance['title']) ? esc_html($instance['title']) : '';
        $map_shortcodes_list = '';
        $args = array(
            'post_type' => 'wpgmapembed',
            'posts_per_page' => -1,
            'post_status' => 'draft',
        );
        $maps_list = new WP_Query($args);

        if ($maps_list->have_posts()) {
            while ($maps_list->have_posts()) {
                $maps_list->the_post();
                $gmap_title = get_post_meta(get_the_ID(), 'wpgmap_title', true);
                if ($gmap_title === '') {
                    $gmap_title = 'No title';
                }
                $option_value = '[gmap-embed id=&quot;' . get_the_ID() . '&quot;]';
                $selected = '';
                if (isset($instance['srmgmap_shortcode']) && $instance['srmgmap_shortcode'] == html_entity_decode($option_value)) {
                    $selected = 'selected';
                }
                $map_shortcodes_list .= '<option value="' . esc_attr($option_value) . '" ' . esc_attr($selected) . '>' . esc_html($gmap_title) . ' ' . '[gmap-embed id=&quot;' . get_the_ID() . '&quot;]' . '</option>';
            }
        }
        ?>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">Title: </label>
        </p>
        <p>
            <input class="widefat" id="<?php echo esc_attr($this->get_field_id('title')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('title')); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>"/>
        </p>
        <p>
            <label for="<?php echo esc_attr($this->get_field_id('srmgmap_shortcode')); ?>"> Select Google Map
                Shortcode:</label><br/>
        </p>
        <p>
            <select id="<?php echo esc_attr($this->get_field_id('srmgmap_shortcode')); ?>"
                    name="<?php echo esc_attr($this->get_field_name('srmgmap_shortcode')); ?>" class="widefat">
                <?php
                $allowed_html = [
                    'option' => [
                        'value' => []
                    ],
                ];
                echo wp_kses(wp_unslash($map_shortcodes_list), $allowed_html); ?>
            </select>
        </p>

        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? esc_html($new_instance['title']) : '';
        $instance['srmgmap_shortcode'] = (!empty($new_instance['srmgmap_shortcode'])) ? $new_instance['srmgmap_shortcode'] : '';

        return $instance;
    }

}
