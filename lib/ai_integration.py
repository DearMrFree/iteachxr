#!/usr/bin/env python3
# iTeachXR - AI Integration Library
# Provides common functions for AI features

import os
import json
import sys
import base64
from openai import OpenAI

# the newest OpenAI model is "gpt-4o" which was released May 13, 2024.
# do not change this unless explicitly requested by the user

# Get the API key from environment variables
OPENAI_API_KEY = os.environ.get("OPENAI_API_KEY", "")

# Initialize OpenAI client
openai = OpenAI(api_key=OPENAI_API_KEY)

def initialize_ai():
    """Initialize the AI system and verify API key"""
    if not OPENAI_API_KEY:
        print("Warning: OPENAI_API_KEY environment variable not set.")
        return False
    
    try:
        # Test the API key with a simple request
        response = openai.chat.completions.create(
            model="gpt-4o",
            messages=[
                {"role": "system", "content": "This is a test request to verify API key."},
                {"role": "user", "content": "Hello"}
            ],
            max_tokens=5
        )
        return True
    except Exception as e:
        print(f"Error initializing OpenAI: {e}")
        return False

def get_content_recommendation(user_data, course_data):
    """
    Generate content recommendations based on user data and course data
    
    Args:
        user_data: Dictionary containing user information and learning history
        course_data: Dictionary containing course content and structure
        
    Returns:
        Dictionary with recommended content
    """
    try:
        # Prepare the prompt for content recommendation
        prompt = f"""
        Based on the following user profile and course data, provide personalized content recommendations:
        
        USER PROFILE:
        - Learning history: {user_data.get('learning_history', [])}
        - Performance areas: {user_data.get('performance', {})}
        - Learning style: {user_data.get('learning_style', 'Not specified')}
        
        COURSE DATA:
        - Topics: {course_data.get('topics', [])}
        - Available resources: {course_data.get('resources', [])}
        
        Provide 3-5 specific resource recommendations with brief explanations why they would be beneficial.
        Format your response as a JSON object with the following structure:
        {{
            "recommendations": [
                {{
                    "resource_id": "ID of the resource",
                    "title": "Title of the resource",
                    "reason": "Brief explanation of why this resource is recommended",
                    "priority": "high/medium/low"
                }}
            ]
        }}
        """
        
        response = openai.chat.completions.create(
            model="gpt-4o",
            messages=[
                {"role": "system", "content": "You are an educational content recommendation system."},
                {"role": "user", "content": prompt}
            ],
            response_format={"type": "json_object"},
            max_tokens=1000
        )
        
        return json.loads(response.choices[0].message.content)
    except Exception as e:
        print(f"Error generating content recommendations: {e}")
        return {"recommendations": [], "error": str(e)}

def generate_automated_feedback(submission_text, assignment_data):
    """
    Generate automated feedback for student submissions
    
    Args:
        submission_text: The text of the student's submission
        assignment_data: Dictionary containing assignment requirements and rubric
        
    Returns:
        Dictionary with feedback, strengths, weaknesses, and suggestions
    """
    try:
        # Prepare the prompt for automated feedback
        prompt = f"""
        Evaluate the following student submission based on the assignment requirements and rubric:
        
        ASSIGNMENT REQUIREMENTS:
        {assignment_data.get('requirements', 'Not specified')}
        
        RUBRIC:
        {assignment_data.get('rubric', 'Not specified')}
        
        STUDENT SUBMISSION:
        {submission_text}
        
        Provide constructive feedback including strengths, areas for improvement, and specific suggestions.
        Format your response as a JSON object with the following structure:
        {{
            "overall_assessment": "Brief overall assessment",
            "grade_suggestion": "Suggested grade or score based on the rubric",
            "strengths": ["Strength 1", "Strength 2", ...],
            "areas_for_improvement": ["Area 1", "Area 2", ...],
            "specific_suggestions": ["Suggestion 1", "Suggestion 2", ...],
            "detailed_feedback": "Detailed paragraph of feedback"
        }}
        """
        
        response = openai.chat.completions.create(
            model="gpt-4o",
            messages=[
                {"role": "system", "content": "You are an educational assessment assistant providing constructive feedback on student work."},
                {"role": "user", "content": prompt}
            ],
            response_format={"type": "json_object"},
            max_tokens=1500
        )
        
        return json.loads(response.choices[0].message.content)
    except Exception as e:
        print(f"Error generating automated feedback: {e}")
        return {
            "overall_assessment": f"Error generating feedback: {e}",
            "strengths": [],
            "areas_for_improvement": [],
            "specific_suggestions": [],
            "detailed_feedback": "An error occurred while generating feedback."
        }

