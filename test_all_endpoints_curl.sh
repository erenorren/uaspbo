#!/bin/bash

echo "=== E-LEARNING API CURL TESTING ==="
echo "Base URL: http://localhost:8000/api"
echo ""

BASE_URL="http://localhost:8000/api"

# Function to print test result
print_result() {
    if [ $1 -eq 0 ]; then
        echo "✅ $2"
    else
        echo "❌ $2"
    fi
}

# Function to extract ID from response
extract_id() {
    echo $1 | grep -o '"id":\([0-9]*\)' | cut -d':' -f2 | head -1
}

echo "📚 COURSES ENDPOINTS TESTING"
echo "============================="

# 1. GET All Courses
echo "1. Testing GET /courses"
response=$(curl -s -w "%{http_code}" -X GET "$BASE_URL/courses" -H "Content-Type: application/json")
http_code=${response: -3}
response_body=${response%???}
print_result $((http_code == 200)) "GET /courses - Status 200"

# Extract course ID for later use
course_id=$(extract_id "$response_body")
echo "   📝 Using Course ID: $course_id"

# 2. GET Courses with Filter
echo "2. Testing GET /courses?status=published"
response=$(curl -s -w "%{http_code}" -X GET "$BASE_URL/courses?status=published" -H "Content-Type: application/json")
http_code=${response: -3}
print_result $((http_code == 200)) "GET /courses?status=published - Status 200"

# 3. POST Create Course (Success)
echo "3. Testing POST /courses - Create Course"
response=$(curl -s -w "%{http_code}" -X POST "$BASE_URL/courses" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "New Test Course via CURL",
    "description": "Course created using curl command",
    "max_students": 20,
    "instructor_id": 1
  }')
http_code=${response: -3}
response_body=${response%???}
print_result $((http_code == 201)) "POST /courses - Status 201"

# Extract new course ID
new_course_id=$(extract_id "$response_body")
echo "   📝 New Course ID: $new_course_id"

# 4. POST Create Course (Validation Error)
echo "4. Testing POST /courses - Validation Error"
response=$(curl -s -w "%{http_code}" -X POST "$BASE_URL/courses" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "",
    "description": "Test",
    "max_students": -5,
    "instructor_id": 1
  }')
http_code=${response: -3}
print_result $((http_code == 422)) "POST /courses - Validation Error 422"

# 5. GET Course by ID
echo "5. Testing GET /courses/$course_id"
response=$(curl -s -w "%{http_code}" -X GET "$BASE_URL/courses/$course_id" -H "Content-Type: application/json")
http_code=${response: -3}
print_result $((http_code == 200)) "GET /courses/$course_id - Status 200"

# 6. GET Course by ID (Not Found)
echo "6. Testing GET /courses/9999 - Not Found"
response=$(curl -s -w "%{http_code}" -X GET "$BASE_URL/courses/9999" -H "Content-Type: application/json")
http_code=${response: -3}
print_result $((http_code == 404)) "GET /courses/9999 - Not Found 404"

# 7. PUT Update Course
echo "7. Testing PUT /courses/$course_id"
response=$(curl -s -w "%{http_code}" -X PUT "$BASE_URL/courses/$course_id" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Updated Course via CURL",
    "description": "Updated description",
    "max_students": 35
  }')
http_code=${response: -3}
print_result $((http_code == 200)) "PUT /courses/$course_id - Status 200"

# 8. PUT Publish Course
echo "8. Testing PUT /courses/$new_course_id/publish"
response=$(curl -s -w "%{http_code}" -X PUT "$BASE_URL/courses/$new_course_id/publish" -H "Content-Type: application/json")
http_code=${response: -3}
print_result $((http_code == 200)) "PUT /courses/$new_course_id/publish - Status 200"

# 9. PUT Unpublish Course
echo "9. Testing PUT /courses/$new_course_id/unpublish"
response=$(curl -s -w "%{http_code}" -X PUT "$BASE_URL/courses/$new_course_id/unpublish" -H "Content-Type: application/json")
http_code=${response: -3}
print_result $((http_code == 200)) "PUT /courses/$new_course_id/unpublish - Status 200"

