<?php
class MaintenanceFilter extends PhateInputFilterBase
{
    public function execute() {
        PhateHttpResponseHeader::setResponseHeader('Maintenance', false);
        $sysconfig = PhateCore::getConfigure();
        $filename = PHATE_MAINTENANCE_DIR . $sysconfig['MAINTENANCE']['load_yaml_file'];
        if (file_exists($filename)) {
            $parseYml = yaml_parse_file($filename);
            // 許可IPからのアクセスはメンテナンスすり抜け
            if (isset($parseYml['AllowIP'])) {
                if (in_array(PhateHttpRequest::getRemoteAddr(), $parseYml['AllowIP'])) {
                    return;
                }
            }
            
            $userId = PhateHttpRequest::getHeaderParam('User-Id');
            // 許可ユーザからのアクセスはメンテナンスすり抜け
            if (!is_null($userId) && isset($parseYml['AllowUserID'])) {
                // from と to
                if (isset($parseYml['AllowUserID']['from']) && isset($parseYml['AllowUserID']['to'])) {
                    if (($parseYml['AllowUserID']['from'] <= $userId) && ($parseYml['AllowUserID']['to'] >= $userId)) {
                        return;
                    }
                }
                if (isset($parseYml['AllowUserID']['individual'])) {
                    if (in_array($userId, $parseYml['AllowUserID']['individual'])) {
                        return;
                    }
                }
            }
            
            /*
             * メンテナンスページの挙動
             */
            PhateHttpResponseHeader::setHttpStatus(PhateHttpResponseHeader::HTTP_OK);
            PhateHttpResponseHeader::setResponseHeader('Maintenance', true);
            PhateHttpResponseHeader::sendResponseHeader();
            // 以下メンテナンスお知らせページのurlだけ返すイメージ
            $rtn = array('url' => $parseYml['Url']);
            $renderer = new PhateMsgPackRenderer;
            $renderer->render($rtn);
            throw new PhateKillException();
        }
    }
}
