#!/bin/bash

echo "=== INDIVIDUAL CURL COMMANDS FOR DOCUMENTATION ==="
echo ""

BASE_URL="http://localhost:8000/api"

echo "1. GET All Courses"
echo "curl -X GET \"$BASE_URL/courses\" -H \"Content-Type: application/json\""
echo ""

echo "2. GET Courses with Filter"
echo "curl -X GET \"$BASE_URL/courses?status=published\" -H \"Content-Type: application/json\""
echo ""

echo "3. Create New Course"
echo "curl -X POST \"$BASE_URL/courses\" \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{"
echo "    \"title\": \"New Course\","
echo "    \"description\": \"Course description\","
echo "    \"max_students\": 25,"
echo "    \"instructor_id\": 1"
echo "  }'"
echo ""

echo "4. Get Course by ID"
echo "curl -X GET \"$BASE_URL/courses/1\" -H \"Content-Type: application/json\""
echo ""

echo "5. Update Course"
echo "curl -X PUT \"$BASE_URL/courses/1\" \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{"
echo "    \"title\": \"Updated Course\","
echo "    \"max_students\": 30"
echo "  }'"
echo ""

echo "6. Publish Course"
echo "curl -X PUT \"$BASE_URL/courses/1/publish\" -H \"Content-Type: application/json\""
echo ""

echo "7. Unpublish Course"
echo "curl -X PUT \"$BASE_URL/courses/1/unpublish\" -H \"Content-Type: application/json\""
echo ""

echo "8. Delete Course"
echo "curl -X DELETE \"$BASE_URL/courses/1\" -H \"Content-Type: application/json\""
echo ""

echo "9. Get All Students"
echo "curl -X GET \"$BASE_URL/students\" -H \"Content-Type: application/json\""
echo ""

echo "10. Create New Student"
echo "curl -X POST \"$BASE_URL/students\" \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{"
echo "    \"email\": \"newstudent@test.com\","
echo "    \"name\": \"New Student\","
echo "    \"password\": \"password123\""
echo "  }'"
echo ""

echo "11. Get Student by ID"
echo "curl -X GET \"$BASE_URL/students/1\" -H \"Content-Type: application/json\""
echo ""

echo "12. Update Student"
echo "curl -X PUT \"$BASE_URL/students/1\" \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{"
echo "    \"name\": \"Updated Student\","
echo "    \"email\": \"updated@email.com\""
echo "  }'"
echo ""

echo "13. Delete Student"
echo "curl -X DELETE \"$BASE_URL/students/1\" -H \"Content-Type: application/json\""
echo ""

echo "14. Enroll Student to Course"
echo "curl -X POST \"$BASE_URL/enrollments\" \\"
echo "  -H \"Content-Type: application/json\" \\"
echo "  -d '{"
echo "    \"student_id\": 1,"
echo "    \"course_id\": 1"
echo "  }'"
echo ""

echo "15. Get Student Enrollments"
echo "curl -X GET \"$BASE_URL/students/1/enrollments\" -H \"Content-Type: application/json\""
echo ""

echo "16. Complete Enrollment"
echo "curl -X PUT \"$BASE_URL/enrollments/1/complete\" -H \"Content-Type: application/json\""
echo ""

echo "✅ All curl commands ready for testing!"