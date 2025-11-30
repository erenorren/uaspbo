#!/bin/bash

echo "=== FOCUSED E-LEARNING API TEST ==="
echo ""

BASE_URL="http://localhost:8000/api"

echo "📚 COURSES ENDPOINTS TEST"
echo "=========================="

# 1. GET /courses - Get all courses
echo "1. GET /courses - Get all courses"
curl -X GET "$BASE_URL/courses" -H "Content-Type: application/json"
echo -e "\n---"

# 2. GET /courses with filter
echo "2. GET /courses?status=published - Get published courses"
curl -X GET "$BASE_URL/courses?status=published" -H "Content-Type: application/json"
echo -e "\n---"

# 3. POST /courses - Create new course
echo "3. POST /courses - Create new course"
curl -X POST "$BASE_URL/courses" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "JavaScript Fundamentals",
    "description": "Learn JavaScript from scratch",
    "max_students": 15,
    "instructor_id": 1
  }'
echo -e "\n---"

# 4. GET /courses/:id - Get course by ID
echo "4. GET /courses/1 - Get course by ID"
curl -X GET "$BASE_URL/courses/1" -H "Content-Type: application/json"
echo -e "\n---"

# 5. PUT /courses/:id - Update course
echo "5. PUT /courses/1 - Update course"
curl -X PUT "$BASE_URL/courses/1" \
  -H "Content-Type: application/json" \
  -d '{
    "title": "PHP OOP Fundamentals - UPDATED",
    "description": "Updated description with advanced topics",
    "max_students": 30
  }'
echo -e "\n---"

# 6. PUT /courses/:id/publish - Publish course
echo "6. PUT /courses/2/publish - Publish draft course"
curl -X PUT "$BASE_URL/courses/2/publish" -H "Content-Type: application/json"
echo -e "\n---"

# 7. PUT /courses/:id/unpublish - Unpublish course
echo "7. PUT /courses/1/unpublish - Unpublish course"
curl -X PUT "$BASE_URL/courses/1/unpublish" -H "Content-Type: application/json"
echo -e "\n---"

# 8. DELETE /courses/:id - Delete course
echo "8. DELETE /courses/4 - Delete course (if exists)"
curl -X DELETE "$BASE_URL/courses/4" -H "Content-Type: application/json"
echo -e "\n---"

echo ""
echo "🎓 STUDENTS ENDPOINTS TEST"
echo "==========================="

# 9. GET /students - Get all students
echo "9. GET /students - Get all students"
curl -X GET "$BASE_URL/students" -H "Content-Type: application/json"
echo -e "\n---"

# 10. POST /students - Create new student
echo "10. POST /students - Create new student"
UNIQUE_EMAIL="teststudent_$(date +%s)@email.com"
curl -X POST "$BASE_URL/students" \
  -H "Content-Type: application/json" \
  -d '{
    "email": "'$UNIQUE_EMAIL'",
    "name": "Test Student",
    "password": "password123"
  }'
echo -e "\n---"

# 11. GET /students/:id - Get student by ID
echo "11. GET /students/2 - Get student by ID"
curl -X GET "$BASE_URL/students/2" -H "Content-Type: application/json"
echo -e "\n---"

# 12. PUT /students/:id - Update student
echo "12. PUT /students/2 - Update student"
curl -X PUT "$BASE_URL/students/2" \
  -H "Content-Type: application/json" \
  -d '{
    "name": "Alice Student - UPDATED",
    "email": "alice.updated@email.com"
  }'
echo -e "\n---"

# 13. DELETE /students/:id - Delete student
echo "13. DELETE /students/4 - Delete student (newly created)"
curl -X DELETE "$BASE_URL/students/4" -H "Content-Type: application/json"
echo -e "\n---"

echo ""
echo "📝 ENROLLMENTS ENDPOINTS TEST"
echo "=============================="

# 14. POST /enrollments - Enroll student to course
echo "14. POST /enrollments - Enroll student to course"
curl -X POST "$BASE_URL/enrollments" \
  -H "Content-Type: application/json" \
  -d '{
    "student_id": 2,
    "course_id": 1
  }'
echo -e "\n---"

# 15. GET /students/:id/enrollments - Get student enrollments
echo "15. GET /students/2/enrollments - Get student enrollments"
curl -X GET "$BASE_URL/students/2/enrollments" -H "Content-Type: application/json"
echo -e "\n---"

# 16. PUT /enrollments/:id/complete - Complete enrollment
echo "16. PUT /enrollments/1/complete - Complete enrollment"
curl -X PUT "$BASE_URL/enrollments/1/complete" -H "Content-Type: application/json"
echo -e "\n---"

echo ""
echo "✅ ALL FOCUSED ENDPOINTS TESTED"