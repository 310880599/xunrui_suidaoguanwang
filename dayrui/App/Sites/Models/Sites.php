<?php namespace Phpcmf\Model\Sites;


class Sites extends \Phpcmf\Model
{

    // 删除站点
    public function delete_site($ids) {

        if (!$ids) {
            return dr_return_data(0, dr_lang('参数不存在'));
        }


        $database = \Phpcmf\Service::M()->db->query('show table status')->getResultArray();

        // 删除表
        foreach ($ids as $siteid) {
            if ($siteid > 1) {
                $this->db->table('site')->where('id', $siteid)->delete();
                // 删除表
                $table = $this->dbprefix($siteid.'_');
                foreach ($database as $t) {
                    if (strpos($t['Name'], $table) === 0) {
                        $this->db->query('DROP TABLE IF EXISTS `'.$t['Name'].'`;');
                        log_message('error', '删除站点【'.$siteid.'】时联动删除表：'.$t['Name']);
                    }
                }
            }
        }

        return dr_return_data(1, 'ok');
    }

}