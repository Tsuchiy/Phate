<?php
class ShardDBHandlerManager {
    public static function createShardId($userId) {
        return $userId % 2;
    }
}
