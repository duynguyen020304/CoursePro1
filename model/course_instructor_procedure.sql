CREATE OR REPLACE PACKAGE COURSE_INSTRUCTOR_PKG AS

    PROCEDURE ADD_INSTRUCTOR_TO_COURSE (
        p_courseID     IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_instructorID IN COURSEINSTRUCTOR.InstructorID%TYPE,
        p_success      OUT BOOLEAN
    );

    PROCEDURE UPDATE_ASSIGNMENT (
        p_oldCourseID    IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_oldInstructorID IN COURSEINSTRUCTOR.InstructorID%TYPE,
        p_newCourseID    IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_newInstructorID IN COURSEINSTRUCTOR.InstructorID%TYPE,
        p_success        OUT BOOLEAN
    );

    PROCEDURE UNLINK_COURSE_INSTRUCTOR (
        p_courseID     IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_instructorID IN COURSEINSTRUCTOR.InstructorID%TYPE,
        p_success      OUT BOOLEAN
    );

    TYPE t_course_instructor_cursor IS REF CURSOR;

    PROCEDURE GET_INSTRUCTORS_BY_COURSE_ID (
        p_courseID IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_cursor   OUT t_course_instructor_cursor
    );

    PROCEDURE GET_ASSIGNMENT (
        p_courseID          IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_instructorID      IN COURSEINSTRUCTOR.InstructorID%TYPE,
        p_found_courseID    OUT COURSEINSTRUCTOR.CourseID%TYPE,
        p_found_instructorID OUT COURSEINSTRUCTOR.InstructorID%TYPE,
        p_created_at        OUT VARCHAR2
    );

END COURSE_INSTRUCTOR_PKG;
/

CREATE OR REPLACE PACKAGE BODY COURSE_INSTRUCTOR_PKG AS

    PROCEDURE ADD_INSTRUCTOR_TO_COURSE (
        p_courseID     IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_instructorID IN COURSEINSTRUCTOR.InstructorID%TYPE,
        p_success      OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count
        FROM COURSEINSTRUCTOR
        WHERE CourseID = p_courseID AND InstructorID = p_instructorID;

        IF v_count = 0 THEN
            INSERT INTO COURSEINSTRUCTOR (CourseID, InstructorID)
            VALUES (p_courseID, p_instructorID);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: Assignment already exists for CourseID ' || p_courseID || ' and InstructorID ' || p_instructorID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in ADD_INSTRUCTOR_TO_COURSE: ' || SQLERRM);
    END ADD_INSTRUCTOR_TO_COURSE;

    PROCEDURE UPDATE_ASSIGNMENT (
        p_oldCourseID    IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_oldInstructorID IN COURSEINSTRUCTOR.InstructorID%TYPE,
        p_newCourseID    IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_newInstructorID IN COURSEINSTRUCTOR.InstructorID%TYPE,
        p_success        OUT BOOLEAN
    )
        IS
        v_rows_updated NUMBER;
    BEGIN
        UPDATE COURSEINSTRUCTOR
        SET
            CourseID     = p_newCourseID,
            InstructorID = p_newInstructorID
        WHERE CourseID = p_oldCourseID AND InstructorID = p_oldInstructorID;

        v_rows_updated := SQL%ROWCOUNT;
        IF v_rows_updated = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No assignment updated for old CourseID ' || p_oldCourseID || ' and old InstructorID ' || p_oldInstructorID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_ASSIGNMENT: ' || SQLERRM);
    END UPDATE_ASSIGNMENT;

    PROCEDURE UNLINK_COURSE_INSTRUCTOR (
        p_courseID     IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_instructorID IN COURSEINSTRUCTOR.InstructorID%TYPE,
        p_success      OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM COURSEINSTRUCTOR
        WHERE CourseID = p_courseID AND InstructorID = p_instructorID;

        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No assignment deleted for CourseID ' || p_courseID || ' and InstructorID ' || p_instructorID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UNLINK_COURSE_INSTRUCTOR: ' || SQLERRM);
    END UNLINK_COURSE_INSTRUCTOR;

    PROCEDURE GET_INSTRUCTORS_BY_COURSE_ID (
        p_courseID IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_cursor   OUT t_course_instructor_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT CourseID, InstructorID, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM COURSEINSTRUCTOR
            WHERE CourseID = p_courseID
            ORDER BY InstructorID ASC;
    EXCEPTION
        WHEN OTHERS THEN
            IF p_cursor%ISOPEN THEN
                CLOSE p_cursor;
            END IF;
            DBMS_OUTPUT.PUT_LINE('Error in GET_INSTRUCTORS_BY_COURSE_ID: ' || SQLERRM);
    END GET_INSTRUCTORS_BY_COURSE_ID;

    PROCEDURE GET_ASSIGNMENT (
        p_courseID          IN COURSEINSTRUCTOR.CourseID%TYPE,
        p_instructorID      IN COURSEINSTRUCTOR.InstructorID%TYPE,
        p_found_courseID    OUT COURSEINSTRUCTOR.CourseID%TYPE,
        p_found_instructorID OUT COURSEINSTRUCTOR.InstructorID%TYPE,
        p_created_at        OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT CourseID, InstructorID, TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_courseID, p_found_instructorID, p_created_at
        FROM COURSEINSTRUCTOR
        WHERE CourseID = p_courseID AND InstructorID = p_instructorID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_courseID := NULL;
            p_found_instructorID := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_courseID := NULL;
            p_found_instructorID := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_ASSIGNMENT: ' || SQLERRM);
    END GET_ASSIGNMENT;

END COURSE_INSTRUCTOR_PKG;