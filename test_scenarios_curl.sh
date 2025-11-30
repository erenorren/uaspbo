#!/bin/bash

echo "=== CURL TEST SCENARIOS FOR DOCUMENTATION ==="
echo ""

BASE_URL="http://localhost:8000/api"

echo "SCENARIO 1: Complete Course Lifecycle"
echo "--------------------------------------"

# 1. Create Course
echo "Step 1: Create Course"
create_course=$(curl -s -X POST "$BASE_URL/courses" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "Lifecycle Test Course",
    "description": "Testing complete lifecycle",
    "max_students": 5,
    "instructor_id": 1
  }')
course_id=$(echo $create_course | grep -o '"id":\([0-9]*\)' | cut -d':' -f2)
echo "✅ Course created with ID: $course_id"

# 2. Publish Course
echo "Step 2: Publish Course"
curl -s -X PUT "$BASE_URL/courses/$course_id/publish" -H "Content-Type: application/json"
echo "✅ Course published"

# 3. Create Student
echo "Step 3: Create Student"
create_student=$(curl -s -X POST "$BASE_URL/students" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "scenario_student_'$(date +%s)'@test.com",
    "name": "Scenario Student",
    "password": "password123"
  }')
student_id=$(echo $create_student | grep -o '"id":\([0-9]*\)' | cut -d':' -f2)
echo "✅ Student created with ID: $student_id"

# 4. Enroll Student
echo "Step 4: Enroll Student"
enrollment=$(curl -s -X POST "$BASE_URL/enrollments" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": '$student_id',
    "course_id": '$course_id'
  }')
enrollment_id=$(echo $enrollment | grep -o '"id":\([0-9]*\)' | cut -d':' -f2)
echo "✅ Student enrolled with Enrollment ID: $enrollment_id"

# 5. Complete Enrollment
echo "Step 5: Complete Enrollment"
curl -s -X PUT "$BASE_URL/enrollments/$enrollment_id/complete" -H "Content-Type: application/json"
echo "✅ Enrollment completed"

# 6. Verify Results
echo "Step 6: Verify Results"
echo "Course details:"
curl -s -X GET "$BASE_URL/courses/$course_id" -H "Content-Type: application/json" | grep -o '"title":"[^"]*"'
echo "Student enrollments:"
curl -s -X GET "$BASE_URL/students/$student_id/enrollments" -H "Content-Type: application/json" | grep -o '"status":"[^"]*"'

echo ""
echo "SCENARIO 2: Error Handling"
echo "---------------------------"

# 1. Validation Error
echo "1. Testing Validation Error:"
curl -s -X POST "$BASE_URL/courses" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "",
    "max_students": -1
  }' | grep -o '"success":false'

# 2. Not Found Error
echo "2. Testing Not Found Error:"
curl -s -X GET "$BASE_URL/courses/9999" -H "Content-Type: application/json" | grep -o '"success":false'

# 3. Business Rule Error
echo "3. Testing Business Rule Error (Duplicate Email):"
curl -s -X POST "$BASE_URL/students" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "student1@email.com",
    "name": "Duplicate",
    "password": "password123"
  }' | grep -o '"success":false'

echo ""
echo "🎉 ALL SCENARIOS COMPLETED!"