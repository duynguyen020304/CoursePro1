CREATE OR REPLACE PACKAGE COURSE_INSTRUCTOR_PKG AS

    PROCEDURE ADD_COURSE_INSTRUCTOR_PROC(
        p_CourseID     IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_InstructorID IN COURSEINSTRUCTOR.InstructorID%TYPE
    );

    PROCEDURE UPDATE_COURSE_INSTRUCTOR_PROC(
        p_OldCourseID     IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_OldInstructorID IN COURSEINSTRUCTOR.InstructorID%TYPE,
        p_NewCourseID     IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_NewInstructorID IN COURSEINSTRUCTOR.InstructorID%TYPE
    );

    PROCEDURE UNLINK_COURSE_INSTRUCTOR_PROC(
        p_CourseID     IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_InstructorID IN COURSEINSTRUCTOR.InstructorID%TYPE
    );

    FUNCTION GET_ASSIGNMENT_FUNC(
        p_CourseID     IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_InstructorID IN COURSEINSTRUCTOR.InstructorID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_INSTR_BY_COURSE_FUNC(
        p_CourseID IN COURSEINSTRUCTOR.CourseID%TYPE
    ) RETURN SYS_REFCURSOR;

END COURSE_INSTRUCTOR_PKG;
/

CREATE OR REPLACE PACKAGE BODY COURSE_INSTRUCTOR_PKG AS

    PROCEDURE ADD_COURSE_INSTRUCTOR_PROC(
        p_CourseID     IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_InstructorID IN COURSEINSTRUCTOR.InstructorID%TYPE
    ) IS
    BEGIN
        INSERT INTO COURSEINSTRUCTOR (CourseID, InstructorID)
        VALUES (p_CourseID, p_InstructorID);
        -- PHP BLL checks for affectedRows === 1. SQL%ROWCOUNT will be 1 on success.
        -- Oracle will handle PK violation (DUP_VAL_ON_INDEX if link exists).
        -- Oracle will handle FK violation (if CourseID or InstructorID does not exist).
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            RAISE_APPLICATION_ERROR(-20060, 'Assignment for CourseID ''' || p_CourseID || ''' and InstructorID ''' || p_InstructorID || ''' already exists.');
        WHEN OTHERS THEN
            RAISE;
    END ADD_COURSE_INSTRUCTOR_PROC;

    PROCEDURE UPDATE_COURSE_INSTRUCTOR_PROC(
        p_OldCourseID     IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_OldInstructorID IN COURSEINSTRUCTOR.InstructorID%TYPE,
        p_NewCourseID     IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_NewInstructorID IN COURSEINSTRUCTOR.InstructorID%TYPE
    ) IS
        v_row_exists NUMBER;
    BEGIN
        SELECT COUNT(*)
        INTO v_row_exists
        FROM COURSEINSTRUCTOR
        WHERE CourseID = p_OldCourseID AND InstructorID = p_OldInstructorID;

        IF v_row_exists = 0 THEN
            RAISE_APPLICATION_ERROR(-20063, 'Original assignment for CourseID ''' || p_OldCourseID || ''' and InstructorID ''' || p_OldInstructorID || ''' not found for update.');
        END IF;

        UPDATE COURSEINSTRUCTOR
        SET CourseID = p_NewCourseID,
            InstructorID = p_NewInstructorID
        WHERE CourseID = p_OldCourseID AND InstructorID = p_OldInstructorID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20061, 'Assignment for CourseID ''' || p_OldCourseID || ''' and InstructorID ''' || p_OldInstructorID || ''' not found for update, or no changes made.');
        END IF;
        -- PHP BLL checks ($stid !== false), implying success if no Oracle error.
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            RAISE_APPLICATION_ERROR(-20060, 'Cannot update: New assignment for CourseID ''' || p_NewCourseID || ''' and InstructorID ''' || p_NewInstructorID || ''' would create a duplicate.');
        WHEN OTHERS THEN
            IF SQLCODE = -20061 OR SQLCODE = -20063 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UPDATE_COURSE_INSTRUCTOR_PROC: ' || SQLERRM);
            END IF;
    END UPDATE_COURSE_INSTRUCTOR_PROC;

    PROCEDURE UNLINK_COURSE_INSTRUCTOR_PROC(
        p_CourseID     IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_InstructorID IN COURSEINSTRUCTOR.InstructorID%TYPE
    ) IS
    BEGIN
        DELETE FROM COURSEINSTRUCTOR
        WHERE CourseID = p_CourseID AND InstructorID = p_InstructorID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20062, 'Assignment for CourseID ''' || p_CourseID || ''' and InstructorID ''' || p_InstructorID || ''' not found for deletion.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20062 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UNLINK_COURSE_INSTRUCTOR_PROC: ' || SQLERRM);
            END IF;
    END UNLINK_COURSE_INSTRUCTOR_PROC;

    FUNCTION GET_ASSIGNMENT_FUNC(
        p_CourseID     IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_InstructorID IN COURSEINSTRUCTOR.InstructorID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                CourseID,
                InstructorID,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                COURSEINSTRUCTOR
            WHERE
                CourseID = p_CourseID AND InstructorID = p_InstructorID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_ASSIGNMENT_FUNC;

    FUNCTION GET_INSTR_BY_COURSE_FUNC(
        p_CourseID IN COURSEINSTRUCTOR.CourseID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                ci.CourseID,
                ci.InstructorID,
                TO_CHAR(ci.created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                COURSEINSTRUCTOR ci
            WHERE
                ci.CourseID = p_CourseID
            ORDER BY
                ci.InstructorID ASC;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_INSTR_BY_COURSE_FUNC;

END COURSE_INSTRUCTOR_PKG;
/
