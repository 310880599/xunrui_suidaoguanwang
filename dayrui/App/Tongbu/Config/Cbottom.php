<?php

return [

    [
        'icon' => 'fa fa-th-large',
        'name' => dr_lang('批量同步到其他站点'),
        'uri' => 'tongbu/home/edit',
        'url' => 'javascript:;" onclick="dr_module_send(\'同步设置\',\''.dr_url('tongbu/home/edit', ['mid' => '{mid}']).'\')',
    ],


];