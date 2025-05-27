CREATE OR REPLACE PACKAGE STUDENT_PKG AS

    PROCEDURE CREATE_STUDENT_PROC(
        p_StudentID IN STUDENT.StudentID%TYPE,
        p_UserID    IN STUDENT.UserID%TYPE
    );

    PROCEDURE DELETE_STUDENT_PROC(
        p_StudentID IN STUDENT.StudentID%TYPE
    );

    PROCEDURE UPDATE_STUDENT_PROC(
        p_StudentID IN STUDENT.StudentID%TYPE,
        p_UserID    IN STUDENT.UserID%TYPE
    );

    FUNCTION GET_STUDENT_BY_ID_FUNC(
        p_StudentID IN STUDENT.StudentID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_STUDENT_BY_USER_ID_FUNC(
        p_UserID IN STUDENT.UserID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_ALL_STUDENTS_FUNC
        RETURN SYS_REFCURSOR;

END STUDENT_PKG;
/

CREATE OR REPLACE PACKAGE BODY STUDENT_PKG AS

    PROCEDURE CREATE_STUDENT_PROC(
        p_StudentID IN STUDENT.StudentID%TYPE,
        p_UserID    IN STUDENT.UserID%TYPE
    ) IS
    BEGIN
        INSERT INTO STUDENT (StudentID, UserID)
        VALUES (p_StudentID, p_UserID);
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            DECLARE
                v_check_pk NUMBER;
                v_check_uq NUMBER;
            BEGIN
                SELECT COUNT(*) INTO v_check_pk FROM STUDENT WHERE StudentID = p_StudentID;
                IF v_check_pk > 0 THEN
                    RAISE_APPLICATION_ERROR(-20160, 'Student with StudentID ''' || p_StudentID || ''' already exists.');
                END IF;
                SELECT COUNT(*) INTO v_check_uq FROM STUDENT WHERE UserID = p_UserID;
                IF v_check_uq > 0 THEN
                    RAISE_APPLICATION_ERROR(-20161, 'User with UserID ''' || p_UserID || ''' is already registered as a student.');
                END IF;
                RAISE;
            END;
        WHEN OTHERS THEN
            RAISE;
    END CREATE_STUDENT_PROC;

    PROCEDURE DELETE_STUDENT_PROC(
        p_StudentID IN STUDENT.StudentID%TYPE
    ) IS
    BEGIN
        DELETE FROM STUDENT
        WHERE StudentID = p_StudentID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20162, 'Student with StudentID ''' || p_StudentID || ''' not found for deletion.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20162 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in DELETE_STUDENT_PROC: ' || SQLERRM);
            END IF;
    END DELETE_STUDENT_PROC;

    PROCEDURE UPDATE_STUDENT_PROC(
        p_StudentID IN STUDENT.StudentID%TYPE,
        p_UserID    IN STUDENT.UserID%TYPE
    ) IS
    BEGIN
        UPDATE STUDENT
        SET UserID = p_UserID
        WHERE StudentID = p_StudentID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20163, 'Student with StudentID ''' || p_StudentID || ''' not found for update.');
        END IF;
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            RAISE_APPLICATION_ERROR(-20161, 'Update failed: UserID ''' || p_UserID || ''' is already associated with another student.');
        WHEN OTHERS THEN
            IF SQLCODE = -20163 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UPDATE_STUDENT_PROC: ' || SQLERRM);
            END IF;
    END UPDATE_STUDENT_PROC;

    FUNCTION GET_STUDENT_BY_ID_FUNC(
        p_StudentID IN STUDENT.StudentID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                StudentID,
                UserID,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                STUDENT
            WHERE
                StudentID = p_StudentID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_STUDENT_BY_ID_FUNC;

    FUNCTION GET_STUDENT_BY_USER_ID_FUNC(
        p_UserID IN STUDENT.UserID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                StudentID,
                UserID,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                STUDENT
            WHERE
                UserID = p_UserID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_STUDENT_BY_USER_ID_FUNC;

    FUNCTION GET_ALL_STUDENTS_FUNC
        RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                StudentID,
                UserID,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                STUDENT
            ORDER BY StudentID ASC;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_ALL_STUDENTS_FUNC;

END STUDENT_PKG;
/

