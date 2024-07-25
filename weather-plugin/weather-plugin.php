<?php
/*
Plugin Name: Weather Plugin
Plugin URI: https://github.com/Donstesh/weather-plugin
Description: A simple plugin to display weather information.
Version: 1.0
Author: Stephen Sienko
Author URI: https://github.com/Donstesh
License: GPL2
*/

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

function weather_plugin_shortcode($atts)
{
    if (isset($_POST['weather_city'])) {
        $city = sanitize_text_field($_POST['weather_city']);
        setcookie('weather_city', $city, time() + (86400 * 30), "/"); // 30 days
    } elseif (isset($_COOKIE['weather_city'])) {
        $city = sanitize_text_field($_COOKIE['weather_city']);
    } else {
        $city = 'Nairobi'; 
    }

    $api_key = 'c302aa63b6614028a19153217242507'; 
    $api_url = "http://api.weatherapi.com/v1/current.json?key=$api_key&q=$city";

    $response = wp_remote_get($api_url);

    if (is_wp_error($response)) {
        return 'Unable to retrieve weather data.';
    }

    $body = wp_remote_retrieve_body($response);
    $data = json_decode($body, true);

    if (!isset($data['current'])) {
        return 'Invalid response from weather API.';
    }

    $current = $data['current'];

    ob_start();
    ?>
    <div class="weather-info">
        <h3>Weather in <?php echo esc_html($city); ?></h3>
        <p>Temperature: <?php echo esc_html($current['temp_c']); ?> °C</p>
        <p>Condition: <?php echo esc_html($current['condition']['text']); ?></p>
        <img src="<?php echo esc_url($current['condition']['icon']); ?>" alt="Weather icon">
    </div>
    <form method="post">
        <label for="weather_city">Select City: </label>
        <input type="text" name="weather_city" id="weather_city" value="<?php echo esc_attr($city); ?>">
        <input type="submit" value="Update">
    </form>
    <?php
    return ob_get_clean();
}

add_shortcode('weather', 'weather_plugin_shortcode');
