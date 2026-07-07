<?php namespace Phpcmf\Controllers\Admin;

class Home extends \Phpcmf\Table
{
    private $form; // 表单验证配置

    public function __construct(...$params) {
        parent::__construct(...$params);
        \Phpcmf\Service::V()->assign('menu', \Phpcmf\Service::M('auth')->_admin_menu(
            [
                '多网站管理' => ['sites/home/index', 'fa fa-share-alt'],
                '创建站点' => ['sites/home/add', 'fa fa-plus'],
                '域名绑定说明' => ['sites/home/bang_index', 'fa fa-code'],
                'help' => ['384'],
            ]
        ));
        // 表单验证配置
        $this->form = [
            'name' => [
                'name' => '站点名称',
                'rule' => [
                    'empty' => dr_lang('站点名称不能为空')
                ],
                'filter' => [],
                'length' => '200'
            ],
            'domain' => [
                'name' => '域名地址',
                'filter' => [],
                'length' => '200'
            ],
        ];
    }

    public function index() {

        $this->_init([
            'table' => 'site',
            'order_by' => 'displayorder ASC,id ASC',
        ]);
        $this->_List();
        \Phpcmf\Service::V()->display('site_index.html');
    }

    public function add() {

        if (IS_AJAX_POST) {
            $data = $this->_validation(\Phpcmf\Service::L('input')->post('data'));
            \Phpcmf\Service::L('input')->system_log('创建网站('.$data['name'].')');
            \Phpcmf\Service::M('site')->create($data);
            exit($this->_json(1, dr_lang('操作成功，请手动更新缓存')));
        }

        \Phpcmf\Service::V()->assign([
            'form' => dr_form_hidden()
        ]);
        \Phpcmf\Service::V()->display('site_add.html');
    }

    // 隐藏或者启用
    public function hidden_edit() {

        $id = (int)\Phpcmf\Service::L('input')->get('id');
        $row = \Phpcmf\Service::M()->table('site')->get($id);
        if (!$row) {
            $this->_json(0, dr_lang('站点数据不存在'));
        }

        $v = $row['disabled'] ? 0 : 1;
        \Phpcmf\Service::M('Site')->table('site')->update($id, ['disabled' => $v]);
        \Phpcmf\Service::M('cache')->sync_cache('');

        exit($this->_json(1, dr_lang($v ? '站点已被禁用' : '站点已被启用'), ['value' => $v]));
    }

    public function del() {

        $ids = \Phpcmf\Service::L('input')->get_post_ids();
        if (!$ids) {
            exit($this->_json(0, dr_lang('你还没有选择呢')));
        } elseif (in_array(1, $ids)) {
            exit($this->_json(0, dr_lang('主站不能删除')));
        }

        $rt = \Phpcmf\Service::M('sites', 'sites')->delete_site($ids);
        if (!$rt['code']) {
            exit($this->_json(0, $rt['msg']));
        }

        \Phpcmf\Service::M('cache')->sync_cache('');
        \Phpcmf\Service::L('input')->system_log('批量删除站点: '. @implode(',', $ids));

        exit($this->_json(1, dr_lang('操作成功'), ['ids' => $ids]));
    }

    // 排序
    public function displayorder_edit() {

        // 查询数据
        $id = (int)\Phpcmf\Service::L('input')->get('id');
        $row = \Phpcmf\Service::M()->table('site')->get($id);
        if (!$row) {
            $this->_json(0, dr_lang('数据#%s不存在', $id));
        }

        $value = (int)\Phpcmf\Service::L('input')->get('value');
        $rt = \Phpcmf\Service::M()->table('site')->save($id, 'displayorder', $value);
        if (!$rt['code']) {
            $this->_json(0, $rt['msg']);
        }

        \Phpcmf\Service::M('cache')->sync_cache(''); // 自动更新缓存
        \Phpcmf\Service::L('input')->system_log('修改站点('.$row['name'].')的排序值为'.$value);
        $this->_json(1, dr_lang('操作成功'));
    }

    public function edit() {

        $ids = \Phpcmf\Service::L('input')->get_post_ids();
        if (!$ids) {
            exit($this->_json(0, dr_lang('你还没有选择呢')));
        }

        $data = \Phpcmf\Service::M()->db->table('site')->whereIn('id', $ids)->get()->getResultArray();
        $value = \Phpcmf\Service::L('input')->post('data', true);
        foreach ($data as $t) {
            $id = $t['id'];
            $t['setting'] = dr_string2array($t['setting']);
            $t['setting']['webpath'] = $id > 1 ? $value[$id]['webpath'] : '';
            \Phpcmf\Service::M()->db->table('site')->where('id', $id)->update([
                'name' => $value[$id]['name'] ? $value[$id]['name'] : '未知',
                'domain' => $value[$id]['domain'] ? $value[$id]['domain'] : 'null',
                'setting' => dr_array2string($t['setting'])
            ]);
        }

        \Phpcmf\Service::M('cache')->sync_cache('');
        \Phpcmf\Service::L('input')->system_log('批量修改站点: '. @implode(',', $ids));

        exit($this->_json(1, dr_lang('操作成功')));
    }

    public function bang_index() {
        \Phpcmf\Service::V()->display('site_bang.html');
    }

    public function menu_index() {

        $id = intval($_GET['id']);
        $data = \Phpcmf\Service::M('menu')->gets('admin');

        if (IS_POST) {
            $ids = \Phpcmf\Service::L('input')->get_post_ids();
            if (!$ids) {
                $this->_json(0, dr_lang('你还没有选择呢'));
            }
            foreach ($data as $t) {
                $site = dr_string2array($t['site']);
                if (in_array($t['id'], $ids)) {
                    // 增加权限
                    $site[$id] = $id;
                } else {
                    // 取消权限
                    unset($site[$id]);
                }
                \Phpcmf\Service::M()->db->table('admin_menu')->where('id', $t['id'])->update([
                    'site' => dr_array2string($site)
                ]);
            }
            \Phpcmf\Service::M('cache')->sync_cache(''); // 自动更新缓存
            $this->_json(1, dr_lang('操作成功'));
        }

        \Phpcmf\Service::V()->assign([
            'data' => $data,
            'siteid' => $id,
        ]);
        \Phpcmf\Service::V()->display('menu_index.html');exit;
    }



    // 验证数据
    private function _validation($data) {

        list($data, $return) = \Phpcmf\Service::L('Form')->validation($data, $this->form);
        if ($return) {
            $this->_json(0, $return['error'], ['field' => $return['name']]);
        }

        if ($data['mode']) {
            // 目录模式
            if (!$data['dirname']) {
                $this->_json(0, dr_lang('本站目录未填写'), ['field' => 'dirname']);
            } elseif (strpos($data['dirname'], '/') !== false) {
                $this->_json(0, dr_lang('本站目录填写格式有误，只能填写相当于本站根目录的文件名称'), ['field' => 'dirname']);
            }
            $data['domain'] = $this->site_info[SITE_ID]['SITE_DOMAIN'].'/'.$data['dirname'];
            $data['webpath'] = $data['dirname'];
        } else {
            if (!$data['webpath']) {
                $this->_json(0, dr_lang('本站Web目录未填写'), ['field' => 'webpath']);
            }
        }

        unset($data['mode']);
        unset($data['dirname']);

        $path = dr_get_dir_path($data['webpath']);
        if (!is_dir($path)) {
            $this->_json(0, dr_lang('目录[%s]不存在', $path));
        } elseif (is_file($path.'index.php')) {
            $this->_json(0, dr_lang('目录[%s]已经创建过站点，请更换目录', $path));
        }

        return $data;
    }


}
