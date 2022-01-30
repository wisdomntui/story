<?php

require './Story.php';

// Email parameters
$toEmail = 'jonathan@sourceitmarketing.com'; // First receipient
$ccEmail = 'malik@sourceitmarketing.com'; // Second receipient
$fromEmail = ''; // Your gmail address
$password = ''; // Your gmail password

// Create new instance of story class
$story = new Story($toEmail, $ccEmail, $fromEmail, $password);

// Execute
$story->story();
