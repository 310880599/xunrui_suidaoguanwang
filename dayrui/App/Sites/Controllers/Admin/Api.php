<?php namespace Phpcmf\Controllers\Admin;

class Api extends \Phpcmf\App
{

    public function login() {

        $uid = \Phpcmf\Service::L('cache')->get_auth_data('site_auth_login');
        if (!$uid) {
            $this->_admin_msg(0, '未收到可用账号授权');
        }
        $member = \Phpcmf\Service::M()->table('member')->get($uid);
        if (!$member) {
            $this->_admin_msg(0, '授权账号不存在');
        }
        \Phpcmf\Service::M('auth')->login_session($member);
        \Phpcmf\Service::L('cache')->set_auth_data('site_auth_login', 0);
        dr_redirect(SELF);
    }

    public function login_select() {

        $id = (int)\Phpcmf\Service::L('input')->get('id');
        $row = \Phpcmf\Service::M()->table('site')->get($id);
        if (!$row) {
            $this->_json(0, dr_lang('站点数据不存在'));
        }

        \Phpcmf\Service::L('cache')->set_auth_data('site_auth_login', $this->uid, $id);

        $self = '';
        $path = \Phpcmf\Service::L('html')->get_webpath($id, 'site');
        $files = dr_file_map($path);
        foreach ($files as $file) {
            if (strpos($file, '.php') !== false && $file != 'index.php') {
                $code = file_get_contents($path.$file);
                if (strpos($code, "define('IS_ADMIN', TRUE)") !== false) {
                    $self = $file;
                }
            }
        }

        if (!$self) {
            $this->_admin_msg(0, dr_lang('没有在网站【%s】找到后台入口文件', $this->site_info[$id]['SITE_NAME']));
        }

        $this->_admin_msg(1, dr_lang('正在切换到【%s】...', $this->site_info[$id]['SITE_NAME']), $this->site_info[$id]['SITE_URL'].$self.'?s=sites&c=api&m=login', 0);
    }


    // 验证目录式域名
    public function check_domain_dir($value) {

        if (!$value) {
            return false;
        }

        foreach (['?', '&', '\\', '*', ' ', '..'] as $p) {
            if (strpos($value, $p) !== false) {
                return false;
            }
        }

        if (substr_count($value, '/') > 1) {
            return false;
        }

        return true;
    }

    // 检测域名
    public function test() {

        $id = (int)\Phpcmf\Service::L('input')->get('id');
        $row = \Phpcmf\Service::M()->table('site')->get($id);
        if (!$row) {
            exit(dr_lang('站点数据不存在'));
        }

        if (!$this->check_domain_dir($row['domain'])) {
            exit(dr_lang('域名（%s）格式不正确', $row['domain']));
        } elseif (!function_exists('stream_context_create')) {
            exit( '函数没有被启用：stream_context_create');
        }

        $url = dr_http_prefix($row['domain']) . '/api.php';

        $code = dr_catcher_data($url, 5);
        if ($code != 'phpcmf ok') {
            exit('['.$row['domain'].']域名绑定异常<br>无法访问：' . $url . '<br>可以尝试手动访问此地址，如果提示phpcmf ok就表示成功');
        }

        exit('绑定正常');
    }

    /**
     * 测试目录是否可用
     */
    public function test_dir() {

        $v = \Phpcmf\Service::L('input')->get('v');
        if (!$v) {
            $this->_json(0, dr_lang('目录为空'));
        } elseif (strpos($v, ' ') === 0) {
            $this->_json(0, dr_lang('不能用空格开头'));
        }

        $path = dr_get_dir_path($v);
        if (is_dir($path)) {
            $this->_json(1, dr_lang('目录正常'));
        } else {
            $this->_json(0, dr_lang('目录[%s]不存在，建议手动删除主站目录和子站目录中的user.ini文件试一试', $path));
        }
    }
}
