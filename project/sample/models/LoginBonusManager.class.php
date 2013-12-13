<?php
class LoginBonusManager extends PhateModelBase
{
    public static function check()
    {
        if (PhateTimer::getApplicationDate(PhateTimer::getTimeStamp(LoginUserInfoManager::getUserAuthData()->getLastLoginDate())) < PhateTimer::getApplicationDate()) {
            return false;
        }
        return true;
    }
    
    public static function add()
    {
        if (!self::check()) {
            return array();
        }
        
        // ポイントによるボーナス
        
        
        // キャンペーンによるボーナス
        
        
    }
    
    private static function getNowPointBonus()
    {
        // ログインボーナスが設定されていない
        if (!($term = SampleLbonusTermPeer::getNowLbonusTerm())) {
            return array();
        }
        $userId = LoginUserInfoManager::getUserAuthData()->getUserId();
        if (!($lbonusUser = SampleLbonusPointUserPeer::retrieveByPk($userId, $term->getTermId()))) {
            $lbonusUser = new SampleLbonusPointUserOrm;
            $lbonusUser->setUserId($userId);
            $lbonusUser->setTermId($term->getTermId());
            $lbonusUser->setPoint(0);
            $lbonusUser->setCycle(0);
        }
        $beforePoint = $lbonusUser->getPoint();
        $beforeCycle = $lbonusUser->getCycle();

        $tmp = SampleLbonusPointPeer::getNowLbonusPoint();
        $point = $tmp ? $tmp->getPoint() : $term->getDefaultPoint();
        
        $lbonusUser->setPoint($lbonusUser->getPoint() + $point);
        if ($lbonusUser->getPoint() > $term->getMaxPoint()) {
            if ($lbonusUser->getCycle() >= $term->getMaxCycle()) {
                $lbonusUser->setPoint($term->getMaxPoint());
            } else {
                $lbonusUser->setPoint($lbonusUser->getPoint() - $term->getMaxPoint());
                $lbonusUser->setCycle($lbonusUser->getCycle() + 1);
            }
        }
        $afterPoint = $lbonusUser->getPoint();
        $afterCycle = $lbonusUser->getCycle();
        
        $lbonusReward = SampleLbonusPointRewardPeer::retrieveByTermId($term->getTermId());
        $reward = array();
        if ($beforeCycle == $afterCycle) {
            for ($i=$beforePoint+1;$i<=$afterPoint;$i++) {
                if (isset($lbonusReward[$i])) {
                    $tmp = SampleRewardPeer::retrieveByRewardId($lbonusReward->getRewardId());
                    $reward = array_merge($reward, $tmp);
                }
            }
        } else {
            for ($i=$beforePoint+1;$i<=$term->getMaxPoint();$i++) {
                if (isset($lbonusReward[$i])) {
                    $tmp = SampleRewardPeer::retrieveByRewardId($lbonusReward->getRewardId());
                    $reward = array_merge($reward, $tmp);
                }
            }
            for ($i=1;$i<=$afterPoint;$i++) {
                if (isset($lbonusReward[$i])) {
                    $tmp = SampleRewardPeer::retrieveByRewardId($lbonusReward->getRewardId());
                    $reward = array_merge($reward, $tmp);
                }
            }
        }
        return array(
            'beforePoint' => $beforePoint,
            'beforeCycle' => $beforeCycle,
            'afterPoint' => $afterPoint,
            'afterCycle' => $afterCycle,
            'reward' => $reward,
        );
    }
    
    private static function getNowCampaignBonus()
    {
        // キャンペーン中じゃない
        if (!($campaign = SampleLbonusCampaignRewardPeer::getNowLbonusCampaign())) {
            return array();
        }
        // 一回こっきりで既に貰ってる
        if (LoginUserInfoManager::getUserAuthData()->getLastLoginDate() >= $campaign->getFromDate() &&
                $campaign->getOneTime()) {
            return array();
        }
        
        $rtn = SampleRewardPeer::retrieveByRewardId($campaign->getRewardId());
        return $rtn ? $rtn : array();
    }
}
