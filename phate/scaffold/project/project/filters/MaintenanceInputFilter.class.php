<?php
class MaintenanceInputFilter extends PhateInputFilterBase
{

    public function execute() {
        if (!file_exists(PROJECT_ROOT . 'maintenance/maintenance.yml')) {
            return;
        }
        $ymlSource = yaml_parse_file(PROJECT_ROOT . 'maintenance/maintenance.yml');
        
        // メンテナンス除外処理などを書く
        
        // メンテナンスページを表示orリダイレクトし、終了
        exit();
    }
}