/**
 * iTeachXR - Main JavaScript File
 * Provides interactive functionality for the iTeachXR theme
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize all components
    initializeAIAssistant();
    initializeTooltips();
    initializePopovers();
    initializeCourseDashboard();
    initializeAssignmentSubmission();
    setupAccessibilityFeatures();
});

/**
 * Initialize the AI Assistant widget
 */
function initializeAIAssistant() {
    const assistantButton = document.getElementById('toggle-ai-assistant');
    const assistantPanel = document.getElementById('ai-assistant-panel');
    const chatInput = document.getElementById('ai-chat-input');
    const sendButton = document.getElementById('ai-send-button');
    const chatMessages = document.getElementById('ai-chat-messages');
    
    // If elements don't exist, return early
    if (!assistantButton || !assistantPanel || !chatInput || !sendButton || !chatMessages) {
        return;
    }
    
    // Toggle assistant panel visibility
    assistantButton.addEventListener('click', function() {
        if (assistantPanel.style.display === 'none' || assistantPanel.style.display === '') {
            assistantPanel.style.display = 'block';
            chatInput.focus();
            
            // Add welcome message if chat is empty
            if (chatMessages.children.length === 0) {
                addMessageToChat('ai', 'Hello! I\'m your iTeachXR AI assistant. How can I help you with your learning today?');
            }
        } else {
            assistantPanel.style.display = 'none';
        }
    });
    
    // Send message on button click
    sendButton.addEventListener('click', function() {
        sendMessage();
    });
    
    // Send message on Enter key
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    // Function to send message
    function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Display user message
        addMessageToChat('user', message);
        
        // Clear input
        chatInput.value = '';
        
        // Show thinking indicator
        const thinkingEl = document.createElement('div');
        thinkingEl.className = 'chat-message ai-message thinking';
        thinkingEl.innerHTML = '<strong>AI Assistant:</strong> <em>Thinking...</em>';
        chatMessages.appendChild(thinkingEl);
        chatMessages.scrollTop = chatMessages.scrollHeight;
        
        // Get user context for better assistance
        const userContext = getUserContext();
        
        // Send to server
        fetch('/api/ai_service.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                query: message,
                context: userContext
            })
        })
        .then(response => response.json())
        .then(data => {
            // Remove thinking indicator
            chatMessages.removeChild(thinkingEl);
            
            // Display response
            if (data.success) {
                addMessageToChat('ai', data.response);
            } else {
                addMessageToChat('ai', 'Sorry, I encountered an error: ' + data.message);
            }
        })
        .catch(error => {
            // Remove thinking indicator
            chatMessages.removeChild(thinkingEl);
            
            // Display error
            console.error('Error:', error);
            addMessageToChat('ai', 'Sorry, there was an error processing your request. Please try again later.');
        });
    }
    
    // Function to add message to chat
    function addMessageToChat(sender, message) {
        const messageEl = document.createElement('div');
        messageEl.className = `chat-message ${sender}-message fade-in`;
        
        if (sender === 'user') {
            messageEl.innerHTML = `<strong>You:</strong> ${message}`;
        } else {
            messageEl.innerHTML = `<strong>AI Assistant:</strong> ${message}`;
        }
        
        chatMessages.appendChild(messageEl);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Function to get user context for better AI assistance
    function getUserContext() {
        // This would be extended in a real implementation to include
        // current course, recent activities, user role, etc.
        const context = {
            page: window.location.pathname,
            time: new Date().toISOString()
        };
        
        // Add course context if available
        const courseId = document.querySelector('body').getAttribute('data-courseid');
        if (courseId) {
            context.courseId = courseId;
        }
        
        return context;
    }
}

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    // Initialize all tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Initialize Bootstrap popovers
 */
function initializePopovers() {
    // Initialize all popovers
    const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl);
    });
}

/**
 * Initialize course dashboard functionality
 */
function initializeCourseDashboard() {
    // Setup course progress tracking
    const progressBars = document.querySelectorAll('.course-progress');
    if (progressBars.length > 0) {
        progressBars.forEach(progressBar => {
            const value = progressBar.getAttribute('data-value');
            progressBar.style.width = value + '%';
            
            // Set color based on progress
            if (value < 30) {
                progressBar.classList.add('bg-danger');
            } else if (value < 70) {
                progressBar.classList.add('bg-warning');
            } else {
                progressBar.classList.add('bg-success');
            }
        });
    }
    
    // Setup AI recommendation expanders
    const recommendationToggles = document.querySelectorAll('.toggle-recommendation');
    if (recommendationToggles.length > 0) {
        recommendationToggles.forEach(toggle => {
            toggle.addEventListener('click', function(e) {
                e.preventDefault();
                const target = document.getElementById(this.getAttribute('data-target'));
                if (target) {
                    if (target.style.display === 'none' || target.style.display === '') {
                        target.style.display = 'block';
                        this.innerHTML = 'Show less <i class="fa fa-chevron-up"></i>';
                    } else {
                        target.style.display = 'none';
                        this.innerHTML = 'Show more <i class="fa fa-chevron-down"></i>';
                    }
                }
            });
        });
    }
}

/**
 * Initialize assignment submission functionality
 */
