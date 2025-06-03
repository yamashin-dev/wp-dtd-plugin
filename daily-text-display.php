<?php
/**
 * Plugin Name: Daily Text Display
 * Plugin URI: https://github.com/yamashin-dev/wp-dtd-plugin
 * Description: 曜日ごとに異なるテキストを表示するプラグイン
 * Version: 1.0.0
 * Requires at least: 5.0
 * Requires PHP: 7.2
 * Author: やましん
 * Author URI: https://x.com/Yama_Shin_0216
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: daily-text-display
 * Domain Path: /languages
 *
 * @package Daily_Text_Display
 */

// 直接アクセス禁止
if (!defined('ABSPATH')) {
  exit;
}

// 管理画面の設定を読み込む
require_once plugin_dir_path(__FILE__) . 'admin/admin-settings.php';

// スタイルシートの読み込み
function dtd_enqueue_styles()
{
  // 現在時刻を時間単位で取得（1時間ごとにキャッシュを更新）
  $version = date('YmdH');
  wp_enqueue_style('dtd-styles', plugins_url('css/style.css', __FILE__), array(), $version);
}
add_action('wp_enqueue_scripts', 'dtd_enqueue_styles');

// プラグインの有効化時の処理
register_activation_hook(__FILE__, 'dtd_activation');
function dtd_activation()
{
  // デフォルト値の設定
      $default_texts = array(
        'monday' => array('text' => '', 'allow_html' => false),
        'tuesday' => array('text' => '', 'allow_html' => false),
        'wednesday' => array('text' => '', 'allow_html' => false),
        'thursday' => array('text' => '', 'allow_html' => false),
        'friday' => array('text' => '', 'allow_html' => false),
        'saturday' => array('text' => '', 'allow_html' => false),
        'sunday' => array('text' => '', 'allow_html' => false)
    );

  if (!get_option('dtd_daily_texts')) {
    add_option('dtd_daily_texts', $default_texts);
  }

  // 表示設定のデフォルト値
  $default_display = array(
    'position' => 'header_inside_top',
    'title' => '本日のメッセージ',
    'enable_display' => true
  );

  if (!get_option('dtd_display_settings')) {
    add_option('dtd_display_settings', $default_display);
  }
}

// フロントエンドでのテキスト表示
function dtd_display_daily_text()
{
  $daily_texts = get_option('dtd_daily_texts');
  $display_settings = get_option('dtd_display_settings', array(
    'position' => 'header_inside_top',
    'title' => '本日のメッセージ',
    'enable_display' => true
  ));

  // テキスト表示が無効の場合は何も表示しない
  if (!($display_settings['enable_display'] ?? true)) {
    return;
  }

  $current_day = strtolower(date('l'));

      if (!empty($daily_texts[$current_day]['text'])) {
        $text = $daily_texts[$current_day]['text'];
        $allow_html = $daily_texts[$current_day]['allow_html'] ?? false;

        // HTMLが許可されている場合は wp_kses_post でサニタイズ、そうでない場合は esc_html を使用
        $formatted_text = $allow_html ? wp_kses_post($text) : nl2br(esc_html($text));
        $title = esc_html($display_settings['title'] ?? '本日のメッセージ');
        $hide_title = $display_settings['hide_title'] ?? false;
        $design_pattern = $display_settings['design_pattern'] ?? 'pattern1';
        $main_color = $display_settings['main_color'] ?? '#333333';
        $accent_color = $display_settings['accent_color'] ?? '#007bff';

        // デバッグ情報を出力
        error_log('Daily Text Display - Title: ' . $title);
        error_log('Daily Text Display - Hide Title: ' . ($hide_title ? 'true' : 'false'));

        // タイトルをdivタグで出力
        $title_html = $hide_title ? '<div class="daily-text-title" style="display: none;"><span>' . $title . '</span></div>' : '<div class="daily-text-title"><span>' . $title . '</span></div>';

        // sprintfを使わずに直接HTMLを構築
        $output = '<div class="daily-text-wrapper ' . esc_attr($design_pattern) . '" style="--dtd-main-color: ' . esc_attr($main_color) . '; --dtd-accent-color: ' . esc_attr($accent_color) . ';">';
        $output .= '<div class="daily-text-content">';
        $output .= $title_html;
        $output .= '<div class="daily-text-message"><span>' . $formatted_text . '</span></div>';
        $output .= '</div>'; // daily-text-contentの終了
        $output .= '</div>'; // daily-text-wrapperの終了

        error_log('Daily Text Display - Final Output: ' . $output);

    // JavaScriptを使用して適切な位置に挿入
?>
    <script>
      document.addEventListener('DOMContentLoaded', function() {
        var dailyText = <?php echo json_encode($output); ?>;
        var position = <?php echo json_encode($display_settings['position']); ?>;
        var targetElement;

        function findHeader() {
          return document.querySelector('header') ||
            document.querySelector('.header') ||
            document.querySelector('.site-header') ||
            document.querySelector('#header');
        }

        function findFooter() {
          return document.querySelector('footer') ||
            document.querySelector('.footer') ||
            document.querySelector('.site-footer') ||
            document.querySelector('#footer');
        }

        switch (position) {
          case 'header_above':
            targetElement = findHeader();
            if (targetElement) {
              targetElement.insertAdjacentHTML('beforebegin', dailyText);
            } else {
              document.body.insertAdjacentHTML('afterbegin', dailyText);
            }
            break;

          case 'header_inside_top':
            targetElement = findHeader();
            if (targetElement) {
              targetElement.insertAdjacentHTML('afterbegin', dailyText);
            }
            break;

          case 'header_inside_bottom':
            targetElement = findHeader();
            if (targetElement) {
              targetElement.insertAdjacentHTML('beforeend', dailyText);
            }
            break;

          case 'footer_above':
            targetElement = findFooter();
            if (targetElement) {
              targetElement.insertAdjacentHTML('beforebegin', dailyText);
            }
            break;

          case 'footer_inside_top':
            targetElement = findFooter();
            if (targetElement) {
              targetElement.insertAdjacentHTML('afterbegin', dailyText);
            }
            break;

          case 'footer_inside_bottom':
            targetElement = findFooter();
            if (targetElement) {
              targetElement.insertAdjacentHTML('beforeend', dailyText);
            }
            break;

          case 'footer_below':
            targetElement = findFooter();
            if (targetElement) {
              targetElement.insertAdjacentHTML('afterend', dailyText);
            }
            break;

          default: // header_inside_top
            targetElement = findHeader();
            if (targetElement) {
              targetElement.insertAdjacentHTML('afterbegin', dailyText);
            }
            break;
        }
      });
    </script>
<?php
  }
}

