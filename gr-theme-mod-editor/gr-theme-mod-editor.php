<?php
/*
Plugin Name: Theme Mods Editor
Description: A plugin to edit theme mods data.
Version: 1.0
Author: Goro
*/

// プラグインを管理メニューに追加
add_action('admin_menu', 'theme_mods_editor_menu');
function theme_mods_editor_menu() {
    add_menu_page('GR Theme Mods Editor', 'GR Theme Mods Editor', 'manage_options', 'gr-theme-mods-editor', 'theme_mods_editor_page');
}

function theme_mods_editor_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }
    //
    // theme_modsデータを取得
    //
    // 現在のテーマのディレクトリ名を取得
    $theme = wp_get_theme();
    $theme_directory_name = $theme->get_template();

    // theme_modsオプション名を生成
    $theme_mods_option_name = 'theme_mods_' . $theme_directory_name;
    $theme_mods = get_option( $theme_mods_option_name, '' );
    $theme_mods_for_textarea = esc_textarea(json_encode($theme_mods, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    // フォームにnonceを追加（theme_mods_editor_page関数内）
    echo '<form method="post">';
    wp_nonce_field('save_theme_mods_nonce');

    // 保存処理
    if (isset($_POST['save_theme_mods'])) {
        check_admin_referer('save_theme_mods_nonce');
        
        $new_mods = $_POST['theme_mods'];
            // サニタイズ処理を追加
        $new_mods_sanitized = sanitize_text_field($new_mods);
        $new_serialized_mods = json_decode(stripslashes($new_mods_sanitized), true);

        if (is_null($new_serialized_mods)) {
            // JSONデコードエラーの処理
            echo '<div class="error"><p>Invalid JSON format.</p></div>';
        } else { 
            // データを更新
            update_option($theme_mods_option_name, $new_serialized_mods);
             // 成功メッセージを表示
            echo '<div class="updated"><p>Theme mods updated successfully.</p></div>';
            // 再度取得して最新データを表示
            $theme_mods = get_option( $theme_mods_option_name, '');
            $theme_mods_for_textarea = esc_textarea(json_encode($theme_mods, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }

    ?>
    <div class="wrap">
        <h1>Theme Mods Editor</h1>
        <form method="post">
            <?php wp_nonce_field('save_theme_mods_nonce'); ?>
            <textarea name="theme_mods" rows="20" cols="100"><?php echo $theme_mods_for_textarea; ?></textarea>
            <p><input type="submit" name="save_theme_mods" class="button-primary" value="Save Changes" /></p>
        </form>
    </div>
    <?php
}
?>