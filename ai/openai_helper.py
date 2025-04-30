#!/usr/bin/env python3
# iTeachXR - OpenAI Integration Helper

import os
import json
import sys
import openai
from openai import OpenAI

# Initialize OpenAI client
OPENAI_API_KEY = os.environ.get("OPENAI_API_KEY")
if not OPENAI_API_KEY:
    print(json.dumps({"error": "OpenAI API key not found in environment variables", "request_api_key": True}))
    sys.exit(1)

# In a production environment, we'd validate the API key
# For now, we'll just initialize the client
try:
    client = OpenAI(api_key=OPENAI_API_KEY)
except Exception as e:
    print(json.dumps({"error": f"Failed to initialize OpenAI client: {str(e)}", "request_api_key": True}))
    sys.exit(1)

def generate_course_structure(name, topic=None, level="undergraduate", duration="medium"):
    """
    Generate a recommended course structure using AI
    
    Args:
        name: Course name
        topic: Course topic or description (optional)
        level: Educational level
        duration: Course duration
        
    Returns:
        Dictionary with course structure recommendations
    """
    # Build the prompt
    prompt = f"""Generate a detailed course structure for a {level} course titled "{name}"""
    if topic:
        prompt += f" about {topic}"
    prompt += f""". The course duration is {duration}.
    
    Include the following in your response:
    1. 5-8 modules with titles and brief descriptions
    2. 3-5 learning objectives for the course
    3. 2-3 key assessments
    4. A brief paragraph describing how XR (VR/AR) could enhance this course
    
    Format your response as valid JSON with these keys:
    - modules: array of objects with title and description
    - objectives: array of learning objective strings
    - assessments: array of assessment objects with title and description
    - xr_enhancement: string describing XR enhancements
    """
    
    try:
        # Call the OpenAI API
        response = client.chat.completions.create(
            model="gpt-4o",  # the newest OpenAI model is "gpt-4o" which was released May 13, 2024
            messages=[
                {"role": "system", "content": "You are an expert curriculum designer for higher education."},
                {"role": "user", "content": prompt}
            ],
            response_format={"type": "json_object"}
        )
        
        # Parse the response
        result = json.loads(response.choices[0].message.content)
        return {"success": True, "data": result}
    except Exception as e:
        return {"success": False, "error": str(e)}

def generate_automated_feedback(submission_text, assignment_details):
    """
    Generate automated feedback for student submissions
    
    Args:
        submission_text: The text of the student's submission
        assignment_details: Dictionary containing assignment requirements
        
    Returns:
        Dictionary with feedback, strengths, weaknesses, and suggestions
    """
    # Build the prompt
    prompt = f"""As an educational assistant, provide detailed feedback on the following student submission:

ASSIGNMENT DETAILS:
Title: {assignment_details.get('title', 'Unknown Assignment')}
Description: {assignment_details.get('description', 'No description provided')}
Learning Objectives: {assignment_details.get('objectives', 'Not specified')}

STUDENT SUBMISSION:
{submission_text}

FORMAT YOUR RESPONSE AS JSON with these keys:
- overall_assessment: A paragraph summarizing the overall quality of the work (150-200 words)
- strengths: Array of 3-5 specific strengths in the submission
- areas_for_improvement: Array of 3-5 specific areas that need improvement
- suggestions: Array of 3-5 actionable suggestions for improvement
- estimated_grade: A letter grade (A, B, C, D, F) with a plus or minus if appropriate
"""
    
    try:
        # Call the OpenAI API
        response = client.chat.completions.create(
            model="gpt-4o",  # the newest OpenAI model is "gpt-4o" which was released May 13, 2024
            messages=[
                {"role": "system", "content": "You are an expert educational assistant providing helpful, constructive feedback."},
                {"role": "user", "content": prompt}
            ],
            response_format={"type": "json_object"}
        )
        
        # Parse the response
        result = json.loads(response.choices[0].message.content)
        return {"success": True, "data": result}
    except Exception as e:
        return {"success": False, "error": str(e)}