def detect_plagiarism(submission_text, reference_texts=None):
    """
    Detect potential plagiarism in student submissions
    
    Args:
        submission_text: The text of the student's submission
        reference_texts: Optional list of reference texts to compare against
        
    Returns:
        Dictionary with plagiarism assessment
    """
    try:
        # Prepare the prompt for plagiarism detection
        prompt = f"""
        Analyze the following student submission for potential plagiarism indicators:
        
        STUDENT SUBMISSION:
        {submission_text}
        """
        
        if reference_texts:
            prompt += "\n\nREFERENCE TEXTS:\n"
            for i, text in enumerate(reference_texts, 1):
                prompt += f"Reference {i}: {text}\n\n"
        
        prompt += """
        Analyze the text for:
        1. Unusual vocabulary or transitions between different writing styles
        2. Formal or academic language not consistent with student level
        3. Outdated information or references
        4. Irrelevant information or tangents
        5. If reference texts are provided, check for similarity with references
        
        Format your response as a JSON object with the following structure:
        {
            "plagiarism_likelihood": "low/medium/high",
            "confidence_score": 0.0-1.0,
            "indicators": ["Indicator 1", "Indicator 2", ...],
            "recommendations": ["Recommendation 1", "Recommendation 2", ...],
            "explanation": "Detailed explanation of the assessment"
        }
        """
        
        response = openai.chat.completions.create(
            model="gpt-4o",
            messages=[
                {"role": "system", "content": "You are a plagiarism detection system. Your task is to analyze student submissions for potential plagiarism indicators."},
                {"role": "user", "content": prompt}
            ],
            response_format={"type": "json_object"},
            max_tokens=1000
        )
        
        return json.loads(response.choices[0].message.content)
    except Exception as e:
        print(f"Error in plagiarism detection: {e}")
        return {
            "plagiarism_likelihood": "unknown",
            "confidence_score": 0,
            "indicators": [],
            "recommendations": ["Run the analysis again."],
            "explanation": f"Error in plagiarism detection: {e}"
        }

def generate_quiz_questions(course_content, difficulty_level, num_questions=5):
    """
    Generate quiz questions based on course content
    
    Args:
        course_content: Text describing the course content
        difficulty_level: String indicating difficulty (easy, medium, hard)
        num_questions: Number of questions to generate
        
    Returns:
        Dictionary with generated quiz questions
    """
    try:
        # Prepare the prompt for quiz generation
        prompt = f"""
        Generate {num_questions} quiz questions based on the following course content:
        
        COURSE CONTENT:
        {course_content}
        
        DIFFICULTY LEVEL: {difficulty_level}
        
        Create questions of various types (multiple choice, true/false, short answer).
        For multiple choice questions, include 4 options and indicate the correct answer.
        
        Format your response as a JSON object with the following structure:
        {{
            "questions": [
                {{
                    "question_type": "multiple_choice/true_false/short_answer",
                    "question_text": "The question text",
                    "options": ["Option A", "Option B", ...] (for multiple choice only),
                    "correct_answer": "The correct answer",
                    "explanation": "Explanation of why this is the correct answer"
                }}
            ]
        }}
        """
        
        response = openai.chat.completions.create(
            model="gpt-4o",
            messages=[
                {"role": "system", "content": "You are an educational quiz generator creating assessment questions based on course content."},
                {"role": "user", "content": prompt}
            ],
            response_format={"type": "json_object"},
            max_tokens=2000
        )
        
        return json.loads(response.choices[0].message.content)
    except Exception as e:
        print(f"Error generating quiz questions: {e}")
        return {"questions": [], "error": str(e)}