function initializeAssignmentSubmission() {
    const submissionForm = document.getElementById('assignment-submission-form');
    if (!submissionForm) return;
    
    // Add file upload preview
    const fileInput = document.getElementById('submission-file');
    const filePreview = document.getElementById('file-preview');
    
    if (fileInput && filePreview) {
        fileInput.addEventListener('change', function() {
            // Clear previous preview
            filePreview.innerHTML = '';
            
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                const fileItem = document.createElement('div');
                fileItem.className = 'file-item';
                fileItem.innerHTML = `
                    <i class="fa fa-file"></i>
                    <span>${file.name}</span>
                    <small>(${formatFileSize(file.size)})</small>
                `;
                filePreview.appendChild(fileItem);
            }
        });
    }
    
    // Add AI feedback preview for text submissions
    const textInput = document.getElementById('submission-text');
    const aiFeedbackPreview = document.getElementById('ai-feedback-preview');
    const previewButton = document.getElementById('preview-feedback-button');
    
    if (textInput && aiFeedbackPreview && previewButton) {
        previewButton.addEventListener('click', function(e) {
            e.preventDefault();
            
            const text = textInput.value.trim();
            if (!text) {
                aiFeedbackPreview.innerHTML = '<div class="alert alert-warning">Please enter some text to get feedback</div>';
                return;
            }
            
            // Show loading indicator
            aiFeedbackPreview.innerHTML = '<div class="text-center"><i class="fa fa-spinner fa-spin"></i> Analyzing your submission...</div>';
            
            // Get assignment ID from form
            const assignmentId = submissionForm.getAttribute('data-assignment-id');
            
            // Request AI feedback preview
            fetch('/api/ai_service.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    action: 'preview_feedback',
                    assignment_id: assignmentId,
                    submission_text: text
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Display feedback preview
                    aiFeedbackPreview.innerHTML = `
                        <div class="ai-feedback">
                            <h4>AI Feedback Preview</h4>
                            <p><strong>Overall:</strong> ${data.feedback.overall_assessment}</p>
                            
                            <h5>Strengths:</h5>
                            <ul>
                                ${data.feedback.strengths.map(item => `<li>${item}</li>`).join('')}
                            </ul>
                            
                            <h5>Suggestions for improvement:</h5>
                            <ul>
                                ${data.feedback.areas_for_improvement.map(item => `<li>${item}</li>`).join('')}
                            </ul>
                            
                            <div class="alert alert-info">
                                <i class="fa fa-info-circle"></i> This is an AI-generated preview to help improve your work before final submission. 
                                The instructor's feedback may differ.
                            </div>
                        </div>
                    `;
                } else {
                    aiFeedbackPreview.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                aiFeedbackPreview.innerHTML = '<div class="alert alert-danger">An error occurred while generating feedback</div>';
            });
        });
    }
    
    // Handle form submission
    submissionForm.addEventListener('submit', function(e) {
        // Add form validation here if needed
    });
}

/**
 * Format file size in human-readable format
 * @param {number} bytes - File size in bytes
 * @returns {string} - Formatted file size
 */
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

/**
 * Setup accessibility features
 */
function setupAccessibilityFeatures() {
    // Skip to content link
    const skipLink = document.getElementById('skip-to-content');
    if (skipLink) {
        skipLink.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.getElementById('page-content');
            if (target) {
                target.tabIndex = -1;
                target.focus();
            }
        });
    }
    
    // Add ARIA roles to elements
    document.querySelectorAll('nav').forEach(nav => {
        if (!nav.hasAttribute('role')) {
            nav.setAttribute('role', 'navigation');
        }
    });
    
    document.querySelectorAll('main, [role="main"]').forEach(main => {
        if (!main.hasAttribute('role')) {
            main.setAttribute('role', 'main');
        }
    });
    
    // Add focus outline for keyboard navigation
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Tab') {
            document.body.classList.add('keyboard-navigation');
        }
    });
    
    document.addEventListener('mousedown', function() {
        document.body.classList.remove('keyboard-navigation');
    });
}

/**
 * Display confirmation dialog
 * @param {string} message - Confirmation message
 * @param {Function} callback - Function to call if confirmed
 */
function confirmAction(message, callback) {
    if (confirm(message)) {
        callback();
    }
}

/**
 * Show notification
 * @param {string} message - Notification message
 * @param {string} type - Notification type (success, error, warning, info)
 * @param {number} duration - Duration in milliseconds
 */
function showNotification(message, type = 'info', duration = 5000) {
    const notificationContainer = document.getElementById('notification-container');
    
    // Create container if it doesn't exist
    if (!notificationContainer) {
        const container = document.createElement('div');
        container.id = 'notification-container';
        container.style.position = 'fixed';
        container.style.top = '20px';
        container.style.right = '20px';
        container.style.zIndex = '9999';
        document.body.appendChild(container);
    }
    
    // Create notification
    const notification = document.createElement('div');
    notification.className = `alert alert-${type} notification fade-in`;
    notification.innerHTML = `
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        ${message}
    `;
    
    // Add to container
    document.getElementById('notification-container').appendChild(notification);
    
    // Auto-dismiss after duration
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, duration);
    
    // Close button functionality
    notification.querySelector('.btn-close').addEventListener('click', function() {
        notification.style.opacity = '0';
        setTimeout(() => {
            notification.remove();
        }, 300);
    });
}

/**
 * Load content dynamically using AJAX
 * @param {string} url - URL to load
 * @param {string} targetId - ID of target element
 * @param {Function} callback - Optional callback function
 */
function loadContent(url, targetId, callback = null) {
    const target = document.getElementById(targetId);
    if (!target) return;
    
    // Show loading indicator
    target.innerHTML = '<div class="text-center p-3"><i class="fa fa-spinner fa-spin"></i> Loading...</div>';
    
    fetch(url)
        .then(response => response.text())
        .then(html => {
            target.innerHTML = html;
            if (callback && typeof callback === 'function') {
                callback();
            }
        })
        .catch(error => {
            console.error('Error loading content:', error);
            target.innerHTML = '<div class="alert alert-danger">Failed to load content</div>';
        });
}
