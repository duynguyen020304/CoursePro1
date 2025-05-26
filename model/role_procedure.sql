CREATE OR REPLACE PACKAGE ROLE_PKG AS

    PROCEDURE CREATE_NEW_ROLE (
        p_roleID IN ROLE.RoleID%TYPE,
        p_roleName IN ROLE.RoleName%TYPE,
        p_success OUT BOOLEAN
    );

    PROCEDURE DELETE_ROLE (
        p_roleID IN ROLE.RoleID%TYPE,
        p_success OUT BOOLEAN
    );

    PROCEDURE UPDATE_ROLE (
        p_roleID IN ROLE.RoleID%TYPE,
        p_roleName IN ROLE.RoleName%TYPE,
        p_success OUT BOOLEAN
    );

    TYPE t_role_cursor IS REF CURSOR;

    PROCEDURE GET_ROLE_BY_ID (
        p_roleID IN ROLE.RoleID%TYPE,
        p_found_roleID OUT ROLE.RoleID%TYPE,
        p_roleName OUT ROLE.RoleName%TYPE,
        p_created_at OUT VARCHAR2
    );

    PROCEDURE GET_ALL_ROLES (
        p_cursor OUT t_role_cursor
    );

END ROLE_PKG;
/

CREATE OR REPLACE PACKAGE BODY ROLE_PKG AS

    PROCEDURE CREATE_NEW_ROLE (
        p_roleID IN ROLE.RoleID%TYPE,
        p_roleName IN ROLE.RoleName%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count FROM ROLE WHERE RoleID = p_roleID;

        IF v_count = 0 THEN
            INSERT INTO ROLE (RoleID, RoleName)
            VALUES (p_roleID, p_roleName);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: RoleID ' || p_roleID || ' already exists.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CREATE_NEW_ROLE: ' || SQLERRM);
    END CREATE_NEW_ROLE;

    PROCEDURE DELETE_ROLE (
        p_roleID IN ROLE.RoleID%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM ROLE
        WHERE RoleID = p_roleID;

        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No role deleted for RoleID ' || p_roleID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_ROLE: ' || SQLERRM);
    END DELETE_ROLE;

    PROCEDURE UPDATE_ROLE (
        p_roleID IN ROLE.RoleID%TYPE,
        p_roleName IN ROLE.RoleName%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_rows_updated NUMBER;
    BEGIN
        UPDATE ROLE
        SET
            RoleName = p_roleName
        WHERE RoleID = p_roleID;

        v_rows_updated := SQL%ROWCOUNT;
        IF v_rows_updated = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No role updated for RoleID ' || p_roleID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_ROLE: ' || SQLERRM);
    END UPDATE_ROLE;

    PROCEDURE GET_ROLE_BY_ID (
        p_roleID IN ROLE.RoleID%TYPE,
        p_found_roleID OUT ROLE.RoleID%TYPE,
        p_roleName OUT ROLE.RoleName%TYPE,
        p_created_at OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT RoleID, RoleName,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_roleID, p_roleName, p_created_at
        FROM ROLE
        WHERE RoleID = p_roleID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_roleID := NULL;
            p_roleName := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_roleID := NULL;
            p_roleName := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_ROLE_BY_ID: ' || SQLERRM);
    END GET_ROLE_BY_ID;

    PROCEDURE GET_ALL_ROLES (
        p_cursor OUT t_role_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT RoleID, RoleName,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM ROLE
            ORDER BY RoleID ASC;
    END GET_ALL_ROLES;

END ROLE_PKG;