<?php

// 直接アクセス禁止
if (!defined('ABSPATH')) {
  exit;
}

// 管理メニューに追加
add_action('admin_menu', 'dtd_add_admin_menu');
function dtd_add_admin_menu()
{
  $hook = add_menu_page(
    '曜日別テキスト設定',
    '曜日別テキスト',
    'manage_options',
    'daily-text-display',
    'dtd_admin_page',
    'dashicons-text'
  );

  // 管理画面用のスタイルとスクリプトを読み込む
  add_action('admin_print_styles-' . $hook, 'dtd_admin_styles');
  add_action('admin_enqueue_scripts', 'dtd_admin_scripts');
}

// 管理画面用のスタイル
function dtd_admin_styles()
{
?>

<?php
}

// 管理画面用のスクリプト
function dtd_admin_scripts($hook)
{
  if ('toplevel_page_daily-text-display' !== $hook) {
    return;
  }
  wp_enqueue_script('jquery');
}

// 管理画面の表示
function dtd_admin_page()
{
  // 現在のタブを取得
  $current_tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : 'text';

  // 保存処理
  if (isset($_POST['dtd_save_settings']) && check_admin_referer('dtd_settings_nonce')) {
    if ($current_tab === 'text') {
      // テキスト設定の保存
      $daily_texts = array();
      $days = array('monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday');

      foreach ($days as $day) {
        $allow_html = isset($_POST['dtd_' . $day]['allow_html']);
        $text = $_POST['dtd_' . $day]['text'] ?? '';

        // HTMLが許可されている場合はwp_kses_postを使用、そうでない場合はsanitize_textarea_fieldを使用
        $sanitized_text = $allow_html ? wp_kses_post($text) : sanitize_textarea_field($text);

        $daily_texts[$day] = array(
          'text' => $sanitized_text,
          'allow_html' => $allow_html
        );
      }
      update_option('dtd_daily_texts', $daily_texts);
    } elseif ($current_tab === 'display') {
      // 表示設定の保存
      $display_settings = get_option('dtd_display_settings', array());
      $display_settings['position'] = sanitize_text_field($_POST['dtd_position'] ?? 'header_below');
      $display_settings['title'] = sanitize_text_field($_POST['dtd_title'] ?? '本日のメッセージ');
      $display_settings['hide_title'] = isset($_POST['dtd_hide_title']);
      $display_settings['enable_display'] = isset($_POST['dtd_enable_display']);
      update_option('dtd_display_settings', $display_settings);
    } elseif ($current_tab === 'design') {
      // デザイン設定の保存
      $display_settings = get_option('dtd_display_settings', array());
      $display_settings['design_pattern'] = sanitize_text_field($_POST['dtd_design_pattern'] ?? 'pattern1');
      $display_settings['main_color'] = sanitize_hex_color($_POST['dtd_main_color'] ?? '#333333');
      $display_settings['accent_color'] = sanitize_hex_color($_POST['dtd_accent_color'] ?? '#007bff');
      update_option('dtd_display_settings', $display_settings);
    }
    echo '<div class="updated"><p>設定を保存しました。</p></div>';
  }

  // 現在の設定を取得
  $daily_texts = get_option('dtd_daily_texts', array());
  $display_settings = get_option('dtd_display_settings', array(
    'position' => 'header_below',
    'title' => '本日のメッセージ',
    'hide_title' => false,
    'enable_display' => true
  ));

?>
  <div class="wrap">
    <h1>曜日別テキスト設定</h1>

    <div class="dtd-tabs">
      <nav class="nav-tab-wrapper">
        <a href="?page=daily-text-display&tab=text" class="nav-tab <?php echo $current_tab === 'text' ? 'nav-tab-active' : ''; ?>">出力テキスト</a>
        <a href="?page=daily-text-display&tab=display" class="nav-tab <?php echo $current_tab === 'display' ? 'nav-tab-active' : ''; ?>">表示設定</a>
        <a href="?page=daily-text-display&tab=design" class="nav-tab <?php echo $current_tab === 'design' ? 'nav-tab-active' : ''; ?>">デザイン</a>
        <a href="?page=daily-text-display&tab=shortcode" class="nav-tab <?php echo $current_tab === 'shortcode' ? 'nav-tab-active' : ''; ?>">ショートコード</a>
      </nav>

      <?php if ($current_tab === 'text'): ?>
        <div id="dtd-text-settings" class="dtd-tab-content active">
          <form method="post" action="">
            <?php wp_nonce_field('dtd_settings_nonce'); ?>
            <table class="form-table">
              <tr>
                <th>月曜日のテキスト</th>
                <td>
                  <textarea name="dtd_monday[text]" rows="3" cols="50"><?php echo esc_textarea($daily_texts['monday']['text'] ?? ''); ?></textarea>
                  <br>
                  <label>
                    <input type="checkbox" name="dtd_monday[allow_html]" value="1" <?php checked($daily_texts['monday']['allow_html'] ?? false); ?>>
                    HTMLタグを許可する
                  </label>
                </td>
              </tr>
              <tr>
                <th>火曜日のテキスト</th>
                <td>
                  <textarea name="dtd_tuesday[text]" rows="3" cols="50"><?php echo esc_textarea($daily_texts['tuesday']['text'] ?? ''); ?></textarea>
                  <br>
                  <label>
                    <input type="checkbox" name="dtd_tuesday[allow_html]" value="1" <?php checked($daily_texts['tuesday']['allow_html'] ?? false); ?>>
                    HTMLタグを許可する
                  </label>
                </td>
              </tr>
              <tr>
                <th>水曜日のテキスト</th>
                <td>
                  <textarea name="dtd_wednesday[text]" rows="3" cols="50"><?php echo esc_textarea($daily_texts['wednesday']['text'] ?? ''); ?></textarea>
                  <br>
                  <label>
                    <input type="checkbox" name="dtd_wednesday[allow_html]" value="1" <?php checked($daily_texts['wednesday']['allow_html'] ?? false); ?>>
                    HTMLタグを許可する
                  </label>
                </td>
              </tr>
              <tr>
                <th>木曜日のテキスト</th>
                <td>
                  <textarea name="dtd_thursday[text]" rows="3" cols="50"><?php echo esc_textarea($daily_texts['thursday']['text'] ?? ''); ?></textarea>
                  <br>
                  <label>
                    <input type="checkbox" name="dtd_thursday[allow_html]" value="1" <?php checked($daily_texts['thursday']['allow_html'] ?? false); ?>>
                    HTMLタグを許可する
                  </label>
                </td>
              </tr>
              <tr>
                <th>金曜日のテキスト</th>
                <td>
                  <textarea name="dtd_friday[text]" rows="3" cols="50"><?php echo esc_textarea($daily_texts['friday']['text'] ?? ''); ?></textarea>
                  <br>
                  <label>
                    <input type="checkbox" name="dtd_friday[allow_html]" value="1" <?php checked($daily_texts['friday']['allow_html'] ?? false); ?>>
                    HTMLタグを許可する
                  </label>
                </td>
              </tr>
              <tr>
                <th>土曜日のテキスト</th>
                <td>
                  <textarea name="dtd_saturday[text]" rows="3" cols="50"><?php echo esc_textarea($daily_texts['saturday']['text'] ?? ''); ?></textarea>
                  <br>
                  <label>
                    <input type="checkbox" name="dtd_saturday[allow_html]" value="1" <?php checked($daily_texts['saturday']['allow_html'] ?? false); ?>>
                    HTMLタグを許可する
                  </label>
                </td>
              </tr>
              <tr>
                <th>日曜日のテキスト</th>
                <td>
                  <textarea name="dtd_sunday[text]" rows="3" cols="50"><?php echo esc_textarea($daily_texts['sunday']['text'] ?? ''); ?></textarea>
                  <br>
                  <label>
                    <input type="checkbox" name="dtd_sunday[allow_html]" value="1" <?php checked($daily_texts['sunday']['allow_html'] ?? false); ?>>
                    HTMLタグを許可する
                  </label>
                </td>
              </tr>
            </table>
            <p class="submit">
              <input type="submit" name="dtd_save_settings" class="button-primary" value="設定を保存">
            </p>
          </form>
        </div>
      <?php endif; ?>

      <?php if ($current_tab === 'shortcode'): ?>
        <div id="dtd-shortcode-settings" class="dtd-tab-content active">
          <h2>ショートコードの使い方</h2>
          <div class="dtd-shortcode-instructions">
            <p>以下のショートコードを使用して、任意の場所やデザインでメッセージを表示できます</p>

            <h3>基本的な使い方</h3>
            <p>現在の曜日のメッセージを表示：</p>
            <code>[daily_text]</code>

            <h3>特定の曜日のメッセージを表示</h3>
            <p>曜日を指定してメッセージを表示：</p>
            <ul>
              <li><code>[daily_text day="monday"]</code> - 月曜日のメッセージ</li>
              <li><code>[daily_text day="tuesday"]</code> - 火曜日のメッセージ</li>
              <li><code>[daily_text day="wednesday"]</code> - 水曜日のメッセージ</li>
              <li><code>[daily_text day="thursday"]</code> - 木曜日のメッセージ</li>
              <li><code>[daily_text day="friday"]</code> - 金曜日のメッセージ</li>
              <li><code>[daily_text day="saturday"]</code> - 土曜日のメッセージ</li>
              <li><code>[daily_text day="sunday"]</code> - 日曜日のメッセージ</li>
            </ul>
            <p>※指定した曜日以外の日には表示されません。</p>

            <h3>デザイン設定</h3>
            <p>デザインをカスタマイズして表示：</p>
            <ul>
              <li><code>[daily_text pattern="pattern1"]</code> - シンプルデザイン</li>
              <li><code>[daily_text pattern="pattern2"]</code> - モダンデザイン</li>
              <li><code>[daily_text pattern="pattern3"]</code> - クラシックデザイン</li>
              <li><code>[daily_text pattern="pattern4"]</code> - ミニマルデザイン</li>
              <li><code>[daily_text pattern="pattern5"]</code> - ボーダーデザイン</li>
              <li><code>[daily_text pattern="pattern6"]</code> - グラデーションデザイン</li>
              <li><code>[daily_text pattern="pattern7"]</code> - ニューモフィズム</li>
            </ul>

            <h3>カラー設定</h3>
            <p>メインカラーとアクセントカラーを指定：</p>
            <ul>
              <li><code>[daily_text main_color="#ff0000"]</code> - メインカラーを赤に設定</li>
              <li><code>[daily_text accent_color="#00ff00"]</code> - アクセントカラーを緑に設定</li>
            </ul>

            <h3>組み合わせ例</h3>
            <p>複数の設定を組み合わせて使用：</p>
            <pre>
月曜日のメッセージをモダンデザインで表示：
[daily_text day="monday" pattern="pattern2"]

カスタムカラーでグラデーションデザイン：
[daily_text pattern="pattern6" main_color="#ff0000" accent_color="#00ff00"]

すべての設定を組み合わせる：
[daily_text day="monday" pattern="pattern2" main_color="#ff0000" accent_color="#00ff00"]
            </pre>

            <h3>使用例</h3>
            <p>投稿や固定ページの本文内で使用：</p>
            <pre>
今日のメッセージ：
[daily_text]

月曜日のメッセージ：
[daily_text day="monday"]

カスタムデザインのメッセージ：
[daily_text pattern="pattern2" main_color="#ff0000" accent_color="#00ff00"]
            </pre>
            <p>※ショートコードの場合、「表示設定」のテキスト表示のオンオフに関わらず表示されます。</p>

            <div class="notice notice-info">
              <p>注意：指定した曜日のメッセージが設定されていない場合は何も表示されません。</p>
            </div>
          </div>
        </div>
      <?php endif; ?>

      <?php if ($current_tab === 'display'): ?>
        <div id="dtd-display-settings" class="dtd-tab-content active">
          <form method="post" action="">
            <?php wp_nonce_field('dtd_settings_nonce'); ?>
            <table class="form-table">
              <tr>
                <th>テキスト表示</th>
                <td>
                  <label>
                    <input type="checkbox" name="dtd_enable_display" value="1" <?php checked($display_settings['enable_display'] ?? true); ?>>
                    テキストを表示する
                  </label>
                  <p class="description">チェックを外すと、すべてのテキスト表示が無効になります。</p>
                </td>
              </tr>
              <tr>
                <th>メッセージのタイトル</th>
                <td>
                  <input type="text" name="dtd_title" value="<?php echo esc_attr($display_settings['title'] ?? '本日のメッセージ'); ?>" class="regular-text">
                  <br>
                  <p class="description">表示されるメッセージのタイトルを設定してください。デフォルト：本日のメッセージ</p>
                  <label>
                    <input type="checkbox" name="dtd_hide_title" value="1" <?php checked($display_settings['hide_title'] ?? false); ?>>
                    タイトルを表示しない
                  </label>
                </td>
              </tr>
              <tr>
                <th>テキストの表示位置</th>
                <td>
                  <select name="dtd_position">
                    <option value="header_inside_top" <?php selected($display_settings['position'], 'header_inside_top'); ?>>ヘッダー内（上部）（デフォルト）</option>
                    <option value="header_inside_bottom" <?php selected($display_settings['position'], 'header_inside_bottom'); ?>>ヘッダー内（下部）</option>
                    <option value="footer_inside_top" <?php selected($display_settings['position'], 'footer_inside_top'); ?>>フッター内（上部）</option>
                    <option value="footer_inside_bottom" <?php selected($display_settings['position'], 'footer_inside_bottom'); ?>>フッター内（下部）</option>
                  </select>
                  <p class="description">テキストを表示する位置を選択してください。ヘッダー/フッター内に表示する場合、テーマによっては正しく表示されない場合があります。</p>
                </td>
              </tr>
            </table>
            <p class="submit">
              <input type="submit" name="dtd_save_settings" class="button-primary" value="設定を保存">
            </p>
          </form>
        </div>
      <?php endif; ?>

      <?php if ($current_tab === 'design'): ?>
        <div id="dtd-design-settings" class="dtd-tab-content active">
          <form method="post" action="">
            <?php wp_nonce_field('dtd_settings_nonce'); ?>
            <table class="form-table">
              <tr>
                <th>デザインパターン</th>
                <td>
                  <?php
                  $patterns = array(
                    'pattern1' => 'シンプル',
                    'pattern2' => 'モダン',
                    'pattern3' => 'クラシック',
                    'pattern4' => 'ミニマル',
                    'pattern5' => 'ボーダー',
                    'pattern6' => 'グラデーション',
                    'pattern7' => 'シャドウ'
                  );
                  ?>
                  <select name="dtd_design_pattern" id="dtd-design-pattern">
                    <?php foreach ($patterns as $pattern => $name): ?>
                      <option value="<?php echo esc_attr($pattern); ?>" <?php selected($display_settings['design_pattern'] ?? 'pattern1', $pattern); ?>><?php echo esc_html($name); ?></option>
                    <?php endforeach; ?>
                  </select>
                  <p class="description">メッセージのデザインパターンを選択してください。</p>
                </td>
              </tr>
              <tr>
                <th>メインカラー</th>
                <td>
                  <input type="color" name="dtd_main_color" id="dtd-main-color" value="<?php echo esc_attr($display_settings['main_color'] ?? '#333333'); ?>" class="dtd-color-picker">
                  <p class="description">メインカラーを選択してください。</p>
                </td>
              </tr>
              <tr>
                <th>アクセントカラー</th>
                <td>
                  <input type="color" name="dtd_accent_color" id="dtd-accent-color" value="<?php echo esc_attr($display_settings['accent_color'] ?? '#007bff'); ?>" class="dtd-color-picker">
                  <p class="description">アクセントカラーを選択してください。</p>
                </td>
              </tr>
            </table>

            <p class="submit">
              <input type="submit" name="dtd_save_settings" class="button-primary" value="設定を保存">
            </p>
          </form>
        </div>
      <?php endif; ?>
    </div>
  </div>
<?php
}
