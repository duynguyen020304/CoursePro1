CREATE OR REPLACE PACKAGE STUDENT_PKG AS

    PROCEDURE CREATE_NEW_STUDENT (
        p_studentID IN STUDENT.StudentID%TYPE,
        p_userID    IN STUDENT.UserID%TYPE,
        p_success   OUT BOOLEAN
    );

    PROCEDURE DELETE_STUDENT (
        p_studentID IN STUDENT.StudentID%TYPE,
        p_success   OUT BOOLEAN
    );

    PROCEDURE UPDATE_STUDENT (
        p_studentID IN STUDENT.StudentID%TYPE,
        p_userID    IN STUDENT.UserID%TYPE,
        p_success   OUT BOOLEAN
    );

    TYPE t_student_cursor IS REF CURSOR;

    PROCEDURE GET_STUDENT_BY_ID (
        p_studentID         IN STUDENT.StudentID%TYPE,
        p_found_studentID   OUT STUDENT.StudentID%TYPE,
        p_userID            OUT STUDENT.UserID%TYPE,
        p_created_at        OUT VARCHAR2
    );

    PROCEDURE GET_STUDENT_BY_USER_ID (
        p_userID            IN STUDENT.UserID%TYPE,
        p_found_studentID   OUT STUDENT.StudentID%TYPE,
        p_found_userID      OUT STUDENT.UserID%TYPE,
        p_created_at        OUT VARCHAR2
    );

    PROCEDURE GET_ALL_STUDENTS (
        p_cursor OUT t_student_cursor
    );

END STUDENT_PKG;
/

CREATE OR REPLACE PACKAGE BODY STUDENT_PKG AS

    PROCEDURE CREATE_NEW_STUDENT (
        p_studentID IN STUDENT.StudentID%TYPE,
        p_userID    IN STUDENT.UserID%TYPE,
        p_success   OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count FROM STUDENT WHERE StudentID = p_studentID;

        IF v_count = 0 THEN
            INSERT INTO STUDENT (StudentID, UserID)
            VALUES (p_studentID, p_userID);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: StudentID ' || p_studentID || ' already exists.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CREATE_NEW_STUDENT: ' || SQLERRM);
    END CREATE_NEW_STUDENT;

    PROCEDURE DELETE_STUDENT (
        p_studentID IN STUDENT.StudentID%TYPE,
        p_success   OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM STUDENT
        WHERE StudentID = p_studentID;

        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No student deleted for StudentID ' || p_studentID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_STUDENT: ' || SQLERRM);
    END DELETE_STUDENT;

    PROCEDURE UPDATE_STUDENT (
        p_studentID IN STUDENT.StudentID%TYPE,
        p_userID    IN STUDENT.UserID%TYPE,
        p_success   OUT BOOLEAN
    )
        IS
        v_rows_updated NUMBER;
    BEGIN
        UPDATE STUDENT
        SET
            UserID = p_userID
        WHERE StudentID = p_studentID;

        v_rows_updated := SQL%ROWCOUNT;
        IF v_rows_updated = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No student updated for StudentID ' || p_studentID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_STUDENT: ' || SQLERRM);
    END UPDATE_STUDENT;

    PROCEDURE GET_STUDENT_BY_ID (
        p_studentID         IN STUDENT.StudentID%TYPE,
        p_found_studentID   OUT STUDENT.StudentID%TYPE,
        p_userID            OUT STUDENT.UserID%TYPE,
        p_created_at        OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT StudentID, UserID,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_studentID, p_userID, p_created_at
        FROM STUDENT
        WHERE StudentID = p_studentID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_studentID := NULL;
            p_userID := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_studentID := NULL;
            p_userID := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_STUDENT_BY_ID: ' || SQLERRM);
    END GET_STUDENT_BY_ID;

    PROCEDURE GET_STUDENT_BY_USER_ID (
        p_userID IN STUDENT.UserID%TYPE,
        p_found_studentID OUT STUDENT.StudentID%TYPE,
        p_found_userID OUT STUDENT.UserID%TYPE,
        p_created_at OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT StudentID, UserID,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_studentID, p_found_userID, p_created_at
        FROM STUDENT
        WHERE UserID = p_userID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_studentID := NULL;
            p_found_userID := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_studentID := NULL;
            p_found_userID := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_STUDENT_BY_USER_ID: ' || SQLERRM);
    END GET_STUDENT_BY_USER_ID;

    PROCEDURE GET_ALL_STUDENTS (
        p_cursor OUT t_student_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT StudentID, UserID,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM STUDENT
            ORDER BY StudentID ASC;
    END GET_ALL_STUDENTS;

END STUDENT_PKG;