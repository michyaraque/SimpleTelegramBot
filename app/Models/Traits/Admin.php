<?php

namespace App\Models\Traits;

trait Admin {
    
    public static function getUserAccount(int $rol_id, int $limit, int $offset) {
        try {
            $stm = self::$static_connection->prepare("SELECT id, username, real_name, user_id, rol_id, ban, ban FROM 
            usuarios WHERE rol_id >= :rol_id ORDER BY ban_reviewbot ASC, id ASC, ban ASC LIMIT :offset,:limit");
            $stm->bindValue(":limit", $limit);
            $stm->bindValue(":offset", $offset);
            $stm->bindValue(":rol_id", $rol_id);
            $stm->execute();
            return $stm->fetchAll(\PDO::FETCH_ASSOC);
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }
    
}