CREATE OR REPLACE PACKAGE USER_PKG AS

    PROCEDURE CREATE_USER_PROC(
        p_UserID       IN USERS.UserID%TYPE,
        p_FirstName    IN USERS.FirstName%TYPE,
        p_LastName     IN USERS.LastName%TYPE,
        p_Email        IN USERS.Email%TYPE,
        p_Password     IN USERS.Password%TYPE,
        p_RoleID       IN USERS.RoleID%TYPE,
        p_ProfileImage IN USERS.ProfileImage%TYPE DEFAULT NULL
    );

    FUNCTION GET_USER_FOR_AUTH_FUNC(
        p_Email IN USERS.Email%TYPE
    ) RETURN SYS_REFCURSOR;

    PROCEDURE DELETE_USER_PROC(
        p_UserID IN USERS.UserID%TYPE
    );

    PROCEDURE UPDATE_USER_PROC(
        p_UserID        IN USERS.UserID%TYPE,
        p_FirstName     IN USERS.FirstName%TYPE    DEFAULT NULL,
        p_LastName      IN USERS.LastName%TYPE     DEFAULT NULL,
        p_Password      IN USERS.Password%TYPE     DEFAULT NULL,
        p_RoleID        IN USERS.RoleID%TYPE       DEFAULT NULL,
        p_ProfileImage  IN USERS.ProfileImage%TYPE DEFAULT NULL,
        p_SetProfileImageNull BOOLEAN DEFAULT FALSE
    );

    FUNCTION GET_USER_BY_ID_FUNC(
        p_UserID IN USERS.UserID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_USER_BY_EMAIL_FUNC(
        p_Email IN USERS.Email%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_ALL_USERS_FUNC
        RETURN SYS_REFCURSOR;

    FUNCTION EMAIL_EXISTS_FUNC(
        p_Email         IN USERS.Email%TYPE,
        p_ExcludeUserID IN USERS.UserID%TYPE DEFAULT NULL
    ) RETURN NUMBER;

END USER_PKG;
/

CREATE OR REPLACE PACKAGE BODY USER_PKG AS

    PROCEDURE CREATE_USER_PROC(
        p_UserID       IN USERS.UserID%TYPE,
        p_FirstName    IN USERS.FirstName%TYPE,
        p_LastName     IN USERS.LastName%TYPE,
        p_Email        IN USERS.Email%TYPE,
        p_Password     IN USERS.Password%TYPE,
        p_RoleID       IN USERS.RoleID%TYPE,
        p_ProfileImage IN USERS.ProfileImage%TYPE DEFAULT NULL
    ) IS
    BEGIN
        INSERT INTO USERS (UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage)
        VALUES (p_UserID, p_FirstName, p_LastName, p_Email, p_Password, p_RoleID, p_ProfileImage);
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            DECLARE
                v_check_pk NUMBER;
                v_check_email NUMBER;
            BEGIN
                SELECT COUNT(*) INTO v_check_pk FROM USERS WHERE UserID = p_UserID;
                IF v_check_pk > 0 THEN
                    RAISE_APPLICATION_ERROR(-20170, 'User with UserID ''' || p_UserID || ''' already exists.');
                END IF;
                SELECT COUNT(*) INTO v_check_email FROM USERS WHERE Email = p_Email;
                IF v_check_email > 0 THEN
                    RAISE_APPLICATION_ERROR(-20171, 'User with Email ''' || p_Email || ''' already exists.');
                END IF;
                RAISE;
            END;
        WHEN OTHERS THEN
            RAISE;
    END CREATE_USER_PROC;

    FUNCTION GET_USER_FOR_AUTH_FUNC(
        p_Email IN USERS.Email%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                UserID,
                FirstName,
                LastName,
                Email,
                Password,
                RoleID,
                ProfileImage,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                USERS
            WHERE
                Email = p_Email;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_USER_FOR_AUTH_FUNC;

    PROCEDURE DELETE_USER_PROC(
        p_UserID IN USERS.UserID%TYPE
    ) IS
    BEGIN
        DELETE FROM USERS
        WHERE UserID = p_UserID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20172, 'User with UserID ''' || p_UserID || ''' not found for deletion.');
        END IF;
        -- ON DELETE CASCADE/SET NULL for related tables should be defined on those tables' FK constraints.
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20172 THEN
                RAISE;
            ELSIF SQLCODE = -2292 THEN
                RAISE_APPLICATION_ERROR(-20173, 'Cannot delete UserID ''' || p_UserID || ''' as it is referenced by other records.');
            ELSE
                RAISE;
            END IF;
    END DELETE_USER_PROC;

    PROCEDURE UPDATE_USER_PROC(
        p_UserID        IN USERS.UserID%TYPE,
        p_FirstName     IN USERS.FirstName%TYPE    DEFAULT NULL,
        p_LastName      IN USERS.LastName%TYPE     DEFAULT NULL,
        p_Password      IN USERS.Password%TYPE     DEFAULT NULL,
        p_RoleID        IN USERS.RoleID%TYPE       DEFAULT NULL,
        p_ProfileImage  IN USERS.ProfileImage%TYPE DEFAULT NULL,
        p_SetProfileImageNull BOOLEAN DEFAULT FALSE
    ) IS
        v_user_exists NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_user_exists FROM USERS WHERE UserID = p_UserID;
        IF v_user_exists = 0 THEN
            RAISE_APPLICATION_ERROR(-20174, 'User with UserID ''' || p_UserID || ''' not found for update.');
        END IF;

        UPDATE USERS
        SET FirstName    = NVL(p_FirstName, FirstName),
            LastName     = NVL(p_LastName, LastName),
            Password     = NVL(p_Password, Password),
            RoleID       = NVL(p_RoleID, RoleID),
            ProfileImage = CASE
                               WHEN p_SetProfileImageNull THEN NULL
                               ELSE NVL(p_ProfileImage, ProfileImage)
                END
        WHERE UserID = p_UserID;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20174 THEN
                RAISE;
            ELSE
                RAISE;
            END IF;
    END UPDATE_USER_PROC;

    FUNCTION GET_USER_BY_ID_FUNC(
        p_UserID IN USERS.UserID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                UserID,
                FirstName,
                LastName,
                Email,
                Password,
                RoleID,
                ProfileImage,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                USERS
            WHERE
                UserID = p_UserID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_USER_BY_ID_FUNC;

    FUNCTION GET_USER_BY_EMAIL_FUNC(
        p_Email IN USERS.Email%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                UserID,
                FirstName,
                LastName,
                Email,
                Password,
                RoleID,
                ProfileImage,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                USERS
            WHERE
                Email = p_Email;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_USER_BY_EMAIL_FUNC;

    FUNCTION GET_ALL_USERS_FUNC
        RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                UserID,
                FirstName,
                LastName,
                Email,
                RoleID,
                ProfileImage,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                USERS
            ORDER BY UserID ASC;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_ALL_USERS_FUNC;

    FUNCTION EMAIL_EXISTS_FUNC(
        p_Email         IN USERS.Email%TYPE,
        p_ExcludeUserID IN USERS.UserID%TYPE DEFAULT NULL
    ) RETURN NUMBER IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_count
        FROM USERS
        WHERE Email = p_Email
          AND (p_ExcludeUserID IS NULL OR UserID != p_ExcludeUserID);

        IF v_count > 0 THEN
            RETURN 1;
        ELSE
            RETURN 0;
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END EMAIL_EXISTS_FUNC;

END USER_PKG;
/

SHOW ERRORS PACKAGE USER_PKG;
SHOW ERRORS PACKAGE BODY USER_PKG;