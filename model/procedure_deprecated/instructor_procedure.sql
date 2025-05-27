CREATE OR REPLACE PACKAGE INSTRUCTOR_PKG AS

    PROCEDURE CREATE_NEW_INSTRUCTOR (
        p_instructorID IN INSTRUCTOR.InstructorID%TYPE,
        p_userID       IN INSTRUCTOR.UserID%TYPE,
        p_biography    IN INSTRUCTOR.Biography%TYPE,
        p_success      OUT BOOLEAN
    );

    PROCEDURE DELETE_INSTRUCTOR (
        p_instructorID IN INSTRUCTOR.InstructorID%TYPE,
        p_success      OUT BOOLEAN
    );

    PROCEDURE UPDATE_INSTRUCTOR (
        p_instructorID IN INSTRUCTOR.InstructorID%TYPE,
        p_userID       IN INSTRUCTOR.UserID%TYPE,
        p_biography    IN INSTRUCTOR.Biography%TYPE,
        p_success      OUT BOOLEAN
    );

    TYPE t_instructor_cursor IS REF CURSOR;

    PROCEDURE GET_INSTRUCTOR_BY_ID (
        p_instructorID      IN INSTRUCTOR.InstructorID%TYPE,
        p_found_instructorID OUT INSTRUCTOR.InstructorID%TYPE,
        p_userID            OUT INSTRUCTOR.UserID%TYPE,
        p_biography         OUT INSTRUCTOR.Biography%TYPE,
        p_created_at        OUT VARCHAR2
    );

    PROCEDURE GET_INSTRUCTOR_BY_USER_ID (
        p_userID            IN INSTRUCTOR.UserID%TYPE,
        p_found_instructorID OUT INSTRUCTOR.InstructorID%TYPE,
        p_found_userID      OUT INSTRUCTOR.UserID%TYPE,
        p_biography         OUT INSTRUCTOR.Biography%TYPE,
        p_created_at        OUT VARCHAR2
    );

    PROCEDURE GET_ALL_INSTRUCTORS (
        p_cursor OUT t_instructor_cursor
    );

END INSTRUCTOR_PKG;
/

CREATE OR REPLACE PACKAGE BODY INSTRUCTOR_PKG AS

    PROCEDURE CREATE_NEW_INSTRUCTOR (
        p_instructorID IN INSTRUCTOR.InstructorID%TYPE,
        p_userID       IN INSTRUCTOR.UserID%TYPE,
        p_biography    IN INSTRUCTOR.Biography%TYPE,
        p_success      OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count FROM INSTRUCTOR WHERE InstructorID = p_instructorID;

        IF v_count = 0 THEN
            INSERT INTO INSTRUCTOR (InstructorID, UserID, Biography)
            VALUES (p_instructorID, p_userID, p_biography);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: InstructorID ' || p_instructorID || ' already exists.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CREATE_NEW_INSTRUCTOR: ' || SQLERRM);
    END CREATE_NEW_INSTRUCTOR;

    PROCEDURE DELETE_INSTRUCTOR (
        p_instructorID IN INSTRUCTOR.InstructorID%TYPE,
        p_success      OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM INSTRUCTOR
        WHERE InstructorID = p_instructorID;

        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No instructor deleted for InstructorID ' || p_instructorID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_INSTRUCTOR: ' || SQLERRM);
    END DELETE_INSTRUCTOR;

    PROCEDURE UPDATE_INSTRUCTOR (
        p_instructorID IN INSTRUCTOR.InstructorID%TYPE,
        p_userID       IN INSTRUCTOR.UserID%TYPE,
        p_biography    IN INSTRUCTOR.Biography%TYPE,
        p_success      OUT BOOLEAN
    )
        IS
        v_rows_updated NUMBER;
    BEGIN
        UPDATE INSTRUCTOR
        SET
            UserID    = p_userID,
            Biography = p_biography
        WHERE InstructorID = p_instructorID;

        v_rows_updated := SQL%ROWCOUNT;
        IF v_rows_updated = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No instructor updated for InstructorID ' || p_instructorID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_INSTRUCTOR: ' || SQLERRM);
    END UPDATE_INSTRUCTOR;

    PROCEDURE GET_INSTRUCTOR_BY_ID (
        p_instructorID      IN INSTRUCTOR.InstructorID%TYPE,
        p_found_instructorID OUT INSTRUCTOR.InstructorID%TYPE,
        p_userID            OUT INSTRUCTOR.UserID%TYPE,
        p_biography         OUT INSTRUCTOR.Biography%TYPE,
        p_created_at        OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT InstructorID, UserID, Biography,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_instructorID, p_userID, p_biography, p_created_at
        FROM INSTRUCTOR
        WHERE InstructorID = p_instructorID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_instructorID := NULL;
            p_userID := NULL;
            p_biography := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_instructorID := NULL;
            p_userID := NULL;
            p_biography := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_INSTRUCTOR_BY_ID: ' || SQLERRM);
    END GET_INSTRUCTOR_BY_ID;

    PROCEDURE GET_INSTRUCTOR_BY_USER_ID (
        p_userID            IN INSTRUCTOR.UserID%TYPE,
        p_found_instructorID OUT INSTRUCTOR.InstructorID%TYPE,
        p_found_userID      OUT INSTRUCTOR.UserID%TYPE,
        p_biography         OUT INSTRUCTOR.Biography%TYPE,
        p_created_at        OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT InstructorID, UserID, Biography,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_instructorID, p_found_userID, p_biography, p_created_at
        FROM INSTRUCTOR
        WHERE UserID = p_userID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_instructorID := NULL;
            p_found_userID := NULL;
            p_biography := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_instructorID := NULL;
            p_found_userID := NULL;
            p_biography := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_INSTRUCTOR_BY_USER_ID: ' || SQLERRM);
    END GET_INSTRUCTOR_BY_USER_ID;

    PROCEDURE GET_ALL_INSTRUCTORS (
        p_cursor OUT t_instructor_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT InstructorID, UserID, Biography,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM INSTRUCTOR
            ORDER BY InstructorID ASC;
    END GET_ALL_INSTRUCTORS;

END INSTRUCTOR_PKG;