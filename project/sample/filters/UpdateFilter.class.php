<?php
class UpdateFilter extends PhateInputFilterBase
{
    public function execute() {
        if (!($platformId = PhateHttpRequest::getHeaderParam('Platform-Id'))) {
            throw new PhateUnauthorizedException('client platform error');
        }
        if (!($cliantVersion = PhateHttpRequest::getHeaderParam('Client-Version'))) {
            throw new PhateUnauthorizedException('client version error');
        }
        /*
         * もしバージョンが違ったらの構造を追記して、
         * レスポンス生成・投げてkill例外返す
         */
        $lowestVersion = SampleApplicationInfoPeer::retrieveByPk($platformId)->getLowestVersion();
        if ($lowestVersion > $cliantVersion) {
            PhateHttpResponseHeader::setResponseHeader('Lowest-Version', $lowestVersion);
            PhateHttpResponseHeader::sendResponseHeader();
            throw new PhateKillException();
        }
    }
}
