-- SQL for Oracle Database 19c
-- Package for ROLE Business Logic Layer

CREATE OR REPLACE PACKAGE ROLE_PKG AS

    PROCEDURE CREATE_ROLE_PROC(
        p_RoleID   IN ROLE.RoleID%TYPE,
        p_RoleName IN ROLE.RoleName%TYPE
    );

    PROCEDURE DELETE_ROLE_PROC(
        p_RoleID IN ROLE.RoleID%TYPE
    );

    PROCEDURE UPDATE_ROLE_PROC(
        p_RoleID   IN ROLE.RoleID%TYPE,
        p_RoleName IN ROLE.RoleName%TYPE
    );

    FUNCTION GET_ROLE_BY_ID_FUNC(
        p_RoleID IN ROLE.RoleID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_ALL_ROLES_FUNC
        RETURN SYS_REFCURSOR;

END ROLE_PKG;
/

CREATE OR REPLACE PACKAGE BODY ROLE_PKG AS

    PROCEDURE CREATE_ROLE_PROC(
        p_RoleID   IN ROLE.RoleID%TYPE,
        p_RoleName IN ROLE.RoleName%TYPE
    ) IS
    BEGIN
        INSERT INTO ROLE (RoleID, RoleName)
        VALUES (p_RoleID, p_RoleName);
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            DECLARE
                v_check_pk NUMBER;
                v_check_uq NUMBER;
            BEGIN
                SELECT COUNT(*) INTO v_check_pk FROM ROLE WHERE RoleID = p_RoleID;
                IF v_check_pk > 0 THEN
                    RAISE_APPLICATION_ERROR(-20150, 'Role with RoleID ''' || p_RoleID || ''' already exists.');
                END IF;
                SELECT COUNT(*) INTO v_check_uq FROM ROLE WHERE RoleName = p_RoleName;
                IF v_check_uq > 0 THEN
                    RAISE_APPLICATION_ERROR(-20151, 'Role with RoleName ''' || p_RoleName || ''' already exists.');
                END IF;
                RAISE;
            END;
        WHEN OTHERS THEN
            RAISE;
    END CREATE_ROLE_PROC;

    PROCEDURE DELETE_ROLE_PROC(
        p_RoleID IN ROLE.RoleID%TYPE
    ) IS
    BEGIN
        DELETE FROM ROLE
        WHERE RoleID = p_RoleID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20152, 'Role with RoleID ''' || p_RoleID || ''' not found for deletion.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -2292 THEN
                RAISE_APPLICATION_ERROR(-20153, 'Cannot delete RoleID ''' || p_RoleID || ''' as it is currently in use by users.');
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in DELETE_ROLE_PROC: ' || SQLERRM);
            END IF;
    END DELETE_ROLE_PROC;

    PROCEDURE UPDATE_ROLE_PROC(
        p_RoleID   IN ROLE.RoleID%TYPE,
        p_RoleName IN ROLE.RoleName%TYPE
    ) IS
    BEGIN
        UPDATE ROLE
        SET RoleName = p_RoleName
        WHERE RoleID = p_RoleID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20154, 'Role with RoleID ''' || p_RoleID || ''' not found for update.');
        END IF;
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            RAISE_APPLICATION_ERROR(-20151, 'Update failed: RoleName ''' || p_RoleName || ''' already exists for another role.');
        WHEN OTHERS THEN
            IF SQLCODE = -20154 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UPDATE_ROLE_PROC: ' || SQLERRM);
            END IF;
    END UPDATE_ROLE_PROC;

    FUNCTION GET_ROLE_BY_ID_FUNC(
        p_RoleID IN ROLE.RoleID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                RoleID,
                RoleName,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                ROLE
            WHERE
                RoleID = p_RoleID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_ROLE_BY_ID_FUNC;

    FUNCTION GET_ALL_ROLES_FUNC
        RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                RoleID,
                RoleName,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                ROLE
            ORDER BY RoleID ASC;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_ALL_ROLES_FUNC;

END ROLE_PKG;
/

