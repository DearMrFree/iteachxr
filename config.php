<?php
// iTeachXR - AI-enhanced Moodle LMS Configuration
// Modified from Moodle config file for Replit environment

// Database setup for Replit
$CFG = new stdClass();
$CFG->dbtype    = 'pgsql';
$CFG->dblibrary = 'pdo';
$CFG->dbhost    = '127.0.0.1';
$CFG->dbname    = 'replit_db';
$CFG->dbuser    = getenv('DB_USER') ?: 'replit';
$CFG->dbpass    = getenv('DB_PASSWORD') ?: '';
$CFG->prefix    = 'mdl_';
$CFG->dboptions = array(
    'dbpersist' => false,
    'dbsocket'  => false,
    'dbport'    => '',
);

// Replit-specific paths
$CFG->wwwroot   = 'https://' . getenv('REPL_SLUG') . '.' . getenv('REPL_OWNER') . '.repl.co';
$CFG->dataroot  = '/home/runner/' . getenv('REPL_SLUG') . '/moodledata';
$CFG->dirroot   = '/home/runner/' . getenv('REPL_SLUG');
$CFG->tempdir   = $CFG->dataroot . '/temp';
$CFG->cachedir  = $CFG->dataroot . '/cache';

// OpenAI API Key for AI features
$CFG->openai_api_key = getenv('OPENAI_API_KEY') ?: '';

// Default theme
$CFG->theme = 'iteachxr';

// Debug settings - set to false for production
$CFG->debug = (E_ALL | E_STRICT);
$CFG->debugdisplay = true;
$CFG->debugdeveloper = true;

// Session and cookie settings
$CFG->sessiontimeout = 8 * 60 * 60; // 8 hours
$CFG->sessioncookie = '';
$CFG->sessioncookiepath = '/';
$CFG->sessioncookiedomain = '';

// Language settings
$CFG->lang = 'en';
$CFG->langmenu = true;
$CFG->langlist = '';
$CFG->locale = 'en_US.UTF-8';

// Security settings
$CFG->passwordpolicy = true;
$CFG->passwordreuselimit = 5;
$CFG->minpasswordlength = 8;
$CFG->minpassworddigits = 1;
$CFG->minpasswordlower = 1;
$CFG->minpasswordupper = 1;
$CFG->minpasswordnonalphanum = 1;
$CFG->maxconsecutiveidentchars = 2;

// Set timezone
date_default_timezone_set('UTC');

// iTeachXR AI enhancements
$CFG->enableAI = true;
$CFG->aiFeatures = [
    'content_recommendations' => true,
    'automated_feedback' => true,
    'plagiarism_detection' => true,
    'personalized_learning' => true,
    'chatbot_assistant' => true
];

// Required Moodle setup
require_once($CFG->dirroot . '/lib/setup.php');
