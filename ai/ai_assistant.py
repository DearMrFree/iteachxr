#!/usr/bin/env python3
# iTeachXR - AI Assistant
# Provides AI-powered assistance for users

import os
import sys
import json
import argparse

# Import AI integration library
sys.path.append(os.path.dirname(os.path.dirname(os.path.abspath(__file__))))
from lib.ai_integration import initialize_ai, process_ai_assistant_query

def parse_arguments():
    """Parse command line arguments"""
    parser = argparse.ArgumentParser(description="Process AI assistant queries")
    
    # We'll support both direct arguments and JSON input for flexibility
    parser.add_argument("--query", type=str, help="User query")
    parser.add_argument("--user_id", type=int, help="User ID")
    parser.add_argument("--context", type=str, help="Context (e.g., 'dashboard', 'course', 'assignment')")
    parser.add_argument("--context_id", type=int, help="Context ID (e.g., course ID, assignment ID)")
    parser.add_argument("--json_input", type=str, help="JSON input containing query and context")
    
    return parser.parse_args()

def main():
    """Main function to process AI assistant queries"""
    args = parse_arguments()
    
    # Initialize AI system
    if not initialize_ai():
        print(json.dumps({"error": "Failed to initialize AI system"}))
        sys.exit(1)
    
    try:
        # Determine if we're using JSON input or direct arguments
        if args.json_input or (not sys.stdin.isatty() and not args.query):
            # Read from provided JSON string or stdin
            if args.json_input:
                input_data = json.loads(args.json_input)
            else:
                input_data = json.load(sys.stdin)
            
            query = input_data.get("query")
            context = {
                "user_id": input_data.get("user_id"),
                "context": input_data.get("context"),
                "context_id": input_data.get("context_id")
            }
            
            # Add any additional context from input_data
            for key, value in input_data.items():
                if key not in ["query", "user_id", "context", "context_id"]:
                    context[key] = value
        else:
            # Use direct arguments
            query = args.query
            context = {
                "user_id": args.user_id,
                "context": args.context,
                "context_id": args.context_id
            }
        
        # Validate query
        if not query:
            print(json.dumps({"error": "No query provided"}))
            sys.exit(1)
        
        # Process the query
        response = process_ai_assistant_query(query, context)
        
        # Output the response
        result = {
            "response": response,
            "query": query
        }
        
        print(json.dumps(result))
            
    except Exception as e:
        print(json.dumps({"error": str(e)}))
        sys.exit(1)

if __name__ == "__main__":
    main()
