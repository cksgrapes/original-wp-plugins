<?php

class DSAU_MetaBox
{
    protected $title;
    protected $table_rows;

    public function __construct ()
    {
    }

    public function set_title ($title)
    {
        $this->title = $title;
    }

    public function set_table_rows ($rows)
    {
        $this->table_rows = $rows;
    }

    public function reset ()
    {
        $this->title = null;
        $this->table_rows = null;
    }

    protected function formatting_table_row ($row)
    {
        ob_start();
        ?>
        <tr valign="top">
            <th scope="row"><label for="inputtext"><?php echo $row['title']; ?></label></th>
            <td>
                <?php echo $row['contents']; ?>
            </td>
        </tr>
        <?php
        $src = ob_get_contents();
        ob_end_clean();
        return $src;
    }

    public function get_table_row ($rows)
    {
        //配列の空を削除
        if (!empty($rows)) {
            $rows = array_filter($rows);
        }

        //$rowsが空ならば処理を終了
        if (empty($rows)) {
            return false;
        }

        $src = '';
        foreach ($rows as $row) {
            $src .= $this->formatting_table_row($row);
        }
        return $src;
    }

    public function view ()
    {
        ?>
        <div class="postbox">
            <h2 class="hndle ui-sortable-handle"><span><?php echo $this->title; ?></span></h2>
            <div class="inside">
                <table class="form-table">
                    <?php echo $this->get_table_row($this->table_rows); ?>
                </table>
            </div>
        </div>
        <?php
        $this->reset();
    }
}

class DSAU_View
{
    protected $my_post;
    protected $option_name;
    protected $action_name;

    public function __construct ()
    {
        $this->option_name = 'dsau_options';
        $this->action_name = 'rundsau';
    }

    protected function save ()
    {
        check_admin_referer($this->action_name);
        $opt = $_POST[$this->option_name];
        update_option($this->option_name, $opt);

        $format = '<div class="updated fade"><p><strong>%s</strong></p></div>';
        echo sprintf($format, __('Options saved.'));
    }

    protected function create_checkbox ($opt, $current_value)
    {
        $format = '<input type="checkbox" id="%2$s" name="%1$s[%2$s]"' . $current_value . '><label for="%2$s">%3$s</label>';
        return sprintf($format, $this->option_name, $opt['id'], $opt['label']);
    }

    protected function create_textarea ($opt, $current_value)
    {
        $format = '<p>%3$s：<br><textarea cols="50" rows="%4$s" name="%1$s[%2$s]">' . $current_value . '</textarea></p>';
        return sprintf($format, $this->option_name, $opt['id'], $opt['label'], $opt['rows']);
    }

    public function init ()
    {
        if (isset($_POST[$this->option_name])) {
            $this->save();
        }

        $meta_box = new DSAU_MetaBox();
        ?>
        <div class="wrap">
            <div id="icon-options-general" class="icon32"><br/></div>
            <h2>テーマ初期設定</h2>
            <form action="" method="post">
                <?php
                wp_nonce_field($this->action_name);

                $opt = get_option($this->option_name);
                $support_title_tag = isset($opt['supportTitleTag']) ? ' checked' : null;
                $support_post_thumbnails = isset($opt['supportPostThumbnails']) ? ' checked' : null;
                $post_thumbnails_sizes = isset($opt['postThumbnailsSizes']) ? $opt['postThumbnailsSizes'] : null;
                $checked_category_on_top = isset($opt['checkedCategoryOnTop']) ? ' checked' : null;
                $only_view_visual_editor = isset($opt['onlyViewVisualEditor']) ? ' checked' : null;
                $change_taxonomy_input_type = isset($opt['changeTaxonomyInputType']) ? ' checked' : null;
                $change_taxonomy_input_type_id = isset($opt['postThumbnailsSizes']) ? $opt['changeTaxonomyInputTypeId'] : null;
                $change_taxonomy_input_type_class = isset($opt['postThumbnailsSizes']) ? $opt['changeTaxonomyInputTypeClass'] : null;
                ?>
                <div id="poststuff">
                    <?php
                    /*
                     * 基本機能サポート
                     */
                    $meta_box->set_title('基本機能サポート');
                    $meta_box->set_table_rows([
                        [
                            'title'    => 'title-tag',
                            'contents' => $this->create_checkbox([
                                'id'    => 'supportTitleTag',
                                'label' => '使用する'
                            ], $support_title_tag)
                        ],
                        [
                            'title'    => 'post-thumbnails',
                            'contents' =>
                                $this->create_checkbox([
                                    'id'    => 'supportPostThumbnails',
                                    'label' => '使用する'
                                ], $support_post_thumbnails) .
                                $this->create_textarea([
                                    'id'    => 'postThumbnailsSizes',
                                    'label' => '追加サムネイルサイズ(name,width,height,crop)',
                                    'rows'  => 4
                                ], $post_thumbnails_sizes)
                        ]
                    ]);
                    $meta_box->view();

                    /*
                     * 記事詳細管理画面
                     */
                    $meta_box->set_title('記事詳細管理画面');
                    $meta_box->set_table_rows([
                        [
                            'title'    => 'カテゴリー',
                            'contents' => $this->create_checkbox([
                                'id'    => 'checkedCategoryOnTop',
                                'label' => 'チェックしたカテゴリを上部にもってこない'
                            ], $checked_category_on_top)
                        ],
                        [
                            'title'    => 'タクソノミー',
                            'contents' =>
                                $this->create_checkbox([
                                    'id'    => 'changeTaxonomyInputType',
                                    'label' => '任意のタクソノミーをラジオボタンにする'
                                ], $change_taxonomy_input_type) .
                                $this->create_textarea([
                                    'id'    => 'changeTaxonomyInputTypeId',
                                    'label' => '詳細画面用 ulのID',
                                    'rows'  => 4
                                ], $change_taxonomy_input_type_id) .
                                $this->create_textarea([
                                    'id'    => 'changeTaxonomyInputTypeClass',
                                    'label' => '一覧画面用用 ulのクラス',
                                    'rows'  => 4
                                ], $change_taxonomy_input_type_class)
                        ],
                        [
                            'title'    => '本文',
                            'contents' => $this->create_checkbox([
                                'id'    => 'onlyViewVisualEditor',
                                'label' => 'ビジュアルエディタのみ表示する'
                            ], $only_view_visual_editor)
                        ]
                    ]);
                    $meta_box->view();
                    ?>
                </div>
                <?php submit_button(); ?>
            </form>
            <!-- /.wrap --></div>
        <?php
    }
}

function dsau_options_page ()
{
    $dsau_view = new DSAU_View();
    $dsau_view->init();
}
