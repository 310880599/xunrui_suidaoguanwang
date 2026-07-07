<?php namespace Phpcmf\Controllers\Admin;

class Menu extends \Phpcmf\App
{

	public function index() {

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
	


}
