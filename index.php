<?php
// iTeachXR - AI-enhanced Moodle LMS optimized for Replit
// Main entry point for the application

// Include configuration and required libraries
require_once('config.php');
require_once($CFG->dirroot . '/lib/setup.php');

// Redirect to login if not authenticated
if (!isloggedin() && !isguestuser()) {
    redirect($CFG->wwwroot . '/login/index.php');
}

// Set up page
$PAGE->set_context(context_system::instance());
$PAGE->set_pagelayout('frontpage');
$PAGE->set_title($SITE->fullname);
$PAGE->set_heading($SITE->fullname);
$PAGE->set_url(new moodle_url('/index.php'));

// Begin output
echo $OUTPUT->header();

// Display role-specific dashboard
if (is_siteadmin()) {
    // Admin dashboard
    include('admin/dashboard_content.php');
} elseif (has_capability('moodle/course:update', context_system::instance())) {
    // Teacher dashboard
    include('teacher/dashboard_content.php');
} else {
    // Student dashboard
    include('student/dashboard_content.php');
}

// AI Assistant Widget - present for all users
echo '<div id="ai-assistant-widget" class="iteachxr-ai-widget">
    <button id="toggle-ai-assistant" class="btn btn-primary">
        <i class="fa fa-robot"></i> AI Assistant
    </button>
    <div id="ai-assistant-panel" style="display:none;">
        <h3>iTeachXR AI Assistant</h3>
        <div id="ai-chat-messages"></div>
        <div class="ai-input-area">
            <input type="text" id="ai-chat-input" placeholder="Ask anything about your courses...">
            <button id="ai-send-button" class="btn btn-success">Send</button>
        </div>
    </div>
</div>';

// Recent activity and notifications
echo '<div class="iteachxr-recent-activity">
    <h2>Recent Activity</h2>
    <div id="activity-feed"></div>
</div>';

// End page
echo $OUTPUT->footer();
?>

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
        
        // Send query to AI service
        fetch('<?php echo $CFG->wwwroot; ?>/api/ai_service.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({
                query: query,
                context: 'dashboard',
                user_id: <?php echo $USER->id; ?>
            })
        })
        .then(response => response.json())
        .then(data => {
            // Add AI response to chat
            addMessageToChat('ai', data.response);
        })
        .catch(error => {
            console.error('Error:', error);
            addMessageToChat('system', 'Sorry, there was an error processing your request.');
        });
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

    // Load recent activity
    loadRecentActivity();
    
    function loadRecentActivity() {
        const activityFeed = document.getElementById('activity-feed');
        fetch('<?php echo $CFG->wwwroot; ?>/user/recent_activity.php', {
            method: 'GET'
        })
        .then(response => response.json())
        .then(data => {
            if (data.activities && data.activities.length > 0) {
                data.activities.forEach(activity => {
                    const activityItem = document.createElement('div');
                    activityItem.className = 'activity-item';
                    activityItem.innerHTML = `
                        <div class="activity-time">${activity.time}</div>
                        <div class="activity-content">
                            <strong>${activity.type}:</strong> ${activity.description}
                        </div>
                    `;
                    activityFeed.appendChild(activityItem);
                });
            } else {
                activityFeed.innerHTML = '<p>No recent activity</p>';
            }
        })
        .catch(error => {
            console.error('Error loading activity:', error);
            activityFeed.innerHTML = '<p>Unable to load recent activity</p>';
        });
    }
});
</script>