def generate_personalized_learning_path(student_profile, course_content):
    """
    Generate a personalized learning path for a student
    
    Args:
        student_profile: Dictionary containing student information
        course_content: Information about the course content and objectives
        
    Returns:
        Dictionary with personalized learning recommendations
    """
    # Build the prompt
    prompt = f"""Create a personalized learning path for a student with the following profile:

STUDENT PROFILE:
Learning Style: {student_profile.get('learning_style', 'Visual')}
Strengths: {student_profile.get('strengths', 'Not specified')}
Areas to Improve: {student_profile.get('areas_to_improve', 'Not specified')}
Prior Knowledge: {student_profile.get('prior_knowledge', 'Beginner')}
Goals: {student_profile.get('goals', 'Complete the course successfully')}

COURSE CONTENT:
Title: {course_content.get('title', 'Unknown Course')}
Description: {course_content.get('description', 'No description provided')}
Objectives: {course_content.get('objectives', 'Not specified')}
Available Modules: {course_content.get('modules', 'Not specified')}

FORMAT YOUR RESPONSE AS JSON with these keys:
- overview: A brief overview of the recommended approach
- recommended_sequence: Array of recommended module/activity IDs in optimal order for this student
- pace_recommendations: Suggestions for pacing (time to spend on each module)
- focus_areas: Array of specific topics or skills the student should focus on
- learning_strategies: Array of recommended learning strategies based on their profile
- additional_resources: Array of additional resource recommendations
"""
    
    try:
        # Call the OpenAI API
        response = client.chat.completions.create(
            model="gpt-4o",  # the newest OpenAI model is "gpt-4o" which was released May 13, 2024
            messages=[
                {"role": "system", "content": "You are an expert educational advisor creating personalized learning experiences."},
                {"role": "user", "content": prompt}
            ],
            response_format={"type": "json_object"}
        )
        
        # Parse the response
        result = json.loads(response.choices[0].message.content)
        return {"success": True, "data": result}
    except Exception as e:
        return {"success": False, "error": str(e)}

def process_ai_assistant_query(query, user_context=None):
    """
    Process a query to the AI teaching assistant
    
    Args:
        query: The user's query text
        user_context: Optional dictionary with context about the user and their current activity
        
    Returns:
        String with the AI assistant's response
    """
    # Build system message with context if available
    system_message = "You are iTeachXR AI Assistant, an educational AI specialized in XR-enhanced learning."
    
    if user_context:
        context_str = ""
        if user_context.get('role'):
            context_str += f"The user is a {user_context['role']}. "
        if user_context.get('course'):
            context_str += f"They are currently working with the course: {user_context['course']}. "
        if user_context.get('activity'):
            context_str += f"They are currently engaged with: {user_context['activity']}. "
        if user_context.get('recent_topics'):
            context_str += f"Recent topics they've been working on: {', '.join(user_context['recent_topics'])}. "
        
        system_message += f"\n\nUser Context: {context_str}"
    
    system_message += "\n\nProvide helpful, concise, and educational responses. Focus on XR-enhanced education when relevant."
    
    try:
        # Call the OpenAI API
        response = client.chat.completions.create(
            model="gpt-4o",  # the newest OpenAI model is "gpt-4o" which was released May 13, 2024
            messages=[
                {"role": "system", "content": system_message},
                {"role": "user", "content": query}
            ]
        )
        
        # Get the response text
        result = response.choices[0].message.content
        return {"success": True, "response": result}
    except Exception as e:
        return {"success": False, "error": str(e)}

# Main function to handle command line arguments
def main():
    if len(sys.argv) < 2:
        print(json.dumps({"error": "No function specified"}))
        return
    
    function = sys.argv[1]
    
    if function == "course_structure":
        if len(sys.argv) < 3:
            print(json.dumps({"error": "Course name required"}))
            return
        
        name = sys.argv[2]
        topic = sys.argv[3] if len(sys.argv) > 3 else None
        level = sys.argv[4] if len(sys.argv) > 4 else "undergraduate"
        duration = sys.argv[5] if len(sys.argv) > 5 else "medium"
        
        result = generate_course_structure(name, topic, level, duration)
        print(json.dumps(result))
    
    elif function == "feedback":
        if len(sys.argv) < 4:
            print(json.dumps({"error": "Submission text and assignment details required"}))
            return
        
        submission_text = sys.argv[2]
        assignment_details_json = sys.argv[3]
        
        try:
            assignment_details = json.loads(assignment_details_json)
        except json.JSONDecodeError:
            print(json.dumps({"error": "Invalid assignment details JSON"}))
            return
        
        result = generate_automated_feedback(submission_text, assignment_details)
        print(json.dumps(result))
    
    elif function == "learning_path":
        if len(sys.argv) < 4:
            print(json.dumps({"error": "Student profile and course content required"}))
            return
        
        student_profile_json = sys.argv[2]
        course_content_json = sys.argv[3]
        
        try:
            student_profile = json.loads(student_profile_json)
            course_content = json.loads(course_content_json)
        except json.JSONDecodeError:
            print(json.dumps({"error": "Invalid JSON data"}))
            return
        
        result = generate_personalized_learning_path(student_profile, course_content)
        print(json.dumps(result))
    
    elif function == "assistant":
        if len(sys.argv) < 3:
            print(json.dumps({"error": "Query required"}))
            return
        
        query = sys.argv[2]
        user_context_json = sys.argv[3] if len(sys.argv) > 3 else None
        
        user_context = None
        if user_context_json:
            try:
                user_context = json.loads(user_context_json)
            except json.JSONDecodeError:
                print(json.dumps({"error": "Invalid user context JSON"}))
                return
        
        result = process_ai_assistant_query(query, user_context)
        print(json.dumps(result))
    
    else:
        print(json.dumps({"error": f"Unknown function: {function}"}))

if __name__ == "__main__":
    main()