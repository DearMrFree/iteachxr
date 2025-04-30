<?php
// iTeachXR - AI-enhanced Moodle LMS optimized for Replit
// Main entry point for the application

// Current year for copyright
$currentYear = date('Y');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>iTeachXR - AI-enhanced Learning Platform</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/font-awesome@4.7.0/css/font-awesome.min.css">
    <style>
        .hero-section {
            background: linear-gradient(135deg, #4b6cb7 0%, #182848 100%);
            color: white;
            padding: 120px 0;
            text-align: center;
        }
        
        .feature-icon {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #4b6cb7;
        }
        
        .feature-card {
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            height: 100%;
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
        }
        
        .ai-assistant-widget {
            position: fixed;
            bottom: 30px;
            right: 30px;
            z-index: 1000;
        }
        
        .ai-assistant-panel {
            position: fixed;
            bottom: 100px;
            right: 30px;
            width: 350px;
            height: 500px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.2);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            z-index: 1000;
        }
        
        .ai-chat-header {
            padding: 15px;
            background: #4b6cb7;
            color: white;
        }
        
        .ai-chat-messages {
            flex-grow: 1;
            padding: 15px;
            overflow-y: auto;
        }
        
        .ai-input-area {
            padding: 15px;
            border-top: 1px solid #eee;
            display: flex;
        }
        
        .ai-input-area input {
            flex-grow: 1;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 20px;
            margin-right: 10px;
        }
        
        .chat-message {
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 20px;
            max-width: 80%;
        }
        
        .user-message {
            background-color: #e6f7ff;
            margin-left: auto;
            border-bottom-right-radius: 0;
        }
        
        .ai-message {
            background-color: #f0f0f0;
            margin-right: auto;
            border-bottom-left-radius: 0;
        }
        
        .system-message {
            background-color: #ffecb3;
            margin: 0 auto;
            text-align: center;
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <h1 class="display-3 mb-4">Welcome to iTeachXR</h1>
            <p class="lead mb-5">The AI-enhanced learning platform optimized for immersive education</p>
            <div class="d-flex justify-content-center gap-3">
                <a href="#features" class="btn btn-light btn-lg px-4">Explore Features</a>
                <a href="#demo" class="btn btn-outline-light btn-lg px-4">View Demo</a>
            </div>
        </div>
    </section>
    
    <!-- Features Section -->
    <section class="py-5" id="features">
        <div class="container">
            <div class="row text-center mb-5">
                <div class="col-lg-8 mx-auto">
                    <h2 class="display-5">AI-Powered Education</h2>
                    <p class="lead">Revolutionizing how teachers teach and students learn with advanced AI capabilities</p>
                </div>
            </div>
            
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-robot"></i>
                        </div>
                        <h3>AI Teaching Assistant</h3>
                        <p>Get instant answers to questions, generate content, and receive personalized support with our AI assistant.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-comments"></i>
                        </div>
                        <h3>Automated Feedback</h3>
                        <p>Provide students with immediate, detailed feedback on assignments using our AI grading system.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-route"></i>
                        </div>
                        <h3>Personalized Learning</h3>
                        <p>Create custom learning paths for each student based on their progress, strengths, and learning style.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-vr-cardboard"></i>
                        </div>
                        <h3>XR Integration</h3>
                        <p>Seamlessly incorporate virtual and augmented reality experiences into your courses.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-chart-bar"></i>
                        </div>
                        <h3>Advanced Analytics</h3>
                        <p>Gain insights into student performance and engagement with detailed AI-powered analytics.</p>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="feature-card">
                        <div class="feature-icon">
                            <i class="fa fa-graduation-cap"></i>
                        </div>
                        <h3>Smart Course Creation</h3>
                        <p>Generate course outlines, objectives, and assessments with AI assistance for educators.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- Demo Section -->
    <section class="py-5 bg-light" id="demo">
        <div class="container">
            <div class="row mb-5">
                <div class="col-lg-8 mx-auto text-center">
                    <h2 class="display-5">See It In Action</h2>
                    <p class="lead">Explore different user interfaces for teachers, students, and administrators</p>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="card-title">Teacher Dashboard</h3>
                            <p class="card-text">Access your courses, track student progress, and generate content with AI assistance.</p>
                            <a href="teacher/dashboard.php" class="btn btn-primary">View Demo</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="card-title">Student Dashboard</h3>
                            <p class="card-text">Access learning materials, get personalized recommendations, and track your progress.</p>
                            <a href="student/dashboard.php" class="btn btn-success">View Demo</a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4 mb-4">
                    <div class="card">
                        <div class="card-body text-center">
                            <h3 class="card-title">Admin Dashboard</h3>
                            <p class="card-text">Manage users, courses, and system settings, plus get AI-powered insights.</p>
                            <a href="admin/dashboard.php" class="btn btn-danger">View Demo</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    
    <!-- AI Assistant Widget -->
    <div class="ai-assistant-widget">
        <button id="toggle-ai-assistant" class="btn btn-primary btn-lg rounded-circle">
            <i class="fa fa-robot"></i>
        </button>
    </div>
    
    <div id="ai-assistant-panel" class="ai-assistant-panel" style="display:none;">
        <div class="ai-chat-header">
            <h5 class="mb-0">iTeachXR AI Assistant</h5>
        </div>
        <div id="ai-chat-messages" class="ai-chat-messages">
            <div class="chat-message ai-message">
                <strong>AI Assistant:</strong> Hello! I'm your iTeachXR AI Assistant. How can I help you today?
            </div>
        </div>
        <div class="ai-input-area">
            <input type="text" id="ai-chat-input" placeholder="Ask me anything...">
            <button id="ai-send-button" class="btn btn-primary">
                <i class="fa fa-paper-plane"></i>
            </button>
        </div>
    </div>
    
    <!-- Footer -->
    <footer class="bg-dark text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>iTeachXR</h5>
                    <p>An AI-enhanced learning platform optimized for immersive education</p>
                </div>
                <div class="col-md-3">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Home</a></li>
                        <li><a href="#features" class="text-white">Features</a></li>
                        <li><a href="#demo" class="text-white">Demo</a></li>
                    </ul>
                </div>
                <div class="col-md-3">
                    <h5>Resources</h5>
                    <ul class="list-unstyled">
                        <li><a href="#" class="text-white">Documentation</a></li>
                        <li><a href="#" class="text-white">API</a></li>
                        <li><a href="#" class="text-white">Support</a></li>
                    </ul>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; <?php echo $currentYear; ?> iTeachXR. All rights reserved.</p>
            </div>
        </div>
    </footer>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // AI Assistant Toggle
        document.getElementById('toggle-ai-assistant').addEventListener('click', function() {
            const panel = document.getElementById('ai-assistant-panel');
            if (panel.style.display === 'none') {
                panel.style.display = 'block';
            } else {
                panel.style.display = 'none';
            }
        });
    
        // AI Assistant Message Handling
        document.getElementById('ai-send-button').addEventListener('click', function() {
            sendAIQuery();
        });
        
        document.getElementById('ai-chat-input').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                sendAIQuery();
            }
        });
    
        // Function to send query to AI backend
        function sendAIQuery() {
            const input = document.getElementById('ai-chat-input');
            const query = input.value.trim();
            
            if (query === '') return;
            
            // Add user message to chat
            addMessageToChat('user', query);
            input.value = '';
            
            // Demo response (in a real implementation, this would call the AI API)
            setTimeout(function() {
                const responses = [
                    "I'm here to help you learn and teach with extended reality tools! What specific XR technology are you interested in exploring?",
                    "That's a great question about immersive learning. Research shows that VR can improve retention by up to 75% compared to traditional methods.",
                    "For your XR course, I'd recommend starting with basic 3D object manipulation before moving to more complex interactions.",
                    "Based on learning patterns, students find it helpful to alternate between theory and practical VR experiences every 15-20 minutes."
                ];
                
                const randomResponse = responses[Math.floor(Math.random() * responses.length)];
                addMessageToChat('ai', randomResponse);
            }, 1000);
        }
    
        // Function to add message to chat
        function addMessageToChat(sender, message) {
            const chatContainer = document.getElementById('ai-chat-messages');
            const messageElement = document.createElement('div');
            messageElement.className = 'chat-message ' + sender + '-message';
            
            if (sender === 'user') {
                messageElement.innerHTML = `<strong>You:</strong> ${message}`;
            } else if (sender === 'ai') {
                messageElement.innerHTML = `<strong>AI Assistant:</strong> ${message}`;
            } else {
                messageElement.innerHTML = `<strong>System:</strong> ${message}`;
            }
            
            chatContainer.appendChild(messageElement);
            chatContainer.scrollTop = chatContainer.scrollHeight;
        }
    });
    </script>
</body>
</html>
