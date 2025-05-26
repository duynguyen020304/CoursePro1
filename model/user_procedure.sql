CREATE OR REPLACE PACKAGE USER_PKG AS

    PROCEDURE CREATE_NEW_USER (
        p_userID IN USERS.UserID%TYPE,
        p_firstName IN USERS.FirstName%TYPE,
        p_lastName IN USERS.LastName%TYPE,
        p_email IN USERS.Email%TYPE,
        p_password IN USERS.Password%TYPE,
        p_roleID IN USERS.RoleID%TYPE,
        p_profileImage IN USERS.ProfileImage%TYPE,
        p_success OUT BOOLEAN
    );

    PROCEDURE DELETE_USER (
        p_userID IN USERS.UserID%TYPE,
        p_success OUT BOOLEAN
    );

    PROCEDURE UPDATE_USER (
        p_userID IN USERS.UserID%TYPE,
        p_firstName IN USERS.FirstName%TYPE DEFAULT NULL,
        p_lastName IN USERS.LastName%TYPE DEFAULT NULL,
        p_password IN USERS.Password%TYPE DEFAULT NULL,
        p_roleID IN USERS.RoleID%TYPE DEFAULT NULL,
        p_profileImage IN USERS.ProfileImage%TYPE,
        p_success OUT BOOLEAN
    );

    TYPE t_user_cursor IS REF CURSOR;

    PROCEDURE GET_USER_BY_ID (
        p_userID IN USERS.UserID%TYPE,
        p_found_userID OUT USERS.UserID%TYPE,
        p_firstName OUT USERS.FirstName%TYPE,
        p_lastName OUT USERS.LastName%TYPE,
        p_found_email OUT USERS.Email%TYPE,
        p_password OUT USERS.Password%TYPE,
        p_roleID OUT USERS.RoleID%TYPE,
        p_profileImage OUT USERS.ProfileImage%TYPE,
        p_created_at OUT VARCHAR2,
        p_purpose IN VARCHAR2 DEFAULT 'get'
    );

    PROCEDURE GET_USER_BY_EMAIL (
        p_email IN USERS.Email%TYPE,
        p_found_userID OUT USERS.UserID%TYPE,
        p_firstName OUT USERS.FirstName%TYPE,
        p_lastName OUT USERS.LastName%TYPE,
        p_found_email OUT USERS.Email%TYPE,
        p_roleID OUT USERS.RoleID%TYPE,
        p_profileImage OUT USERS.ProfileImage%TYPE,
        p_created_at OUT VARCHAR2
    );

    PROCEDURE GET_ALL_USERS (
        p_cursor OUT t_user_cursor
    );

    PROCEDURE EMAIL_EXISTS (
        p_email IN USERS.Email%TYPE,
        p_excludeUserID IN USERS.UserID%TYPE DEFAULT NULL,
        p_exists OUT BOOLEAN
    );

END USER_PKG;
/

