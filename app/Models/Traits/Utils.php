<?php

namespace App\Models\Traits;

trait Utils {

    public static $rol_name;

    private static function convertRolNameToNumber(string $rol_name) {
        switch(strtolower($rol_name)) {
            case 'none':
                $val = (int) 0;
                break;
            case 'editor':
                $val = 1;
                break;
            case 'moderator':
                $val = 1;
                break;
            case 'admin':
                $val = 999;
                break;
        }
        return $val;
    }

    public static function registerUser(int $user_id, ?string $username, string $real_name, string $language, string $rol) {
        try {
            $stm = self::$static_connection->prepare("INSERT IGNORE INTO 
            users (user_id, username, real_name, register_date, language, rol_id) 
            VALUES (:user_id, :username, :real_name, :time, :lang, :rol_id);");
            $stm->bindValue(":user_id", $user_id);
            $stm->bindValue(":username", $username);
            $stm->bindValue(":real_name", $real_name);
            $stm->bindValue(":time", time());
            $stm->bindValue(":lang", $language);
            $stm->bindValue(":rol_id", self::convertRolNameToNumber($rol));
            $stm->execute();
            return self::$static_connection->lastInsertId();
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function getAllSteps(int $user_id) {

        try {
            $stm = self::$static_connection->prepare("SELECT temp_data, step FROM conversation WHERE user_id = ?;");
            $stm->execute([$user_id]);
            return $stm->fetch(\PDO::FETCH_ASSOC);
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function getLanguage(int $user_id) {

        try {
            $stm = self::$static_connection->prepare("SELECT language FROM users WHERE user_id = ?;");
            $stm->execute([$user_id]);
            return $stm->fetch(\PDO::FETCH_ASSOC);
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function updateLanguage(int $user_id, string $language) {
        try {
            $stm = self::$static_connection->prepare("UPDATE users SET language = ? WHERE user_id = ?;");
            $stm->execute([$language, $user_id]);
            return $stm->rowCount();
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function isRegister(int $user_id): bool {
        try {
            $stm = self::$static_connection->prepare("SELECT user_id FROM users WHERE user_id = ? LIMIT 1;");
            $stm->execute([$user_id]);

            if($stm->rowCount() > 0){
                return true;
            }

            return false;

        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function updateRolId(int $user_id, string $rol) {
        try {
            $stm = self::$static_connection->prepare("UPDATE users SET rol_id = ? WHERE user_id = ?;");
            $stm->execute([self::convertRolNameToNumber($rol), $user_id]);
            return $stm->rowCount();
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function getUserRolData(int $user_id) {
        try {
            $stm = self::$static_connection->prepare("SELECT roles.id, roles.name FROM 
            users INNER JOIN roles ON users.rol_id = roles.id WHERE users.user_id = ?;");
            $stm->execute([$user_id]);
            return $stm->fetch(\PDO::FETCH_ASSOC);
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function getUserRolId(int $user_id) {
        try {
            $result = self::getUserRolData($user_id);
            return $result['id'];
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function getUserRolName(int $user_id) {
        try {
            $result = self::getUserRolData($user_id);
            if(!empty($result['name'])) {
                return $result['name'];
            } else {
                return false;
            }
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function getUserTerms(int $user_id) {
        try {
            $stm = self::$static_connection->prepare("SELECT term_conditions FROM users WHERE user_id = ?");
            $stm->execute([ $user_id]);
            return $stm->fetchColumn();
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function setUserTerms(int $user_id) {
        try {
            $stm = self::$static_connection->prepare("UPDATE users SET term_conditions = 'accepted' WHERE user_id = ?");
            $stm->execute([$user_id]);
            return $stm->rowCount();
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function setStep( int $user_id, string $step) {

        try {
            $stm = self::$static_connection->prepare("UPDATE conversation SET step = ? WHERE user_id = ?;");
            $stm->execute([$step, $user_id]);
            return $stm->rowCount();
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function getStep(int $user_id) {

        if(empty(self::$static_connection)) {
            self::create();
        }

        try {
            $stm = self::$static_connection->prepare("SELECT step FROM conversation WHERE user_id = ?");
            $stm->execute([$user_id]);
            return $stm->fetchColumn();
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function setTempData(int $user_id, $content) {
        try {
            $stm = self::$static_connection->prepare("UPDATE conversation SET temp_data = ? WHERE user_id = ?");
            if (is_array($content)) { 
                $content = json_encode($content); 
            }
            $stm->execute([$content, $user_id]);
            return $stm->rowCount();
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function getTempData(int $user_id, bool $encode = true) {
        try {
            $stm = self::$static_connection->prepare("SELECT temp_data FROM conversation WHERE user_id = ?");
            $stm->execute([$user_id]);
            $data = $stm->fetchColumn();
            if ($encode) {
                return json_decode($data, true);
            } else {
                return $data;
            }
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }
          
    public static function setRecursiveTempData(int $user_id, array $data, ?bool $recursive = true, string $bot_selector) {
        $temp_data = self::getTempData($user_id, true, $bot_selector);
        if($recursive) {
            $merge_data = array_merge($temp_data, $data);
        } else {
            $merge_data = array_merge_recursive($temp_data, $data);
        }
        self::setTempData($user_id, $merge_data, $bot_selector);
        return $temp_data;
    }

    public static function resetTempAndStep(int $user_id) {

        if(empty(self::$static_connection)) {
            self::create();
        }
        
        try {
            $query = "UPDATE conversation SET temp_data= '', step = '' WHERE user_id = ?;";
            $stm = self::$static_connection->prepare($query);
            $stm->execute([$user_id]);
            return $stm->fetch(\PDO::FETCH_ASSOC);
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function cleanStep(int $user_id) {

        try {
            $query = "UPDATE conversation SET step = '' WHERE user_id = ?;";
            $stm = self::$static_connection->prepare($query);
            $stm->execute([$user_id]);
            return $stm->fetch(\PDO::FETCH_ASSOC);
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function banStatus(int $user_id) {
        try {
            $stm = self::$static_connection->prepare("SELECT ban FROM users WHERE user_id = ?");
            $stm->execute([$user_id]);
            return $stm->fetchColumn();
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function banUser(int $user_id) {
        try {

            $stm = self::$static_connection->prepare("UPDATE users SET ban = '1' WHERE user_id = ?");
            $stm->execute([$user_id]);
            return $stm->rowCount();
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function unbanUser(int $user_id) {
        try {
            $stm = self::$static_connection->prepare("UPDATE users SET ban = '0' WHERE user_id = ?");
            $stm->execute([$user_id]);
            return $stm->rowCount();
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function query(string $query, array $data) {

        if(empty(self::$static_connection)) {
            self::create();
        }

        try {
            $stm = self::$static_connection->prepare($query);
            $stm->execute($data);
            return $stm->fetch(\PDO::FETCH_ASSOC);
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function queryFetchAll(string $query, array $data) {

        if(empty(self::$static_connection)) {
            self::create();
        }

        try {
            $stm = self::$static_connection->prepare($query);
            $stm->execute($data);
            return $stm->fetchAll(\PDO::FETCH_ASSOC);
        } catch(\PDOException $e) {
            error_log($e->getMessage());
        }
    }

    public static function _getRolName(int $user_id, string $rol_selected): bool {

        if(!empty(self::$rol_name)) {
            if(self::$rol_name == $rol_selected) {
                return true;
            }
        } else {
            $rol_name = self::getUserRolName($user_id);
            self::$rol_name = $rol_name;
            if($rol_name == $rol_selected) {
                return true;
            }
        }
        return false;
    }

    public static function isNone(int $user_id): bool {
        if(!empty(self::_getRolName($user_id, 'None'))) {
            return true;
        }
        return false;
    }

    public static function isEditor(int $user_id): bool {
        if(!empty(self::_getRolName($user_id, 'Editor'))) {
            return true;
        }
        return false;
    }

    public static function isModerator(int $user_id): bool {

        if(!empty(self::_getRolName($user_id, 'Moderator'))) {
            return true;
        }
        return false;
    }
    
    public static function isAdministrator(int $user_id): bool {
        if(!empty(self::_getRolName($user_id, 'Admin'))) {
            return true;
        }
        return false;
    }

}