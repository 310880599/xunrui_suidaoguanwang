<?php namespace Phpcmf\Controllers\Admin;

class Home extends \Phpcmf\App
{

    public function index() {

        $module = \Phpcmf\Service::L('cache')->get('module-'.SITE_ID.'-content');
        if (!$module) {
            $this->_admin_msg(0, dr_lang('未安装任何内容模块'));
        }

        $data = \Phpcmf\Service::M('app')->get_config(APP_DIR);

        if (IS_AJAX_POST) {

            $post = \Phpcmf\Service::L('input')->post('data');
            \Phpcmf\Service::M('app')->save_config(APP_DIR, $post);

            $this->_json(1, dr_lang('操作成功'));
        }

        $page = intval(\Phpcmf\Service::L('input')->get('page'));

        \Phpcmf\Service::V()->assign([
            'page' => $page,
            'data' => $data,
            'form' => dr_form_hidden(['page' => $page]),
            'menu' => \Phpcmf\Service::M('auth')->_admin_menu(
                [
                    '插件设置' => [APP_DIR.'/'.\Phpcmf\Service::L('Router')->class.'/index', 'fa fa-cog'],
                ]
            ),
            'module' => $module
        ]);
        \Phpcmf\Service::V()->display('config.html');
    }


    // 同步模块
    public function edit() {

        $mid = dr_safe_filename($_GET['mid']);
        $row = \Phpcmf\Service::M('Module')->table('module')->where('dirname', $mid)->getRow();
        if (!$row) {
            $this->_json(0, dr_lang('此模块[%s]未安装', $mid));
        }

        $ids = \Phpcmf\Service::L('input')->get('ids');
        if (!$ids) {
            $this->_json(0, dr_lang('所选内容不存在'));
        }

        $site = dr_string2array($row['site']);

        // 计算可用站点信息
        $list = [];
        foreach ($this->site_info as $siteid => $t) {
            $module = \Phpcmf\Service::L('cache')->get('module-'.$siteid.'-'.$mid);
            if (isset($site[$siteid]) && $module) {
                $list[$siteid] = [
                    'name' => $t['SITE_NAME'],
                    'select' => \Phpcmf\Service::L('Tree')->select_category(
                        $module['category'],
                        0,
                        'name="data['.$siteid.'][catid]"',
                        '-不同步-',
                        1, 1
                    ),
                ];
            }
        }

        if (IS_POST) {

            $ct = 0;
            $post = \Phpcmf\Service::L('input')->post('data');
            foreach ($ids as $id) {
                $this->_module_init($mid, SITE_ID);
                $data = $this->content_model->get_data($id);
                foreach ($list as $siteid => $t) {
                    if ($post[$siteid]['catid']) {
                        // 初始化站点模块
                        $this->_module_init($mid, $siteid);

                        $fields = [];
                        // 主表字段
                        $fields[1] = $this->get_cache('table-'.$siteid, $this->content_model->dbprefix($siteid.'_'.$mid));
                        $cache = $this->get_cache('table-'.$siteid, $this->content_model->dbprefix($siteid.'_'.$mid.'_category_data'));
                        $cache && $fields[1] = array_merge($fields[1], $cache);

                        // 附表字段
                        $fields[0] = $this->get_cache('table-'.$siteid, $this->content_model->dbprefix($siteid.'_'.$mid.'_data_0'));
                        $cache = $this->get_cache('table-'.$siteid, $this->content_model->dbprefix($siteid.'_'.$mid.'_category_data_0'));
                        $cache && $fields[0] = array_merge($fields[0], $cache);

                        // 去重复
                        $fields[0] = array_unique($fields[0]);
                        $fields[1] = array_unique($fields[1]);

                        $save = [];

                        // 主表附表归类
                        foreach ($fields as $ismain => $field) {
                            foreach ($field as $name) {
                                isset($data[$name]) && $save[$ismain][$name] = $data[$name];
                            }
                        }

                        $save[1]['uid'] = $save[0]['uid'] = $data['uid'];
                        $save[1]['catid'] = $save[0]['catid'] = $post[$siteid]['catid'];

                        $save[1]['url'] = '';
                        $save[1]['status'] = 9; //9表示正常发布，1表示审核里面
                        $save[1]['hits'] = 0;
                        $save[1]['displayorder'] = 0;
                        $save[1]['link_id'] = 0;
                        $save[1]['inputtime'] = $save[1]['updatetime'] = SYS_TIME;
                        $save[1]['inputip'] = '127.0.0.1';

                        $rt = $this->content_model->save_content(0, $save);

                        if ($rt['code']) {
                            $ct++;
                        }
                    }
                }
            }
            $this->_json(1, dr_lang('本次同步%s条数据', $ct));
            exit;
        }

        \Phpcmf\Service::V()->assign([
            'ids' => $ids,
            'list' => $list,
            'form' => dr_form_hidden(),
        ]);
        \Phpcmf\Service::V()->display('sync.html');exit;
    }

}