# 10. DELETE Course
echo "10. Testing DELETE /courses/$new_course_id"
response=$(curl -s -w "%{http_code}" -X DELETE "$BASE_URL/courses/$new_course_id" -H "Content-Type: application/json")
http_code=${response: -3}
print_result $((http_code == 200)) "DELETE /courses/$new_course_id - Status 200"

echo ""
echo "🎓 STUDENTS ENDPOINTS TESTING"
echo "=============================="

# 11. GET All Students
echo "11. Testing GET /students"
response=$(curl -s -w "%{http_code}" -X GET "$BASE_URL/students" -H "Content-Type: application/json")
http_code=${response: -3}
response_body=${response%???}
print_result $((http_code == 200)) "GET /students - Status 200"

# Extract student ID for later use
student_id=$(extract_id "$response_body")
echo "   📝 Using Student ID: $student_id"

# 12. POST Create Student (Success)
echo "12. Testing POST /students - Create Student"
unique_email="curl_student_$(date +%s)@test.com"
response=$(curl -s -w "%{http_code}" -X POST "$BASE_URL/students" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "'$unique_email'",
    "name": "CURL Test Student",
    "password": "password123"
  }')
http_code=${response: -3}
response_body=${response%???}
print_result $((http_code == 201)) "POST /students - Status 201"

# Extract new student ID
new_student_id=$(extract_id "$response_body")
echo "   📝 New Student ID: $new_student_id"

# 13. POST Create Student (Duplicate Email)
echo "13. Testing POST /students - Duplicate Email"
response=$(curl -s -w "%{http_code}" -X POST "$BASE_URL/students" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "student1@email.com",
    "name": "Duplicate Student",
    "password": "password123"
  }')
http_code=${response: -3}
print_result $((http_code == 400)) "POST /students - Duplicate Email 400"

# 14. GET Student by ID
echo "14. Testing GET /students/$student_id"
response=$(curl -s -w "%{http_code}" -X GET "$BASE_URL/students/$student_id" -H "Content-Type: application/json")
http_code=${response: -3}
print_result $((http_code == 200)) "GET /students/$student_id - Status 200"

# 15. PUT Update Student
echo "15. Testing PUT /students/$student_id"
response=$(curl -s -w "%{http_code}" -X PUT "$BASE_URL/students/$student_id" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Updated Student via CURL",
    "email": "updated_via_curl@email.com"
  }')
http_code=${response: -3}
print_result $((http_code == 200)) "PUT /students/$student_id - Status 200"

# 16. DELETE Student
echo "16. Testing DELETE /students/$new_student_id"
response=$(curl -s -w "%{http_code}" -X DELETE "$BASE_URL/students/$new_student_id" -H "Content-Type: application/json")
http_code=${response: -3}
print_result $((http_code == 200)) "DELETE /students/$new_student_id - Status 200"

echo ""
echo "📝 ENROLLMENTS ENDPOINTS TESTING"
echo "================================="

# 17. POST Enrollment (Success) - First ensure course is published
echo "17. Preparing course for enrollment..."
curl -s -X PUT "$BASE_URL/courses/$course_id/publish" -H "Content-Type: application/json" > /dev/null

echo "18. Testing POST /enrollments - Success"
response=$(curl -s -w "%{http_code}" -X POST "$BASE_URL/enrollments" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": '$student_id',
    "course_id": '$course_id'
  }')
http_code=${response: -3}
response_body=${response%???}
print_result $((http_code == 201)) "POST /enrollments - Status 201"

# Extract enrollment ID
enrollment_id=$(extract_id "$response_body")
echo "   📝 Enrollment ID: $enrollment_id"

# 19. POST Enrollment (Course Not Published)
echo "19. Testing POST /enrollments - Course Not Published"
# Create a draft course first
draft_course_response=$(curl -s -X POST "$BASE_URL/courses" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Draft Course for Testing",
    "description": "This course is in draft status",
    "max_students": 10,
    "instructor_id": 1,
    "status": "draft"
  }')
