<?php
// iTeachXR - AI Demo Page
$pageTitle = "AI Features Demo - iTeachXR";
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
    <style>
        body {
            padding-top: 20px;
            padding-bottom: 50px;
        }
        .feature-card {
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            height: 100%;
            transition: transform 0.3s ease;
            padding: 20px;
            margin-bottom: 20px;
        }
        .feature-card:hover {
            transform: translateY(-5px);
        }
        .feature-icon {
            font-size: 2rem;
            margin-bottom: 1rem;
            color: #4b6cb7;
        }
        .result-area {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            min-height: 200px;
            margin-top: 20px;
            background-color: #f8f9fa;
            overflow-y: auto;
            white-space: pre-wrap;
        }
        .loading {
            text-align: center;
            padding: 30px;
        }
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <header class="mb-5">
            <div class="row">
                <div class="col-md-8">
                    <h1>iTeachXR AI Features Demo</h1>
                    <p class="lead">Test and explore the AI capabilities of the iTeachXR platform</p>
                </div>
                <div class="col-md-4 text-end">
                    <a href="index.php" class="btn btn-outline-primary">
                        <i class="fa fa-home"></i> Back to Home
                    </a>
                </div>
            </div>
            <hr>
        </header>
        
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa fa-graduation-cap"></i>
                    </div>
                    <h3>Course Structure Generation</h3>
                    <p>Use AI to generate a complete course structure with modules, objectives, and assessments.</p>
                    <button id="show-course-generator" class="btn btn-primary">Try It</button>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa fa-comments"></i>
                    </div>
                    <h3>Automated Feedback</h3>
                    <p>Generate detailed feedback for student submissions using AI.</p>
                    <button id="show-feedback-generator" class="btn btn-success">Try It</button>
                </div>
            </div>
            
            <div class="col-md-4">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fa fa-robot"></i>
                    </div>
                    <h3>AI Teaching Assistant</h3>
                    <p>Interact with the AI teaching assistant for help with any educational topic.</p>
                    <button id="show-ai-assistant" class="btn btn-info">Try It</button>
                </div>
            </div>
        </div>
        
        <!-- Course Structure Generator -->
        <div id="course-generator" class="row mt-5 d-none">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3 class="mb-0">Course Structure Generator</h3>
                    </div>
                    <div class="card-body">
                        <form id="course-structure-form">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="course-name" class="form-label">Course Name</label>
                                    <input type="text" class="form-control" id="course-name" required 
                                        placeholder="e.g., Introduction to Augmented Reality">
                                </div>
                                <div class="col-md-6">
                                    <label for="course-topic" class="form-label">Course Topic (Optional)</label>
                                    <input type="text" class="form-control" id="course-topic" 
                                        placeholder="e.g., AR markers and spatial mapping">
                                </div>
                            </div>
                            
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="course-level" class="form-label">Educational Level</label>
                                    <select class="form-select" id="course-level">
                                        <option value="elementary">Elementary School</option>
                                        <option value="middle">Middle School</option>
                                        <option value="high_school">High School</option>
                                        <option value="undergraduate">Undergraduate</option>
                                        <option value="graduate">Graduate</option>
                                        <option value="professional">Professional Development</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="course-duration" class="form-label">Course Duration</label>
                                    <select class="form-select" id="course-duration">
                                        <option value="short">Short (1-4 weeks)</option>
                                        <option value="medium" selected>Medium (5-10 weeks)</option>
                                        <option value="long">Long (11-16 weeks)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fa fa-magic me-2"></i> Generate Course Structure
                                </button>
                            </div>
                        </form>
                        
                        <div id="course-structure-result" class="result-area d-none">
                            <div id="course-structure-loading" class="loading d-none">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3">Generating course structure...</p>
                            </div>
                            <div id="course-structure-content"></div>
                            <div id="api-key-instructions" class="d-none mt-4">
                                <div class="alert alert-info">
                                    <h5>Need an OpenAI API key?</h5>
                                    <p>To use AI features in iTeachXR, you'll need an OpenAI API key with available quota.</p>
                                    <ol>
                                        <li>Sign up at <a href="https://platform.openai.com/signup" target="_blank">OpenAI Platform</a></li>
                                        <li>Visit the <a href="https://platform.openai.com/api-keys" target="_blank">API Keys page</a></li>
                                        <li>Create a new secret key</li>
                                        <li>Copy the API key (it starts with "sk-")</li>
                                        <li>Provide it to your iTeachXR administrator</li>
                                    </ol>
                                    <p>Once the API key is added to the system, all AI features will be available.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Automated Feedback Generator -->
        <div id="feedback-generator" class="row mt-5 d-none">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-success text-white">
                        <h3 class="mb-0">Automated Feedback Generator</h3>
                    </div>
                    <div class="card-body">
                        <form id="feedback-form">
                            <div class="mb-3">
                                <label for="assignment-details" class="form-label">Assignment Details</label>
                                <textarea class="form-control" id="assignment-details" rows="4" required
                                    placeholder="Enter the assignment title, description, and learning objectives."></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="submission-text" class="form-label">Student Submission</label>
                                <textarea class="form-control" id="submission-text" rows="8" required
                                    placeholder="Enter the content of the student's submission to receive feedback."></textarea>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-success">
                                    <i class="fa fa-check-circle me-2"></i> Generate Feedback
                                </button>
                            </div>
                        </form>
                        
                        <div id="feedback-result" class="result-area d-none">
                            <div id="feedback-loading" class="loading d-none">
                                <div class="spinner-border text-success" role="status">
                                    <span class="visually-hidden">Loading...</span>
                                </div>
                                <p class="mt-3">Generating feedback...</p>
                            </div>
                            <div id="feedback-content"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- AI Teaching Assistant -->
        <div id="ai-assistant" class="row mt-5 d-none">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-info text-white">
                        <h3 class="mb-0">AI Teaching Assistant</h3>
                    </div>
                    <div class="card-body">
                        <div class="chat-container" style="height: 400px; overflow-y: auto; border: 1px solid #ddd; border-radius: 5px; padding: 15px; margin-bottom: 20px;">
                            <div id="chat-messages">
                                <div class="card mb-3 border-info">
                                    <div class="card-body">
                                        <div class="d-flex">
                                            <div class="me-3">
                                                <i class="fa fa-robot text-info fs-3"></i>
                                            </div>
                                            <div>
                                                <h5 class="card-title">AI Teaching Assistant</h5>
                                                <p class="card-text">
                                                    Hello! I'm the iTeachXR AI Teaching Assistant. I can help you with:
                                                </p>
                                                <ul>
                                                    <li>Questions about XR technology and education</li>
                                                    <li>Content creation for courses</li>
                                                    <li>Teaching strategies for XR</li>
                                                    <li>Technical advice on AR/VR implementation</li>
                                                </ul>
                                                <p class="card-text">How can I assist you today?</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <form id="assistant-form" class="d-flex">
                            <input type="text" class="form-control me-2" id="assistant-query" 
                                placeholder="Ask a question or request help...">
                            <button type="submit" class="btn btn-info">
                                <i class="fa fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Section visibility toggles
        document.getElementById('show-course-generator').addEventListener('click', function() {
            hideAllSections();
            document.getElementById('course-generator').classList.remove('d-none');
        });
        
        document.getElementById('show-feedback-generator').addEventListener('click', function() {
            hideAllSections();
            document.getElementById('feedback-generator').classList.remove('d-none');
        });
        
        document.getElementById('show-ai-assistant').addEventListener('click', function() {
            hideAllSections();
            document.getElementById('ai-assistant').classList.remove('d-none');
        });
        
        // Course structure form submit
        document.getElementById('course-structure-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const name = document.getElementById('course-name').value;
            const topic = document.getElementById('course-topic').value;
            const level = document.getElementById('course-level').value;
            const duration = document.getElementById('course-duration').value;
            
            const resultArea = document.getElementById('course-structure-result');
            const loadingElement = document.getElementById('course-structure-loading');
            const contentElement = document.getElementById('course-structure-content');
            
            resultArea.classList.remove('d-none');
            loadingElement.classList.remove('d-none');
            contentElement.innerHTML = '';
            
            // Prepare form data
            const formData = new FormData();
            formData.append('name', name);
            if (topic) formData.append('topic', topic);
            formData.append('level', level);
            formData.append('duration', duration);
            
            // Call the API
            fetch('api/ai_service.php?action=course_structure', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loadingElement.classList.add('d-none');
                
                // Check if there's an API key error
                if (data.api_key_error) {
                    contentElement.innerHTML = `
                        <div class="alert alert-warning">
                            <h4 class="alert-heading">OpenAI API Key Required</h4>
                            <p>${data.message || 'A valid OpenAI API key is required to use this feature.'}</p>
                            <hr>
                            <p class="mb-0">Please contact your system administrator to provide a valid API key with sufficient quota.</p>
                        </div>
                    `;
                    return;
                }
                
                if (data.error) {
                    contentElement.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                    return;
                }
                
                if (data.success && data.data) {
                    const result = data.data;
                    
                    let html = `<h4>Course Structure for: ${name}</h4>`;
                    
                    // Learning Objectives
                    if (result.objectives && result.objectives.length > 0) {
                        html += `<h5 class="mt-4">Learning Objectives</h5>
                                <ul>`;
                        result.objectives.forEach(objective => {
                            html += `<li>${objective}</li>`;
                        });
                        html += `</ul>`;
                    }
                    
                    // Modules
                    if (result.modules && result.modules.length > 0) {
                        html += `<h5 class="mt-4">Modules</h5>`;
                        result.modules.forEach((module, index) => {
                            html += `
                                <div class="card mt-2">
                                    <div class="card-header">
                                        <h6 class="mb-0">Module ${index + 1}: ${module.title}</h6>
                                    </div>
                                    <div class="card-body">
                                        <p>${module.description}</p>
                                    </div>
                                </div>
                            `;
                        });
                    }
                    
                    // Assessments
                    if (result.assessments && result.assessments.length > 0) {
                        html += `<h5 class="mt-4">Assessments</h5>
                                <ul class="list-group">`;
                        result.assessments.forEach(assessment => {
                            html += `<li class="list-group-item">
                                <h6>${assessment.title}</h6>
                                <p>${assessment.description}</p>
                            </li>`;
                        });
                        html += `</ul>`;
                    }
                    
                    // XR Enhancement
                    if (result.xr_enhancement) {
                        html += `
                            <div class="card mt-4 bg-light">
                                <div class="card-header">
                                    <h5 class="mb-0">XR Enhancement Opportunities</h5>
                                </div>
                                <div class="card-body">
                                    <p>${result.xr_enhancement}</p>
                                </div>
                            </div>
                        `;
                    }
                    
                    contentElement.innerHTML = html;
                } else {
                    contentElement.innerHTML = `<div class="alert alert-warning">Failed to generate course structure. Please try again.</div>`;
                }
            })
            .catch(error => {
                loadingElement.classList.add('d-none');
                contentElement.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            });
        });
        
        // Feedback form submit
        document.getElementById('feedback-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const assignmentDetails = document.getElementById('assignment-details').value;
            const submissionText = document.getElementById('submission-text').value;
            
            const resultArea = document.getElementById('feedback-result');
            const loadingElement = document.getElementById('feedback-loading');
            const contentElement = document.getElementById('feedback-content');
            
            resultArea.classList.remove('d-none');
            loadingElement.classList.remove('d-none');
            contentElement.innerHTML = '';
            
            // Create assignment details object
            const assignmentObj = {
                title: "Assignment",
                description: assignmentDetails,
                objectives: "Provide quality work that demonstrates understanding of concepts"
            };
            
            // Prepare form data
            const formData = new FormData();
            formData.append('submission_text', submissionText);
            formData.append('assignment_details', JSON.stringify(assignmentObj));
            
            // Call the API
            fetch('api/ai_service.php?action=feedback', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                loadingElement.classList.add('d-none');
                
                if (data.error) {
                    contentElement.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
                    return;
                }
                
                if (data.success && data.data) {
                    const result = data.data;
                    
                    let html = `<h4>Feedback Summary</h4>`;
                    
                    // Overall assessment
                    if (result.overall_assessment) {
                        html += `
                            <div class="card mt-3">
                                <div class="card-header">
                                    <h5 class="mb-0">Overall Assessment</h5>
                                </div>
                                <div class="card-body">
                                    <p>${result.overall_assessment}</p>
                                </div>
                            </div>
                        `;
                    }
                    
                    // Estimated grade
                    if (result.estimated_grade) {
                        html += `
                            <div class="alert alert-info mt-3">
                                <strong>Estimated Grade:</strong> ${result.estimated_grade}
                            </div>
                        `;
                    }
                    
                    // Strengths
                    if (result.strengths && result.strengths.length > 0) {
                        html += `
                            <div class="card mt-3 border-success">
                                <div class="card-header bg-success text-white">
                                    <h5 class="mb-0">Strengths</h5>
                                </div>
                                <div class="card-body">
                                    <ul>`;
                        result.strengths.forEach(strength => {
                            html += `<li>${strength}</li>`;
                        });
                        html += `</ul>
                                </div>
                            </div>
                        `;
                    }
                    
                    // Areas for improvement
                    if (result.areas_for_improvement && result.areas_for_improvement.length > 0) {
                        html += `
                            <div class="card mt-3 border-warning">
                                <div class="card-header bg-warning">
                                    <h5 class="mb-0">Areas for Improvement</h5>
                                </div>
                                <div class="card-body">
                                    <ul>`;
                        result.areas_for_improvement.forEach(area => {
                            html += `<li>${area}</li>`;
                        });
                        html += `</ul>
                                </div>
                            </div>
                        `;
                    }
                    
                    // Suggestions
                    if (result.suggestions && result.suggestions.length > 0) {
                        html += `
                            <div class="card mt-3 border-info">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">Suggestions for Improvement</h5>
                                </div>
                                <div class="card-body">
                                    <ul>`;
                        result.suggestions.forEach(suggestion => {
                            html += `<li>${suggestion}</li>`;
                        });
                        html += `</ul>
                                </div>
                            </div>
                        `;
                    }
                    
                    contentElement.innerHTML = html;
                } else {
                    contentElement.innerHTML = `<div class="alert alert-warning">Failed to generate feedback. Please try again.</div>`;
                }
            })
            .catch(error => {
                loadingElement.classList.add('d-none');
                contentElement.innerHTML = `<div class="alert alert-danger">Error: ${error.message}</div>`;
            });
        });
        
        // AI Assistant form submit
        document.getElementById('assistant-form').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const query = document.getElementById('assistant-query').value;
            if (!query.trim()) return;
            
            const chatContainer = document.getElementById('chat-messages');
            
            // Add user message to chat
            chatContainer.innerHTML += `
                <div class="card mb-3 border-primary text-end">
                    <div class="card-body">
                        <div class="d-flex justify-content-end">
                            <div class="me-3 text-end">
                                <h5 class="card-title">You</h5>
                                <p class="card-text">${query}</p>
                            </div>
                            <div>
                                <i class="fa fa-user text-primary fs-3"></i>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Add loading message
            const loadingId = 'loading-' + Date.now();
            chatContainer.innerHTML += `
                <div id="${loadingId}" class="card mb-3 border-info">
                    <div class="card-body">
                        <div class="d-flex">
                            <div class="me-3">
                                <i class="fa fa-robot text-info fs-3"></i>
                            </div>
                            <div>
                                <h5 class="card-title">AI Teaching Assistant</h5>
                                <p class="card-text">
                                    <div class="spinner-border spinner-border-sm text-info" role="status">
                                        <span class="visually-hidden">Loading...</span>
                                    </div>
                                    Thinking...
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Scroll to bottom
            chatContainer.scrollTop = chatContainer.scrollHeight;
            
            // Clear input
            document.getElementById('assistant-query').value = '';
            
            // Prepare user context
            const userContext = {
                role: 'user',
                recent_topics: ['XR Education', 'Teaching with VR/AR', 'Immersive Learning']
            };
            
            // Prepare form data
            const formData = new FormData();
            formData.append('query', query);
            formData.append('user_context', JSON.stringify(userContext));
            
            // Call the API
            fetch('api/ai_service.php?action=assistant', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                // Remove loading message
                document.getElementById(loadingId).remove();
                
                let responseText = 'I apologize, but I couldn\'t process your request at this time.';
                
                if (data.success && data.response) {
                    responseText = data.response;
                } else if (data.error) {
                    responseText = `Error: ${data.error}`;
                }
                
                // Add AI response to chat
                chatContainer.innerHTML += `
                    <div class="card mb-3 border-info">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fa fa-robot text-info fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="card-title">AI Teaching Assistant</h5>
                                    <p class="card-text">${formatResponse(responseText)}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Scroll to bottom
                chatContainer.scrollTop = chatContainer.scrollHeight;
            })
            .catch(error => {
                // Remove loading message
                document.getElementById(loadingId).remove();
                
                // Add error message to chat
                chatContainer.innerHTML += `
                    <div class="card mb-3 border-danger">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="me-3">
                                    <i class="fa fa-exclamation-triangle text-danger fs-3"></i>
                                </div>
                                <div>
                                    <h5 class="card-title">Error</h5>
                                    <p class="card-text">I apologize, but an error occurred: ${error.message}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                
                // Scroll to bottom
                chatContainer.scrollTop = chatContainer.scrollHeight;
            });
        });
        
        // Helper function to hide all sections
        function hideAllSections() {
            document.getElementById('course-generator').classList.add('d-none');
            document.getElementById('feedback-generator').classList.add('d-none');
            document.getElementById('ai-assistant').classList.add('d-none');
        }
        
        // Helper function to format AI response text
        function formatResponse(text) {
            // Convert line breaks to <br> tags
            text = text.replace(/\n/g, '<br>');
            
            // Format markdown-style lists
            text = text.replace(/- ([^\n<]+)/g, '• $1');
            
            return text;
        }
    });
    </script>
</body>
</html>