// 表示位置に応じてフックを設定
add_action('wp_footer', 'dtd_display_daily_text');

// ショートコード機能の追加
function dtd_shortcode($atts) {
    $atts = shortcode_atts(array(
        'day' => strtolower(date('l')), // デフォルトは現在の曜日
        'pattern' => '', // デザインパターン
        'main_color' => '', // メインカラー
        'accent_color' => '' // アクセントカラー
    ), $atts);

    $daily_texts = get_option('dtd_daily_texts');
    $display_settings = get_option('dtd_display_settings', array(
        'title' => '本日のメッセージ',
        'enable_display' => true,
        'design_pattern' => 'pattern1',
        'main_color' => '#333333',
        'accent_color' => '#007bff'
    ));

    // ショートコードのパラメータで上書き
    if (!empty($atts['pattern'])) {
        $display_settings['design_pattern'] = sanitize_text_field($atts['pattern']);
    }
    if (!empty($atts['main_color'])) {
        $display_settings['main_color'] = sanitize_hex_color($atts['main_color']);
    }
    if (!empty($atts['accent_color'])) {
        $display_settings['accent_color'] = sanitize_hex_color($atts['accent_color']);
    }

    // 指定された曜日のテキストを取得
    $day = strtolower($atts['day']);
    $valid_days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');

    // 無効な曜日が指定された場合は空文字を返す
    if (!in_array($day, $valid_days)) {
        return '';
    }

    // 現在の曜日を取得
    $current_day = strtolower(date('l'));

    // 曜日が指定されていて、現在の曜日と異なる場合は空文字を返す
    if ($day !== $current_day) {
        return '';
    }

    if (!empty($daily_texts[$day]['text'])) {
        $text = $daily_texts[$day]['text'];
        $allow_html = $daily_texts[$day]['allow_html'] ?? false;

        // HTMLが許可されている場合は wp_kses_post でサニタイズ、そうでない場合は esc_html を使用
        $formatted_text = $allow_html ? wp_kses_post($text) : nl2br(esc_html($text));
        $title = esc_html($display_settings['title'] ?? '本日のメッセージ');
        $hide_title = $display_settings['hide_title'] ?? false;
        $design_pattern = $display_settings['design_pattern'] ?? 'pattern1';
        $main_color = $display_settings['main_color'] ?? '#333333';
        $accent_color = $display_settings['accent_color'] ?? '#007bff';

        // デバッグ情報を出力
        error_log('Daily Text Display - Title: ' . $title);
        error_log('Daily Text Display - Hide Title: ' . ($hide_title ? 'true' : 'false'));

        // タイトルをdivタグで出力
        $title_html = $hide_title ? '<div class="daily-text-title" style="display: none;"><span>' . $title . '</span></div>' : '<div class="daily-text-title"><span>' . $title . '</span></div>';

        // sprintfを使わずに直接HTMLを構築
        $output = '<div class="daily-text-wrapper ' . esc_attr($design_pattern) . '" style="--dtd-main-color: ' . esc_attr($main_color) . '; --dtd-accent-color: ' . esc_attr($accent_color) . ';">';
        $output .= '<div class="daily-text-content">';
        $output .= $title_html;
        $output .= '<div class="daily-text-message"><span>' . $formatted_text . '</span></div>';
        $output .= '</div>'; // daily-text-contentの終了
        $output .= '</div>'; // daily-text-wrapperの終了

        return $output;
    }

    return '';
}
add_shortcode('daily_text', 'dtd_shortcode');
