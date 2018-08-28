<?php

/**
 * デフォルトセットアップ
 */
function defaults_setup ()
{
    $opt = get_option('dsau_options');

    /**
     * タイトルタグサポート
     */
    if (!empty($opt['supportTitleTag'])) {
        add_theme_support('title-tag');
    }

    /**
     * サムネイルサポート
     */
    if (!empty($opt['supportPostThumbnails'])) {
        add_theme_support('post-thumbnails');
        if (!empty($opt['postThumbnailsSizes'])) {
            $sizesArray = explode("\n", $opt['postThumbnailsSizes']);
            $sizesArray2 = [];
            foreach ($sizesArray as $item) {
                $itemArray = explode(',', $item);
                $sizesArray2[] = [
                    "name"   => $itemArray[0],
                    "width"  => $itemArray[1],
                    "height" => $itemArray[2],
                    "crop"   => $itemArray[3]
                ];
            }
            register_image_sizes($sizesArray2);
        }
    }

    /**
     * 不要記述削除
     */
    remove_headers();
}

add_action('after_setup_theme', 'defaults_setup');

/**
 * JSとCSSを読み込む
 */
function set_org_scripts ()
{
    $scripts = [
        [
            "type"      => 'css',
            "handle"    => 'my-common',
            "src"       => '/assets/css/style.css',
            "in_footer" => false
        ],
        [
            "type"      => 'js',
            "handle"    => 'my-common',
            "src"       => '/assets/js/script.js',
            "in_footer" => true
        ]
    ];
    org_scripts($scripts);
    wp_enqueue_style('my-common', '/assets/css/style.css');
}

add_action('wp_enqueue_scripts', 'set_org_scripts');

/**
 * チェックの入っているカテゴリーを上部にもってくる機能を切る
 */
function wp_category_terms_checklist_no_top ($args, $post_id = null)
{
    $args['checked_ontop'] = false;
    return $args;
}

/*
* 任意のタクソノミーをラジオボタンに変更
*/
function change_taxonomy_input_type ()
{
    $opt = get_option('dsau_options');
    $ids = explode("\n", $opt['changeTaxonomyInputTypeId']);
    $classes = explode("\n", $opt['changeTaxonomyInputTypeClass']);
    $ids_arr_text = '';
    $classes_arr_text = '';
    foreach ($ids as $id) {
        $ids_arr_text .= "'" . $id . "',";
    }
    foreach ($classes as $class) {
        $classes_arr_text .= "'" . $class . "',";
    }
    $ids_arr_text = trim($ids_arr_text, ',');
    $classes_arr_text = trim($classes_arr_text, ',');
    ob_start(); ?>
    <script>
        /**
         * チェックボックスをラジオボタンに変更
         * @param  {Object} taxonomy チェックボックスを検索する基準の要素
         */
        var resetInputType = function (taxonomy) {
            if (!taxonomy) {
                return false;
            }
            var
                items = taxonomy.getElementsByTagName('input'),
                has_check = false,
                i;
            // 選択済みの項目があればhas_checkをtrueにする
            for (i = 0; i < items.length; i++) {
                if ((!has_check) && items[i].checked) {
                    has_check = true;
                }
            }
            for (i = 0; i < items.length; i++) {
                // ひとつも選択がない場合、ひとつめの項目をcheckedにする
                if (!has_check && i === 0) {
                    items[i].checked = true;
                }
                // input typeをradioに変更
                items[i].setAttribute('type', 'radio');
            }
        };

        /**
         * 詳細画面でresetInputTypeを実行
         * @param  {Array} elementNameList 処理を与えたい要素のIDを配列で指定
         */
        var changeInputType = function (elementNameList) {
            var elements = [], i;
            // タクソノミーのチェックリストを取得
            for (i = 0; i < elementNameList.length; i++) {
                elements.push(document.getElementById(elementNameList[i]));
            }
            // チェックリストの数 resetInputTypeを実行
            for (i = 0; i < elements.length; i++) {
                resetInputType(elements[i]);
            }
        };

        /**
         * クイック編集画面でresetInputTypeを実行
         * @param  {Array} elementNameList 処理を与えたい要素のクラスを配列で指定
         */
        var changeEditInputType = function (inlineEditPost, elementNameList) {
            // 元の edit の処理を取り出しておく
            var _Edit = inlineEditPost.edit;

            // edit 関数を書き換える
            inlineEditPost.edit = function (id) {
                // 元の edit の処理を行う
                _Edit.apply(inlineEditPost, arguments);

                if (typeof(id) === 'object') {
                    id = this.getId(id);
                }

                editRow = document.getElementById('edit-' + id);

                var elements = [], i;
                // タクソノミーのチェックリストを取得
                for (i = 0; i < elementNameList.length; i++) {
                    elements.push(editRow.getElementsByClassName(elementNameList[i])[0]);
                }
                // チェックリストの数 resetInputTypeを実行
                for (i = 0; i < elements.length; i++) {
                    resetInputType(elements[i]);
                }

                return false;
            };
        };

        /**
         * inlineEditPostを代入
         * @type {Object}
         */
        var inlineEditPost = inlineEditPost || null;

        /**
         * 初期化
         */
        var init = function () {
            var ids = [<?php echo $ids_arr_text; ?>];
            var classes = [<?php echo $classes_arr_text; ?>];

            // 詳細画面、先祖ulのIDを渡す
            changeInputType(ids);

            if (inlineEditPost && inlineEditPost.edit) {
                // クイック編集、先祖ulのクラスを渡す
                changeEditInputType(inlineEditPost, classes);
            }
        };
        setTimeout(init, 1000);
    </script>
    <?php
    $script = ob_get_contents();
    ob_end_clean();

    echo $script;
}

/**
 * 機能系関数実行
 */
function setFunction ()
{
    $opt = get_option('dsau_options');

    if (!empty($opt['checkedCategoryOnTop'])) {
        add_action('wp_terms_checklist_args', 'wp_category_terms_checklist_no_top');
    }

    /**
     * リッチエディタのみの表示にする
     */
    if (!empty($opt['onlyViewVisualEditor'])) {
        add_filter('wp_editor_settings', function ($settings) {
            if (user_can_richedit()) {
                $settings['quicktags'] = false;
            }
            return $settings;
        });
    }

    if (!empty($opt['changeTaxonomyInputType']) && !empty($opt['changeTaxonomyInputTypeId']) && !empty($opt['changeTaxonomyInputTypeClass'])) {
        add_action('admin_print_scripts-edit.php', 'change_taxonomy_input_type');
        add_action('admin_print_scripts-post.php', 'change_taxonomy_input_type');
        add_action('admin_print_scripts-post-new.php', 'change_taxonomy_input_type');
    }
}

setFunction();