def generate_personalized_learning_path(student_profile, course_objectives):
    """
    Generate a personalized learning path for a student
    
    Args:
        student_profile: Dictionary containing student information
        course_objectives: List of course learning objectives
        
    Returns:
        Dictionary with personalized learning path
    """
    try:
        # Prepare the prompt for personalized learning path
        prompt = f"""
        Create a personalized learning path based on the following student profile and course objectives:
        
        STUDENT PROFILE:
        - Prior knowledge: {student_profile.get('prior_knowledge', 'Not specified')}
        - Learning style: {student_profile.get('learning_style', 'Not specified')}
        - Strengths: {student_profile.get('strengths', [])}
        - Areas for improvement: {student_profile.get('areas_for_improvement', [])}
        - Goals: {student_profile.get('goals', 'Not specified')}
        
        COURSE OBJECTIVES:
        {course_objectives}
        
        Create a structured learning path that addresses the student's needs and helps them achieve the course objectives.
        
        Format your response as a JSON object with the following structure:
        {{
            "learning_path": [
                {{
                    "stage": "Stage name/number",
                    "objectives": ["Objective 1", "Objective 2", ...],
                    "activities": ["Activity 1", "Activity 2", ...],
                    "resources": ["Resource 1", "Resource 2", ...],
                    "assessment": "Description of assessment for this stage",
                    "estimated_duration": "Estimated time to complete this stage"
                }}
            ],
            "recommendations": "Overall recommendations for the student"
        }}
        """
        
        response = openai.chat.completions.create(
            model="gpt-4o",
            messages=[
                {"role": "system", "content": "You are an educational learning path designer specializing in personalized learning."},
                {"role": "user", "content": prompt}
            ],
            response_format={"type": "json_object"},
            max_tokens=2000
        )
        
        return json.loads(response.choices[0].message.content)
    except Exception as e:
        print(f"Error generating personalized learning path: {e}")
        return {"learning_path": [], "recommendations": f"Error generating learning path: {e}"}

def process_ai_assistant_query(query, user_context=None):
    """
    Process a query to the AI assistant
    
    Args:
        query: The user's query text
        user_context: Optional dictionary with context about the user and their current activity
        
    Returns:
        String with the AI assistant's response
    """
    try:
        # Prepare system message based on context
        system_message = """You are an educational AI assistant for the iTeachXR learning platform. 
        You help users with their questions about courses, assignments, and learning. 
        Keep responses helpful, concise, and encouraging."""
        
        if user_context:
            context_str = "\n\nUser context:\n"
            for key, value in user_context.items():
                context_str += f"- {key}: {value}\n"
            system_message += context_str
        
        response = openai.chat.completions.create(
            model="gpt-4o",
            messages=[
                {"role": "system", "content": system_message},
                {"role": "user", "content": query}
            ],
            max_tokens=800
        )
        
        return response.choices[0].message.content
    except Exception as e:
        print(f"Error processing AI assistant query: {e}")
        return f"I'm sorry, I encountered an error while processing your request. Please try again later. Error details: {e}"

# Command-line interface for testing
if __name__ == "__main__":
    if len(sys.argv) > 1:
        command = sys.argv[1]
        
        if command == "initialize":
            success = initialize_ai()
            print(json.dumps({"success": success}))
        
        elif command == "assistant":
            if len(sys.argv) > 2:
                query = sys.argv[2]
                context = json.loads(sys.argv[3]) if len(sys.argv) > 3 else None
                response = process_ai_assistant_query(query, context)
                print(json.dumps({"response": response}))
            else:
                print(json.dumps({"error": "No query provided"}))
        
        elif command == "recommendations":
            if len(sys.argv) > 3:
                user_data = json.loads(sys.argv[2])
                course_data = json.loads(sys.argv[3])
                recommendations = get_content_recommendation(user_data, course_data)
                print(json.dumps(recommendations))
            else:
                print(json.dumps({"error": "Missing user or course data"}))
        
        elif command == "feedback":
            if len(sys.argv) > 3:
                submission_text = sys.argv[2]
                assignment_data = json.loads(sys.argv[3])
                feedback = generate_automated_feedback(submission_text, assignment_data)
                print(json.dumps(feedback))
            else:
                print(json.dumps({"error": "Missing submission or assignment data"}))
        
        else:
            print(json.dumps({"error": f"Unknown command: {command}"}))
    else:
        print(json.dumps({"error": "No command provided"}))