CREATE OR REPLACE PACKAGE BODY USER_PKG AS

    PROCEDURE CREATE_NEW_USER (
        p_userID IN USERS.UserID%TYPE,
        p_firstName IN USERS.FirstName%TYPE,
        p_lastName IN USERS.LastName%TYPE,
        p_email IN USERS.Email%TYPE,
        p_password IN USERS.Password%TYPE,
        p_roleID IN USERS.RoleID%TYPE,
        p_profileImage IN USERS.ProfileImage%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count FROM USERS WHERE UserID = p_userID;

        IF v_count = 0 THEN
            INSERT INTO USERS (UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage)
            VALUES (p_userID, p_firstName, p_lastName, p_email, p_password, p_roleID, p_profileImage);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: UserID ' || p_userID || ' already exists.');
        END IF;
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error: Email ' || p_email || ' already exists.');
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CREATE_NEW_USER: ' || SQLERRM);
    END CREATE_NEW_USER;

    PROCEDURE AUTHENTICATE_USER (
        p_email IN USERS.Email%TYPE,
        p_found_userID OUT USERS.UserID%TYPE,
        p_firstName OUT USERS.FirstName%TYPE,
        p_lastName OUT USERS.LastName%TYPE,
        p_found_email OUT USERS.Email%TYPE,
        p_hashed_password OUT USERS.Password%TYPE,
        p_roleID OUT USERS.RoleID%TYPE,
        p_profileImage OUT USERS.ProfileImage%TYPE,
        p_created_at OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_userID, p_firstName, p_lastName, p_found_email, p_hashed_password, p_roleID, p_profileImage, p_created_at
        FROM USERS
        WHERE Email = p_email;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_userID := NULL;
            p_firstName := NULL;
            p_lastName := NULL;
            p_found_email := NULL;
            p_hashed_password := NULL;
            p_roleID := NULL;
            p_profileImage := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_userID := NULL;
            p_firstName := NULL;
            p_lastName := NULL;
            p_found_email := NULL;
            p_hashed_password := NULL;
            p_roleID := NULL;
            p_profileImage := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in AUTHENTICATE_USER: ' || SQLERRM);
    END AUTHENTICATE_USER;

    PROCEDURE DELETE_USER (
        p_userID IN USERS.UserID%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM USERS
        WHERE UserID = p_userID;

        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No user deleted for UserID ' || p_userID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_USER: ' || SQLERRM);
    END DELETE_USER;

    PROCEDURE UPDATE_USER (
        p_userID IN USERS.UserID%TYPE,
        p_firstName IN USERS.FirstName%TYPE,
        p_lastName IN USERS.LastName%TYPE,
        p_password IN USERS.Password%TYPE,
        p_roleID IN USERS.RoleID%TYPE,
        p_profileImage IN USERS.ProfileImage%TYPE,
        p_success OUT BOOLEAN
    )
        IS
        v_current_firstName USERS.FirstName%TYPE;
        v_current_lastName  USERS.LastName%TYPE;
        v_current_password  USERS.Password%TYPE;
        v_current_roleID    USERS.RoleID%TYPE;
        v_rows_updated      NUMBER;
    BEGIN
        SELECT FirstName, LastName, Password, RoleID
        INTO v_current_firstName, v_current_lastName, v_current_password, v_current_roleID
        FROM USERS
        WHERE UserID = p_userID;

        UPDATE USERS
        SET
            FirstName    = NVL(p_firstName, v_current_firstName),
            LastName     = NVL(p_lastName, v_current_lastName),
            Password     = NVL(p_password, v_current_password),
            RoleID       = NVL(p_roleID, v_current_roleID),
            ProfileImage = p_profileImage
        WHERE UserID = p_userID;

        v_rows_updated := SQL%ROWCOUNT;
        IF v_rows_updated = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No user updated for UserID ' || p_userID);
        END IF;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error: User with ID ' || p_userID || ' not found for update.');
        WHEN DUP_VAL_ON_INDEX THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error: Cannot update user. Email already exists.');
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_USER: ' || SQLERRM);
    END UPDATE_USER;

    PROCEDURE GET_USER_BY_ID (
        p_userID IN USERS.UserID%TYPE,
        p_found_userID OUT USERS.UserID%TYPE,
        p_firstName OUT USERS.FirstName%TYPE,
        p_lastName OUT USERS.LastName%TYPE,
        p_found_email OUT USERS.Email%TYPE,
        p_password OUT USERS.Password%TYPE,
        p_roleID OUT USERS.RoleID%TYPE,
        p_profileImage OUT USERS.ProfileImage%TYPE,
        p_created_at OUT VARCHAR2,
        p_purpose IN VARCHAR2 DEFAULT 'get'
    )
        IS
    BEGIN
        SELECT UserID, FirstName, LastName, Email, Password, RoleID, ProfileImage,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_userID, p_firstName, p_lastName, p_found_email, p_password, p_roleID, p_profileImage, p_created_at
        FROM USERS
        WHERE UserID = p_userID;

        IF p_purpose = 'get' THEN
            p_password := NULL;
        END IF;

    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_userID := NULL;
            p_firstName := NULL;
            p_lastName := NULL;
            p_found_email := NULL;
            p_password := NULL;
            p_roleID := NULL;
            p_profileImage := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_userID := NULL;
            p_firstName := NULL;
            p_lastName := NULL;
            p_found_email := NULL;
            p_password := NULL;
            p_roleID := NULL;
            p_profileImage := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_USER_BY_ID: ' || SQLERRM);
    END GET_USER_BY_ID;

    PROCEDURE GET_USER_BY_EMAIL (
        p_email IN USERS.Email%TYPE,
        p_found_userID OUT USERS.UserID%TYPE,
        p_firstName OUT USERS.FirstName%TYPE,
        p_lastName OUT USERS.LastName%TYPE,
        p_found_email OUT USERS.Email%TYPE,
        p_roleID OUT USERS.RoleID%TYPE,
        p_profileImage OUT USERS.ProfileImage%TYPE,
        p_created_at OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT UserID, FirstName, LastName, Email, RoleID, ProfileImage,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_userID, p_firstName, p_lastName, p_found_email, p_roleID, p_profileImage, p_created_at
        FROM USERS
        WHERE Email = p_email;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_userID := NULL;
            p_firstName := NULL;
            p_lastName := NULL;
            p_found_email := NULL;
            p_roleID := NULL;
            p_profileImage := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_userID := NULL;
            p_firstName := NULL;
            p_lastName := NULL;
            p_found_email := NULL;
            p_roleID := NULL;
            p_profileImage := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_USER_BY_EMAIL: ' || SQLERRM);
    END GET_USER_BY_EMAIL;

    PROCEDURE GET_ALL_USERS (
        p_cursor OUT t_user_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT UserID, FirstName, LastName, Email, RoleID, ProfileImage,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM USERS
            ORDER BY UserID ASC;
    END GET_ALL_USERS;

    PROCEDURE EMAIL_EXISTS (
        p_email IN USERS.Email%TYPE,
        p_excludeUserID IN USERS.UserID%TYPE DEFAULT NULL,
        p_exists OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        IF p_excludeUserID IS NULL THEN
            SELECT COUNT(UserID)
            INTO v_count
            FROM USERS
            WHERE Email = p_email;
        ELSE
            SELECT COUNT(UserID)
            INTO v_count
            FROM USERS
            WHERE Email = p_email AND UserID != p_excludeUserID;
        END IF;

        IF v_count > 0 THEN
            p_exists := TRUE;
        ELSE
            p_exists := FALSE;
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            p_exists := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in EMAIL_EXISTS: ' || SQLERRM);
    END EMAIL_EXISTS;

END USER_PKG;