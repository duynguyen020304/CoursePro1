<?php

namespace Tests\Unit;

use App\Support\RbacPermissionMap;
use PHPUnit\Framework\TestCase;

class RbacPermissionMapTest extends TestCase
{
    public function test_student_role_includes_my_courses_view_permission(): void
    {
        $permissions = RbacPermissionMap::permissionsForRole('student');

        $this->assertContains('my-courses.view', $permissions);
        $this->assertTrue(RbacPermissionMap::roleHasPermission('student', 'my-courses.view'));
    }
}
