CREATE OR REPLACE PACKAGE INSTRUCTOR_PKG AS

    PROCEDURE CREATE_INSTRUCTOR_PROC(
        p_InstructorID IN INSTRUCTOR.InstructorID%TYPE,
        p_UserID       IN INSTRUCTOR.UserID%TYPE,
        p_Biography    IN INSTRUCTOR.Biography%TYPE
    );

    PROCEDURE DELETE_INSTRUCTOR_PROC(
        p_InstructorID IN INSTRUCTOR.InstructorID%TYPE
    );

    PROCEDURE UPDATE_INSTRUCTOR_PROC(
        p_InstructorID IN INSTRUCTOR.InstructorID%TYPE,
        p_UserID       IN INSTRUCTOR.UserID%TYPE,
        p_Biography    IN INSTRUCTOR.Biography%TYPE
    );

    FUNCTION GET_INSTRUCTOR_BY_ID_FUNC(
        p_InstructorID IN INSTRUCTOR.InstructorID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_INSTRUCTOR_BY_USER_ID_FUNC(
        p_UserID IN INSTRUCTOR.UserID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_ALL_INSTRUCTORS_FUNC
        RETURN SYS_REFCURSOR;

END INSTRUCTOR_PKG;
/

CREATE OR REPLACE PACKAGE BODY INSTRUCTOR_PKG AS

    PROCEDURE CREATE_INSTRUCTOR_PROC(
        p_InstructorID IN INSTRUCTOR.InstructorID%TYPE,
        p_UserID       IN INSTRUCTOR.UserID%TYPE,
        p_Biography    IN INSTRUCTOR.Biography%TYPE
    ) IS
    BEGIN
        INSERT INTO INSTRUCTOR (InstructorID, UserID, Biography)
        VALUES (p_InstructorID, p_UserID, p_Biography);
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            RAISE_APPLICATION_ERROR(-20090, 'Instructor with InstructorID ''' || p_InstructorID || ''' or UserID ''' || p_UserID || ''' already exists.');
        WHEN OTHERS THEN
            RAISE;
    END CREATE_INSTRUCTOR_PROC;

    PROCEDURE DELETE_INSTRUCTOR_PROC(
        p_InstructorID IN INSTRUCTOR.InstructorID%TYPE
    ) IS
    BEGIN
        DELETE FROM INSTRUCTOR
        WHERE InstructorID = p_InstructorID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20091, 'Instructor with InstructorID ''' || p_InstructorID || ''' not found for deletion.');
        END IF;
        -- ON DELETE CASCADE for fk_instructor_users handles related records.
        -- But deleting INSTRUCTOR does not delete USER record.
        -- Related records in CourseInstructor will be deleted via ON DELETE CASCADE.
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20091 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in DELETE_INSTRUCTOR_PROC: ' || SQLERRM);
            END IF;
    END DELETE_INSTRUCTOR_PROC;

    PROCEDURE UPDATE_INSTRUCTOR_PROC(
        p_InstructorID IN INSTRUCTOR.InstructorID%TYPE,
        p_UserID       IN INSTRUCTOR.UserID%TYPE,
        p_Biography    IN INSTRUCTOR.Biography%TYPE
    ) IS
    BEGIN
        UPDATE INSTRUCTOR
        SET UserID = p_UserID,
            Biography = p_Biography
        WHERE InstructorID = p_InstructorID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20092, 'Instructor with InstructorID ''' || p_InstructorID || ''' not found for update.');
        END IF;
        -- PHP BLL checks ($stid !== false), success if no Oracle error.
        -- Oracle will handle UQ violation (UserID).
        -- Oracle will handle FK violation (UserID in USERS).
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            RAISE_APPLICATION_ERROR(-20090, 'Update failed: UserID ''' || p_UserID || ''' is already associated with another instructor.');
        WHEN OTHERS THEN
            IF SQLCODE = -20092 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UPDATE_INSTRUCTOR_PROC: ' || SQLERRM);
            END IF;
    END UPDATE_INSTRUCTOR_PROC;

    FUNCTION GET_INSTRUCTOR_BY_ID_FUNC(
        p_InstructorID IN INSTRUCTOR.InstructorID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                InstructorID,
                UserID,
                Biography,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                INSTRUCTOR
            WHERE
                InstructorID = p_InstructorID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_INSTRUCTOR_BY_ID_FUNC;

    FUNCTION GET_INSTRUCTOR_BY_USER_ID_FUNC(
        p_UserID IN INSTRUCTOR.UserID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                InstructorID,
                UserID,
                Biography,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                INSTRUCTOR
            WHERE
                UserID = p_UserID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_INSTRUCTOR_BY_USER_ID_FUNC;

    FUNCTION GET_ALL_INSTRUCTORS_FUNC
        RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                InstructorID,
                UserID,
                Biography,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                INSTRUCTOR
            ORDER BY InstructorID ASC;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_ALL_INSTRUCTORS_FUNC;

END INSTRUCTOR_PKG;
/