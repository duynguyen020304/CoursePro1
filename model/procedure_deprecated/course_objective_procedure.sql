CREATE OR REPLACE PACKAGE COURSE_OBJECTIVE_PKG AS

    PROCEDURE CREATE_NEW_OBJECTIVE (
        p_objectiveID IN COURSEOBJECTIVE.ObjectiveID%TYPE,
        p_courseID    IN COURSEOBJECTIVE.CourseID%TYPE,
        p_objective   IN COURSEOBJECTIVE.Objective%TYPE,
        p_success     OUT BOOLEAN
    );

    PROCEDURE UPDATE_OBJECTIVE (
        p_objectiveID IN COURSEOBJECTIVE.ObjectiveID%TYPE,
        p_courseID    IN COURSEOBJECTIVE.CourseID%TYPE,
        p_objective   IN COURSEOBJECTIVE.Objective%TYPE,
        p_success     OUT BOOLEAN
    );

    PROCEDURE DELETE_OBJECTIVE (
        p_objectiveID IN COURSEOBJECTIVE.ObjectiveID%TYPE,
        p_success     OUT BOOLEAN
    );

    TYPE t_course_objective_cursor IS REF CURSOR;

    PROCEDURE GET_OBJECTIVE_BY_ID (
        p_objectiveID       IN COURSEOBJECTIVE.ObjectiveID%TYPE,
        p_found_objectiveID OUT COURSEOBJECTIVE.ObjectiveID%TYPE,
        p_courseID          OUT COURSEOBJECTIVE.CourseID%TYPE,
        p_objective         OUT COURSEOBJECTIVE.Objective%TYPE,
        p_created_at        OUT VARCHAR2
    );

    PROCEDURE GET_OBJECTIVES_BY_COURSE_ID (
        p_courseID  IN COURSEOBJECTIVE.CourseID%TYPE,
        p_cursor    OUT t_course_objective_cursor
    );

END COURSE_OBJECTIVE_PKG;
/

CREATE OR REPLACE PACKAGE BODY COURSE_OBJECTIVE_PKG AS

    PROCEDURE CREATE_NEW_OBJECTIVE (
        p_objectiveID IN COURSEOBJECTIVE.ObjectiveID%TYPE,
        p_courseID    IN COURSEOBJECTIVE.CourseID%TYPE,
        p_objective   IN COURSEOBJECTIVE.Objective%TYPE,
        p_success     OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count FROM COURSEOBJECTIVE WHERE ObjectiveID = p_objectiveID;

        IF v_count = 0 THEN
            INSERT INTO COURSEOBJECTIVE (ObjectiveID, CourseID, Objective)
            VALUES (p_objectiveID, p_courseID, p_objective);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: ObjectiveID ' || p_objectiveID || ' already exists.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CREATE_NEW_OBJECTIVE: ' || SQLERRM);
    END CREATE_NEW_OBJECTIVE;

    PROCEDURE UPDATE_OBJECTIVE (
        p_objectiveID IN COURSEOBJECTIVE.ObjectiveID%TYPE,
        p_courseID    IN COURSEOBJECTIVE.CourseID%TYPE,
        p_objective   IN COURSEOBJECTIVE.Objective%TYPE,
        p_success     OUT BOOLEAN
    )
        IS
        v_rows_updated NUMBER;
    BEGIN
        UPDATE COURSEOBJECTIVE
        SET
            Objective = p_objective
        WHERE ObjectiveID = p_objectiveID AND CourseID = p_courseID;

        v_rows_updated := SQL%ROWCOUNT;
        IF v_rows_updated = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No objective updated for ObjectiveID ' || p_objectiveID || ' and CourseID ' || p_courseID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_OBJECTIVE: ' || SQLERRM);
    END UPDATE_OBJECTIVE;

    PROCEDURE DELETE_OBJECTIVE (
        p_objectiveID IN COURSEOBJECTIVE.ObjectiveID%TYPE,
        p_success     OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM COURSEOBJECTIVE
        WHERE ObjectiveID = p_objectiveID;

        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No objective deleted for ObjectiveID ' || p_objectiveID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_OBJECTIVE: ' || SQLERRM);
    END DELETE_OBJECTIVE;

    PROCEDURE GET_OBJECTIVE_BY_ID (
        p_objectiveID       IN COURSEOBJECTIVE.ObjectiveID%TYPE,
        p_found_objectiveID OUT COURSEOBJECTIVE.ObjectiveID%TYPE,
        p_courseID          OUT COURSEOBJECTIVE.CourseID%TYPE,
        p_objective         OUT COURSEOBJECTIVE.Objective%TYPE,
        p_created_at        OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT ObjectiveID, CourseID, Objective,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_objectiveID, p_courseID, p_objective, p_created_at
        FROM COURSEOBJECTIVE
        WHERE ObjectiveID = p_objectiveID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_objectiveID := NULL;
            p_courseID := NULL;
            p_objective := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_objectiveID := NULL;
            p_courseID := NULL;
            p_objective := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_OBJECTIVE_BY_ID: ' || SQLERRM);
    END GET_OBJECTIVE_BY_ID;

    PROCEDURE GET_OBJECTIVES_BY_COURSE_ID (
        p_courseID  IN COURSEOBJECTIVE.CourseID%TYPE,
        p_cursor    OUT t_course_objective_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT ObjectiveID, CourseID, Objective,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM COURSEOBJECTIVE
            WHERE CourseID = p_courseID
            ORDER BY ObjectiveID ASC;
    EXCEPTION
        WHEN OTHERS THEN
            IF p_cursor%ISOPEN THEN
                CLOSE p_cursor;
            END IF;
            DBMS_OUTPUT.PUT_LINE('Error in GET_OBJECTIVES_BY_COURSE_ID: ' || SQLERRM);
    END GET_OBJECTIVES_BY_COURSE_ID;

END COURSE_OBJECTIVE_PKG;