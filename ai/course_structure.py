#!/usr/bin/env python3
# iTeachXR - Course Structure Generator
# Generate course structure recommendations

import os
import sys
import json
import argparse
from pathlib import Path

# Import AI integration library
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from lib.ai_integration import initialize_ai, OpenAI

def parse_arguments():
    """Parse command line arguments"""
    parser = argparse.ArgumentParser(description="Generate course structure recommendations")
    parser.add_argument("--courseid", type=int, required=True, help="Course ID")
    parser.add_argument("--name", type=str, required=True, help="Course name")
    parser.add_argument("--topic", type=str, help="Course topic or description")
    parser.add_argument("--level", type=str, choices=["elementary", "secondary", "undergraduate", "graduate", "professional"], 
                      default="undergraduate", help="Educational level")
    parser.add_argument("--duration", type=str, choices=["short", "medium", "semester", "long"], 
                      default="medium", help="Course duration")
    parser.add_argument("--output", type=str, help="Output file path (optional)")
    return parser.parse_args()

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
    # Initialize AI
    if not initialize_ai():
        return {"error": "Failed to initialize AI system"}
    
    # the newest OpenAI model is "gpt-4o" which was released May 13, 2024.
    # do not change this unless explicitly requested by the user
    
    # Use the course name as the topic if no topic is provided
    if not topic:
        topic = name
    
    # Prepare prompt
    duration_weeks = {
        "short": "4 weeks",
        "medium": "8 weeks",
        "semester": "15 weeks",
        "long": "20+ weeks"
    }
    
    prompt = f"""
    Generate a comprehensive course structure for a {level} level course titled "{name}" on the topic of {topic}.
    The course duration is approximately {duration_weeks.get(duration, "8 weeks")}.
    
    Please include:
    1. A list of 5-10 key learning objectives
    2. A weekly outline with topics, subtopics, and suggested activities
    3. Recommended assessment strategies
    4. Key resources that should be included
    
    Format your response as a JSON object with the following structure:
    {{
        "title": "Course title",
        "description": "Brief course description",
        "learning_objectives": ["objective 1", "objective 2", ...],
        "weekly_outline": [
            {{
                "week": 1,
                "title": "Week title",
                "topics": ["topic 1", "topic 2", ...],
                "activities": ["activity 1", "activity 2", ...],
                "resources": ["resource 1", "resource 2", ...]
            }},
            ...
        ],
        "assessments": [
            {{
                "type": "assessment type",
                "description": "assessment description",
                "weight": "percentage of final grade"
            }},
            ...
        ]
    }}
    """
    
    # Get AI response
    try:
        openai = OpenAI(api_key=os.environ.get("OPENAI_API_KEY", ""))
        response = openai.chat.completions.create(
            model="gpt-4o",
            messages=[
                {"role": "system", "content": "You are an expert curriculum designer who specializes in creating well-structured educational courses."},
                {"role": "user", "content": prompt}
            ],
            response_format={"type": "json_object"},
            max_tokens=3000
        )
        
        # Parse the JSON response
        structure = json.loads(response.choices[0].message.content)
        
        # Add metadata
        structure["course_id"] = None  # Will be set by the caller
        structure["level"] = level
        structure["duration"] = duration
        
        # Create HTML representations for easier display
        structure["outline_html"] = create_outline_html(structure)
        structure["objectives_html"] = create_objectives_html(structure)
        
        return structure
    
    except Exception as e:
        return {"error": str(e)}

def create_outline_html(structure):
    """Create HTML representation of the course outline"""
    html = "<div class='course-outline'>"
    
    # Weekly outline
    html += "<h4>Weekly Schedule</h4>"
    html += "<div class='accordion' id='weeklyOutline'>"
    
    for i, week in enumerate(structure.get("weekly_outline", [])):
        week_num = week.get("week", i+1)
        
        html += f"""
        <div class="accordion-item">
            <h2 class="accordion-header" id="week{week_num}Header">
                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" 
                        data-bs-target="#week{week_num}Content" aria-expanded="false" aria-controls="week{week_num}Content">
                    Week {week_num}: {week.get('title', 'Weekly Topics')}
                </button>
            </h2>
            <div id="week{week_num}Content" class="accordion-collapse collapse" aria-labelledby="week{week_num}Header" 
                 data-bs-parent="#weeklyOutline">
                <div class="accordion-body">
                    <h5>Topics</h5>
                    <ul>
        """
        
        # Topics
        for topic in week.get("topics", []):
            html += f"<li>{topic}</li>"
        
        html += "</ul>"
        
        # Activities
        if week.get("activities"):
            html += "<h5>Activities</h5><ul>"
            for activity in week.get("activities", []):
                html += f"<li>{activity}</li>"
            html += "</ul>"
        
        # Resources
        if week.get("resources"):
            html += "<h5>Resources</h5><ul>"
            for resource in week.get("resources", []):
                html += f"<li>{resource}</li>"
            html += "</ul>"
        
        html += """
                </div>
            </div>
        </div>
        """
    
    html += "</div>"  # End accordion
    
    # Assessments
    if structure.get("assessments"):
        html += "<h4 class='mt-4'>Assessment Strategy</h4>"
        html += "<table class='table table-bordered'>"
        html += "<thead><tr><th>Assessment</th><th>Description</th><th>Weight</th></tr></thead>"
        html += "<tbody>"
        
        for assessment in structure.get("assessments", []):
            html += f"""
            <tr>
                <td>{assessment.get('type', 'Assessment')}</td>
                <td>{assessment.get('description', '')}</td>
                <td>{assessment.get('weight', '')}</td>
            </tr>
            """
        
        html += "</tbody></table>"
    
    html += "</div>"  # End course-outline
    return html

def create_objectives_html(structure):
    """Create HTML representation of the learning objectives"""
    html = "<div class='learning-objectives'>"
    html += "<ul class='list-group list-group-flush'>"
    
    for objective in structure.get("learning_objectives", []):
        html += f"<li class='list-group-item'>{objective}</li>"
    
    html += "</ul></div>"
    return html

def main():
    """Main function to generate course structure"""
    args = parse_arguments()
    
    try:
        # Generate course structure
        structure = generate_course_structure(
            name=args.name,
            topic=args.topic,
            level=args.level,
            duration=args.duration
        )
        
        # Add course ID
        structure["course_id"] = args.courseid
        
        # Save to file
        output_dir = Path("moodledata/ai/course_structures")
        output_dir.mkdir(parents=True, exist_ok=True)
        
        output_file = args.output if args.output else output_dir / f"course_{args.courseid}.json"
        
        with open(output_file, 'w') as f:
            json.dump(structure, f, indent=2)
        
        # Print success message
        print(json.dumps({"success": True, "message": "Course structure generated successfully"}))
            
    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()
