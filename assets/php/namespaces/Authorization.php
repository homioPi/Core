<?php 
    namespace HomioPi\Authorization;

    function authorize() {
        $db = \HomioPi\Database\connect();

        if(!isset($_COOKIE['HomioPi_token_id']) || !isset($_COOKIE['HomioPi_token'])) {
            \HomioPi\response\error('error_authorization');
            return false;
        }

        // Find token
        $db->where('id', $_COOKIE['HomioPi_token_id']);
        $row = $db->getOne('tokens');

        // Throw an error and delete token if one can not be found
        if($row === null) {
            \HomioPi\Authorization\delete_token($_COOKIE['HomioPi_token_id']);
            
            \HomioPi\Response\error('error_authorization');
            return false;
        }

        // Create new token when expired
        if($row['expires'] < time()) {
            // Delete old token from database
            $db->where('id', $_COOKIE['HomioPi_token_id']);
            $db->delete('tokens');

            \HomioPi\Authorization\create_token($row['uuid']);
        }

        $_SESSION['HomioPi_uuid'] = $row['uuid'];
        
        return true;
    }

    function login_verify($username, $password) {
        $db = \HomioPi\Database\connect();

        $users = $db->get('users');

        // Loop users to check if username and password match
        foreach ($users as $user) {
            if(trim($username) != trim($user['username'])) {
                continue;
            }

            if($user['uuid'] == 'default') {
                continue;
            }

            if(!password_verify(trim($password), $user['password_hash'])) {
                continue;
            }

            // Create token if username and password entered are correct
            \HomioPi\Authorization\create_token($user['uuid']);

            return true;
        }

        return false;
    }

    function create_token($uuid) {
        $db = \HomioPi\Database\connect();

        $token_id   = unique_id_secure(32);
        $token      = unique_id_secure(64);
        $expires    = time() + 900; // Token expires after 15 minutes

        if(!$db->insert('tokens', [
            'id'         => $token_id,
            'uuid'       => $uuid,
            'expires'    => $expires
        ])) {
            \HomioPi\Response\error('error_token_creation_failed');
        }

        setcookie('HomioPi_token', $token, time() + 365 * 24* 60 * 60, '/');
        setcookie('HomioPi_token_id', $token_id, time() + 365 * 24* 60 * 60, '/');

        return true;
    }

    function delete_token($token_id) {
        $db = \HomioPi\Database\connect();

        $db->where('id', $token_id);
        $db->delete('tokens');

        setcookie('HomioPi_token', null, -1, '/');
        setcookie('HomioPi_token_id', null, -1, '/');
        unset($_SESSION['HomioPi_uuid']);

        return true;
    }
?>