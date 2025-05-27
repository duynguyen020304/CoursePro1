CREATE OR REPLACE PACKAGE COURSE_OBJECTIVE_PKG AS

    PROCEDURE CREATE_OBJECTIVE_PROC(
        p_ObjectiveID IN COURSEOBJECTIVE.ObjectiveID%TYPE,
        p_CourseID    IN COURSEOBJECTIVE.CourseID%TYPE,
        p_Objective   IN COURSEOBJECTIVE.Objective%TYPE
    );

    PROCEDURE UPDATE_OBJECTIVE_PROC(
        p_ObjectiveID IN COURSEOBJECTIVE.ObjectiveID%TYPE,
        p_CourseID    IN COURSEOBJECTIVE.CourseID%TYPE,
        p_Objective   IN COURSEOBJECTIVE.Objective%TYPE
    );

    PROCEDURE DELETE_OBJECTIVE_PROC(
        p_ObjectiveID IN COURSEOBJECTIVE.ObjectiveID%TYPE
    );

    FUNCTION GET_OBJ_BY_OBJ_ID_FUNC(
        p_ObjectiveID IN COURSEOBJECTIVE.ObjectiveID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_OBJS_BY_COURSE_ID_FUNC(
        p_CourseID IN COURSEOBJECTIVE.CourseID%TYPE
    ) RETURN SYS_REFCURSOR;

END COURSE_OBJECTIVE_PKG;
/

CREATE OR REPLACE PACKAGE BODY COURSE_OBJECTIVE_PKG AS

    PROCEDURE CREATE_OBJECTIVE_PROC(
        p_ObjectiveID IN COURSEOBJECTIVE.ObjectiveID%TYPE,
        p_CourseID    IN COURSEOBJECTIVE.CourseID%TYPE,
        p_Objective   IN COURSEOBJECTIVE.Objective%TYPE
    ) IS
    BEGIN
        INSERT INTO COURSEOBJECTIVE (ObjectiveID, CourseID, Objective)
        VALUES (p_ObjectiveID, p_CourseID, p_Objective);
        -- PHP BLL checks for affectedRows === 1. SQL%ROWCOUNT will be 1 on success.
        -- Oracle will handle PK violation (DUP_VAL_ON_INDEX if (ObjectiveID, CourseID) pair already exists).
        -- Oracle will handle FK violation (if CourseID does not exist in COURSE table).
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            RAISE_APPLICATION_ERROR(-20070, 'Objective with ObjectiveID ''' || p_ObjectiveID || ''' and CourseID ''' || p_CourseID || ''' already exists.');
        WHEN OTHERS THEN
            RAISE;
    END CREATE_OBJECTIVE_PROC;

    PROCEDURE UPDATE_OBJECTIVE_PROC(
        p_ObjectiveID IN COURSEOBJECTIVE.ObjectiveID%TYPE,
        p_CourseID    IN COURSEOBJECTIVE.CourseID%TYPE,
        p_Objective   IN COURSEOBJECTIVE.Objective%TYPE
    ) IS
    BEGIN
        UPDATE COURSEOBJECTIVE
        SET Objective = p_Objective
        WHERE ObjectiveID = p_ObjectiveID AND CourseID = p_CourseID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20071, 'CourseObjective with ObjectiveID ''' || p_ObjectiveID || ''' and CourseID ''' || p_CourseID || ''' not found for update.');
        END IF;
        -- PHP BLL checks ($stid !== false), implying success if no Oracle error.
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20071 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UPDATE_OBJECTIVE_PROC: ' || SQLERRM);
            END IF;
    END UPDATE_OBJECTIVE_PROC;

    PROCEDURE DELETE_OBJECTIVE_PROC(
        p_ObjectiveID IN COURSEOBJECTIVE.ObjectiveID%TYPE
    ) IS
    BEGIN
        DELETE FROM COURSEOBJECTIVE
        WHERE ObjectiveID = p_ObjectiveID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20072, 'No CourseObjective found with ObjectiveID ''' || p_ObjectiveID || ''' for deletion, or no rows deleted.');
        END IF;
        -- Note: This deletes ALL records matching p_ObjectiveID, regardless of CourseID,
        -- mirroring the BLL's current behavior.
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20072 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in DELETE_OBJECTIVE_PROC: ' || SQLERRM);
            END IF;
    END DELETE_OBJECTIVE_PROC;

    FUNCTION GET_OBJ_BY_OBJ_ID_FUNC(
        p_ObjectiveID IN COURSEOBJECTIVE.ObjectiveID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                ObjectiveID,
                CourseID,
                Objective,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                COURSEOBJECTIVE
            WHERE
                ObjectiveID = p_ObjectiveID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_OBJ_BY_OBJ_ID_FUNC;

    FUNCTION GET_OBJS_BY_COURSE_ID_FUNC(
        p_CourseID IN COURSEOBJECTIVE.CourseID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                ObjectiveID,
                CourseID,
                Objective,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                COURSEOBJECTIVE
            WHERE
                CourseID = p_CourseID
            ORDER BY ObjectiveID ASC; -- As per BLL
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_OBJS_BY_COURSE_ID_FUNC;

END COURSE_OBJECTIVE_PKG;
/