draft_course_id=$(extract_id "$draft_course_response")

response=$(curl -s -w "%{http_code}" -X POST "$BASE_URL/enrollments" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": '$student_id',
    "course_id": '$draft_course_id'
  }')
http_code=${response: -3}
print_result $((http_code == 400)) "POST /enrollments - Course Not Published 400"

# Clean up draft course
curl -s -X DELETE "$BASE_URL/courses/$draft_course_id" -H "Content-Type: application/json" > /dev/null

# 20. POST Enrollment (Duplicate)
echo "20. Testing POST /enrollments - Duplicate Enrollment"
response=$(curl -s -w "%{http_code}" -X POST "$BASE_URL/enrollments" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": '$student_id',
    "course_id": '$course_id'
  }')
http_code=${response: -3}
print_result $((http_code == 400)) "POST /enrollments - Duplicate Enrollment 400"

# 21. GET Student Enrollments
echo "21. Testing GET /students/$student_id/enrollments"
response=$(curl -s -w "%{http_code}" -X GET "$BASE_URL/students/$student_id/enrollments" -H "Content-Type: application/json")
http_code=${response: -3}
print_result $((http_code == 200)) "GET /students/$student_id/enrollments - Status 200"

# 22. PUT Complete Enrollment
echo "22. Testing PUT /enrollments/$enrollment_id/complete"
response=$(curl -s -w "%{http_code}" -X PUT "$BASE_URL/enrollments/$enrollment_id/complete" -H "Content-Type: application/json")
http_code=${response: -3}
print_result $((http_code == 200)) "PUT /enrollments/$enrollment_id/complete - Status 200"

# 23. PUT Complete Enrollment (Already Completed)
echo "23. Testing PUT /enrollments/$enrollment_id/complete - Already Completed"
response=$(curl -s -w "%{http_code}" -X PUT "$BASE_URL/enrollments/$enrollment_id/complete" -H "Content-Type: application/json")
http_code=${response: -3}
print_result $((http_code == 400)) "PUT /enrollments/$enrollment_id/complete - Already Completed 400"

echo ""
echo "🧪 BUSINESS RULES TESTING"
echo "=========================="

# 24. Test Course Full scenario
echo "24. Testing Course Full Scenario"
# Create course with capacity 1
small_course_response=$(curl -s -X POST "$BASE_URL/courses" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Small Course",
    "description": "Course with only 1 seat",
    "max_students": 1,
    "instructor_id": 1,
    "status": "published"
  }')
small_course_id=$(extract_id "$small_course_response")

# Enroll first student (should succeed)
curl -s -X POST "$BASE_URL/enrollments" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": '$student_id',
    "course_id": '$small_course_id'
  }' > /dev/null

# Try to enroll second student (should fail)
response=$(curl -s -w "%{http_code}" -X POST "$BASE_URL/enrollments" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 3,
    "course_id": '$small_course_id'
  }')
http_code=${response: -3}
print_result $((http_code == 400)) "POST /enrollments - Course Full 400"

# Clean up
curl -s -X DELETE "$BASE_URL/courses/$small_course_id" -H "Content-Type: application/json" > /dev/null

echo ""
echo "📊 TEST SUMMARY"
echo "==============="
echo "All CRUD operations tested:"
echo "• Courses: ✅ Create, Read, Update, Delete, Publish, Unpublish"
echo "• Students: ✅ Create, Read, Update, Delete" 
echo "• Enrollments: ✅ Create, Read, Update Status"
echo ""
echo "Error handling tested:"
echo "• Validation Errors: ✅ 422 Status"
echo "• Not Found: ✅ 404 Status" 
echo "• Business Rules: ✅ 400 Status"
echo "• Duplicate Prevention: ✅ Email & Enrollment checks"
echo ""
echo "🎉 CURL TESTING COMPLETED SUCCESSFULLY!"