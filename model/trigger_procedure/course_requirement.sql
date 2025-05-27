CREATE OR REPLACE PACKAGE COURSE_REQUIREMENT_PKG AS

    PROCEDURE CREATE_REQUIREMENT_PROC(
        p_RequirementID IN COURSEREQUIREMENT.RequirementID%TYPE,
        p_CourseID      IN COURSEREQUIREMENT.CourseID%TYPE,
        p_Requirement   IN COURSEREQUIREMENT.Requirement%TYPE
    );

    PROCEDURE UPDATE_REQUIREMENT_PROC(
        p_RequirementID IN COURSEREQUIREMENT.RequirementID%TYPE,
        p_CourseID      IN COURSEREQUIREMENT.CourseID%TYPE,
        p_Requirement   IN COURSEREQUIREMENT.Requirement%TYPE
    );

    PROCEDURE DELETE_REQUIREMENT_PROC(
        p_RequirementID IN COURSEREQUIREMENT.RequirementID%TYPE
    );

    FUNCTION GET_REQ_BY_REQ_ID_FUNC(
        p_RequirementID IN COURSEREQUIREMENT.RequirementID%TYPE
    ) RETURN SYS_REFCURSOR;

    FUNCTION GET_REQS_BY_COURSE_ID_FUNC(
        p_CourseID IN COURSEREQUIREMENT.CourseID%TYPE
    ) RETURN SYS_REFCURSOR;

END COURSE_REQUIREMENT_PKG;
/

CREATE OR REPLACE PACKAGE BODY COURSE_REQUIREMENT_PKG AS

    PROCEDURE CREATE_REQUIREMENT_PROC(
        p_RequirementID IN COURSEREQUIREMENT.RequirementID%TYPE,
        p_CourseID      IN COURSEREQUIREMENT.CourseID%TYPE,
        p_Requirement   IN COURSEREQUIREMENT.Requirement%TYPE
    ) IS
    BEGIN
        INSERT INTO COURSEREQUIREMENT (RequirementID, CourseID, Requirement)
        VALUES (p_RequirementID, p_CourseID, p_Requirement);
        -- PHP BLL checks for affectedRows === 1. SQL%ROWCOUNT will be 1 on success.
        -- Oracle will handle PK violation (DUP_VAL_ON_INDEX if (RequirementID, CourseID) pair already exists).
        -- Oracle will handle FK violation (if CourseID does not exist in COURSE table).
    EXCEPTION
        WHEN DUP_VAL_ON_INDEX THEN
            RAISE_APPLICATION_ERROR(-20080, 'Requirement with RequirementID ''' || p_RequirementID || ''' and CourseID ''' || p_CourseID || ''' already exists.');
        WHEN OTHERS THEN
            RAISE;
    END CREATE_REQUIREMENT_PROC;

    PROCEDURE UPDATE_REQUIREMENT_PROC(
        p_RequirementID IN COURSEREQUIREMENT.RequirementID%TYPE,
        p_CourseID      IN COURSEREQUIREMENT.CourseID%TYPE,
        p_Requirement   IN COURSEREQUIREMENT.Requirement%TYPE
    ) IS
    BEGIN
        UPDATE COURSEREQUIREMENT
        SET Requirement = p_Requirement
        WHERE RequirementID = p_RequirementID AND CourseID = p_CourseID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20081, 'CourseRequirement with RequirementID ''' || p_RequirementID || ''' and CourseID ''' || p_CourseID || ''' not found for update.');
        END IF;
        -- PHP BLL checks ($stid !== false), implying success if no Oracle error.
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20081 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in UPDATE_REQUIREMENT_PROC: ' || SQLERRM);
            END IF;
    END UPDATE_REQUIREMENT_PROC;

    PROCEDURE DELETE_REQUIREMENT_PROC(
        p_RequirementID IN COURSEREQUIREMENT.RequirementID%TYPE
    ) IS
    BEGIN
        DELETE FROM COURSEREQUIREMENT
        WHERE RequirementID = p_RequirementID;

        IF SQL%ROWCOUNT = 0 THEN
            RAISE_APPLICATION_ERROR(-20082, 'No CourseRequirement found with RequirementID ''' || p_RequirementID || ''' for deletion, or no rows deleted.');
        END IF;
        -- Note: This deletes ALL records matching p_RequirementID, regardless of CourseID,
        -- mirroring the BLL's current behavior.
    EXCEPTION
        WHEN OTHERS THEN
            IF SQLCODE = -20082 THEN
                RAISE;
            ELSE
                RAISE_APPLICATION_ERROR(-20000, 'Unexpected error in DELETE_REQUIREMENT_PROC: ' || SQLERRM);
            END IF;
    END DELETE_REQUIREMENT_PROC;

    FUNCTION GET_REQ_BY_REQ_ID_FUNC(
        p_RequirementID IN COURSEREQUIREMENT.RequirementID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                RequirementID,
                CourseID,
                Requirement,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                COURSEREQUIREMENT
            WHERE
                RequirementID = p_RequirementID;
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_REQ_BY_REQ_ID_FUNC;

    FUNCTION GET_REQS_BY_COURSE_ID_FUNC(
        p_CourseID IN COURSEREQUIREMENT.CourseID%TYPE
    ) RETURN SYS_REFCURSOR IS
        v_cursor SYS_REFCURSOR;
    BEGIN
        OPEN v_cursor FOR
            SELECT
                RequirementID,
                CourseID,
                Requirement,
                TO_CHAR(created_at, 'YYYY-MM-DD HH24:MI:SS.FF6') AS created_at_formatted
            FROM
                COURSEREQUIREMENT
            WHERE
                CourseID = p_CourseID
            ORDER BY RequirementID ASC; -- As per BLL
        RETURN v_cursor;
    EXCEPTION
        WHEN OTHERS THEN
            RAISE;
    END GET_REQS_BY_COURSE_ID_FUNC;

END COURSE_REQUIREMENT_PKG;
/