CREATE OR REPLACE PACKAGE COURSE_REQUIREMENT_PKG AS

    PROCEDURE CREATE_NEW_REQUIREMENT (
        p_requirementID IN COURSEREQUIREMENT.RequirementID%TYPE,
        p_courseID      IN COURSEREQUIREMENT.CourseID%TYPE,
        p_requirement   IN COURSEREQUIREMENT.Requirement%TYPE,
        p_success       OUT BOOLEAN
    );

    PROCEDURE UPDATE_REQUIREMENT (
        p_requirementID IN COURSEREQUIREMENT.RequirementID%TYPE,
        p_courseID      IN COURSEREQUIREMENT.CourseID%TYPE,
        p_requirement   IN COURSEREQUIREMENT.Requirement%TYPE,
        p_success       OUT BOOLEAN
    );

    PROCEDURE DELETE_REQUIREMENT (
        p_requirementID IN COURSEREQUIREMENT.RequirementID%TYPE,
        p_success       OUT BOOLEAN
    );

    TYPE t_course_requirement_cursor IS REF CURSOR;

    PROCEDURE GET_REQUIREMENT_BY_ID (
        p_requirementID     IN COURSEREQUIREMENT.RequirementID%TYPE,
        p_found_requirementID OUT COURSEREQUIREMENT.RequirementID%TYPE,
        p_courseID          OUT COURSEREQUIREMENT.CourseID%TYPE,
        p_requirement       OUT COURSEREQUIREMENT.Requirement%TYPE,
        p_created_at        OUT VARCHAR2
    );

    PROCEDURE GET_REQUIREMENTS_BY_COURSE_ID (
        p_courseID  IN COURSEREQUIREMENT.CourseID%TYPE,
        p_cursor    OUT t_course_requirement_cursor
    );

END COURSE_REQUIREMENT_PKG;
/

CREATE OR REPLACE PACKAGE BODY COURSE_REQUIREMENT_PKG AS

    PROCEDURE CREATE_NEW_REQUIREMENT (
        p_requirementID IN COURSEREQUIREMENT.RequirementID%TYPE,
        p_courseID      IN COURSEREQUIREMENT.CourseID%TYPE,
        p_requirement   IN COURSEREQUIREMENT.Requirement%TYPE,
        p_success       OUT BOOLEAN
    )
        IS
        v_count NUMBER;
    BEGIN
        SELECT COUNT(*) INTO v_count FROM COURSEREQUIREMENT WHERE RequirementID = p_requirementID;

        IF v_count = 0 THEN
            INSERT INTO COURSEREQUIREMENT (RequirementID, CourseID, Requirement)
            VALUES (p_requirementID, p_courseID, p_requirement);
            COMMIT;
            p_success := TRUE;
        ELSE
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: RequirementID ' || p_requirementID || ' already exists.');
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in CREATE_NEW_REQUIREMENT: ' || SQLERRM);
    END CREATE_NEW_REQUIREMENT;

    PROCEDURE UPDATE_REQUIREMENT (
        p_requirementID IN COURSEREQUIREMENT.RequirementID%TYPE,
        p_courseID      IN COURSEREQUIREMENT.CourseID%TYPE,
        p_requirement   IN COURSEREQUIREMENT.Requirement%TYPE,
        p_success       OUT BOOLEAN
    )
        IS
        v_rows_updated NUMBER;
    BEGIN
        UPDATE COURSEREQUIREMENT
        SET
            Requirement = p_requirement
        WHERE RequirementID = p_requirementID AND CourseID = p_courseID;

        v_rows_updated := SQL%ROWCOUNT;
        IF v_rows_updated = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No requirement updated for RequirementID ' || p_requirementID || ' and CourseID ' || p_courseID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in UPDATE_REQUIREMENT: ' || SQLERRM);
    END UPDATE_REQUIREMENT;

    PROCEDURE DELETE_REQUIREMENT (
        p_requirementID IN COURSEREQUIREMENT.RequirementID%TYPE,
        p_success       OUT BOOLEAN
    )
        IS
        v_rows_deleted NUMBER;
    BEGIN
        DELETE FROM COURSEREQUIREMENT
        WHERE RequirementID = p_requirementID;

        v_rows_deleted := SQL%ROWCOUNT;
        IF v_rows_deleted = 1 THEN
            COMMIT;
            p_success := TRUE;
        ELSE
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Warning: No requirement deleted for RequirementID ' || p_requirementID);
        END IF;
    EXCEPTION
        WHEN OTHERS THEN
            ROLLBACK;
            p_success := FALSE;
            DBMS_OUTPUT.PUT_LINE('Error in DELETE_REQUIREMENT: ' || SQLERRM);
    END DELETE_REQUIREMENT;

    PROCEDURE GET_REQUIREMENT_BY_ID (
        p_requirementID     IN COURSEREQUIREMENT.RequirementID%TYPE,
        p_found_requirementID OUT COURSEREQUIREMENT.RequirementID%TYPE,
        p_courseID          OUT COURSEREQUIREMENT.CourseID%TYPE,
        p_requirement       OUT COURSEREQUIREMENT.Requirement%TYPE,
        p_created_at        OUT VARCHAR2
    )
        IS
    BEGIN
        SELECT RequirementID, CourseID, Requirement,
               TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6')
        INTO p_found_requirementID, p_courseID, p_requirement, p_created_at
        FROM COURSEREQUIREMENT
        WHERE RequirementID = p_requirementID;
    EXCEPTION
        WHEN NO_DATA_FOUND THEN
            p_found_requirementID := NULL;
            p_courseID := NULL;
            p_requirement := NULL;
            p_created_at := NULL;
        WHEN OTHERS THEN
            p_found_requirementID := NULL;
            p_courseID := NULL;
            p_requirement := NULL;
            p_created_at := NULL;
            DBMS_OUTPUT.PUT_LINE('Error in GET_REQUIREMENT_BY_ID: ' || SQLERRM);
    END GET_REQUIREMENT_BY_ID;

    PROCEDURE GET_REQUIREMENTS_BY_COURSE_ID (
        p_courseID  IN COURSEREQUIREMENT.CourseID%TYPE,
        p_cursor    OUT t_course_requirement_cursor
    )
        IS
    BEGIN
        OPEN p_cursor FOR
            SELECT RequirementID, CourseID, Requirement,
                   TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM COURSEREQUIREMENT
            WHERE CourseID = p_courseID
            ORDER BY RequirementID ASC;
    EXCEPTION
        WHEN OTHERS THEN
            IF p_cursor%ISOPEN THEN
                CLOSE p_cursor;
            END IF;
            DBMS_OUTPUT.PUT_LINE('Error in GET_REQUIREMENTS_BY_COURSE_ID: ' || SQLERRM);
    END GET_REQUIREMENTS_BY_COURSE_ID;

END COURSE_REQUIREMENT_PKG;