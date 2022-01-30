<?php

require './Story.php';

// Email parameters
$toEmail = 'wisdomntui@gmail.com';
$ccEmail = 'osuobiem@gmail.com';
$fromEmail = 'wisdomntui@gmail.com';
$password = 'exynos22135546';

// Create new instance of story class
$story = new Story($toEmail, $ccEmail, $fromEmail, $password);

// Execute
$story->story();
