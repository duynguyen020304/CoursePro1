<?php
require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../dto/user_dto.php';

class UserBLL extends Database
{
    public function create_user(UserDTO $user): bool
    {
        $hashedPassword = password_hash($user->password, PASSWORD_DEFAULT);
        $sql = "BEGIN USER_PKG.CREATE_USER_PROC(:userID, :firstName, :lastName, :email, :password, :roleID, :profileImage); END;";

        $bindParams = [
            ':userID'       => $user->userID,
            ':firstName'    => $user->firstName,
            ':lastName'     => $user->lastName,
            ':email'        => $user->email,
            ':password'     => $hashedPassword,
            ':roleID'       => $user->roleID,
            ':profileImage' => $user->profileImage,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function authenticate(string $email, string $password): ?UserDTO
    {
        $sql = "BEGIN :result_cursor := USER_PKG.GET_USER_FOR_AUTH_FUNC(:email_param); END;";
        $bindParams = [
            ':email_param' => $email
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[UserBLL] Failed to create new cursor for GET_USER_FOR_AUTH_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[UserBLL] OCI Parse failed for GET_USER_FOR_AUTH_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':email_param', $bindParams[':email_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[UserBLL] OCI Execute failed for GET_USER_FOR_AUTH_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[UserBLL] OCI Execute failed for result cursor of GET_USER_FOR_AUTH_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                if (isset($row['PASSWORD']) && password_verify($password, $row['PASSWORD'])) {
                    $dto = new UserDTO(
                        $row['USERID'],
                        $row['FIRSTNAME'],
                        $row['LASTNAME'],
                        $row['EMAIL'],
                        "",
                        $row['ROLEID'],
                        $row['PROFILEIMAGE'],
                        $row['CREATED_AT_FORMATTED'] ?? null
                    );
                }
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function delete_user(string $userID): bool
    {
        $sql = "BEGIN USER_PKG.DELETE_USER_PROC(:userID); END;";
        $bindParams = [':userID' => $userID];
        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function update_user(UserDTO $user): bool
    {
        $sql = "BEGIN USER_PKG.UPDATE_USER_PROC(:userID, :firstName, :lastName, :password, :roleID, :profileImage); END;";
        $bindParams = [
            ':userID'        => $user->userID,
            ':firstName'     => $user->firstName,
            ':lastName'      => $user->lastName,
            ':password'      => $user->password,
            ':roleID'        => $user->roleID,
            ':profileImage'  => $user->profileImage,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        return ($stid !== false);
    }

    public function get_user_by_user_id(string $userID, string $purpose = "get"): ?UserDTO
    {
        $sql = "BEGIN :result_cursor := USER_PKG.GET_USER_BY_ID_FUNC(:userID_param); END;";
        $bindParams = [
            ':userID_param' => $userID
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[UserBLL] Failed to create new cursor for GET_USER_BY_ID_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[UserBLL] OCI Parse failed for GET_USER_BY_ID_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':userID_param', $bindParams[':userID_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[UserBLL] OCI Execute failed for GET_USER_BY_ID_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[UserBLL] OCI Execute failed for result cursor of GET_USER_BY_ID_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $passwordForDTO = "";
                if ($purpose === "update" || $purpose === "internal_check") {
                    $passwordForDTO = $row['PASSWORD'];
                }
                $dto = new UserDTO(
                    $row['USERID'],
                    $row['FIRSTNAME'],
                    $row['LASTNAME'],
                    $row['EMAIL'],
                    $passwordForDTO,
                    $row['ROLEID'],
                    $row['PROFILEIMAGE'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function get_user_by_email(string $email): ?UserDTO
    {
        $sql = "BEGIN :result_cursor := USER_PKG.GET_USER_BY_EMAIL_FUNC(:email_param); END;";
        $bindParams = [
            ':email_param' => $email
        ];

        $dto = null;
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[UserBLL] Failed to create new cursor for GET_USER_BY_EMAIL_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return null;
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[UserBLL] OCI Parse failed for GET_USER_BY_EMAIL_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return null;
        }

        @oci_bind_by_name($parsed_stid, ':email_param', $bindParams[':email_param']);
        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[UserBLL] OCI Execute failed for GET_USER_BY_EMAIL_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[UserBLL] OCI Execute failed for result cursor of GET_USER_BY_EMAIL_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return null;
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            if (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $dto = new UserDTO(
                    $row['USERID'],
                    $row['FIRSTNAME'],
                    $row['LASTNAME'],
                    $row['EMAIL'],
                    "",
                    $row['ROLEID'],
                    $row['PROFILEIMAGE'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $dto;
    }

    public function get_all_users(): array
    {
        $sql = "BEGIN :result_cursor := USER_PKG.GET_ALL_USERS_FUNC(); END;";
        $users = [];
        $out_cursor = @oci_new_cursor($this->conn);
        if (!$out_cursor) {
            error_log('[UserBLL] Failed to create new cursor for GET_ALL_USERS_FUNC: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            return [];
        }

        $parsed_stid = @oci_parse($this->conn, $sql);
        if (!$parsed_stid) {
            error_log('[UserBLL] OCI Parse failed for GET_ALL_USERS_FUNC. SQL: ' . $sql . ' Error: ' . ($this->conn ? oci_error($this->conn)['message'] : 'No connection'));
            @oci_free_cursor($out_cursor);
            return [];
        }

        @oci_bind_by_name($parsed_stid, ':result_cursor', $out_cursor, -1, OCI_B_CURSOR);

        $execute_mode = ($this->inTransaction) ? OCI_NO_AUTO_COMMIT : OCI_DEFAULT;
        if (!@oci_execute($parsed_stid, $execute_mode)) {
            error_log('[UserBLL] OCI Execute failed for GET_ALL_USERS_FUNC block. Error: ' . ($parsed_stid ? oci_error($parsed_stid)['message'] : 'No statement handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }

        if (!@oci_execute($out_cursor, $execute_mode)) {
            error_log('[UserBLL] OCI Execute failed for result cursor of GET_ALL_USERS_FUNC. Error: ' . ($out_cursor ? oci_error($out_cursor)['message'] : 'No cursor handle'));
            @oci_free_statement($parsed_stid);
            @oci_free_cursor($out_cursor);
            return [];
        }
        $stid_cursor = $out_cursor;

        if ($stid_cursor) {
            while (($row = @oci_fetch_array($stid_cursor, OCI_ASSOC + OCI_RETURN_NULLS))) {
                $users[] = new UserDTO(
                    $row['USERID'],
                    $row['FIRSTNAME'],
                    $row['LASTNAME'],
                    $row['EMAIL'],
                    "",
                    $row['ROLEID'],
                    $row['PROFILEIMAGE'],
                    $row['CREATED_AT_FORMATTED'] ?? null
                );
            }
            @oci_free_statement($stid_cursor);
        }
        @oci_free_statement($parsed_stid);

        return $users;
    }

    public function email_exists(string $email, ?string $excludeUserID = null): bool
    {
        $sql = "SELECT USER_PKG.EMAIL_EXISTS_FUNC(:email, :excludeUserID) AS EMAIL_EXISTS FROM DUAL";

        $bindParams = [
            ':email'         => $email,
            ':excludeUserID' => $excludeUserID,
        ];

        $stid = $this->executePrepared($sql, $bindParams);
        if ($stid) {
            $row = @oci_fetch_array($stid, OCI_ASSOC + OCI_RETURN_NULLS);
            @oci_free_statement($stid);
            if ($row && isset($row['EMAIL_EXISTS'])) {
                return (int)$row['EMAIL_EXISTS'] === 1;
            }
        }
        error_log('[UserBLL] Failed to check email existence for Email: ' . $email);
        return false;
    }
}
?>