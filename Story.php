<?php

use PHPMailer\PHPMailer\PHPMailer;

require 'vendor/autoload.php';

class Story
{
    private $storyIdsUrl = 'https://hacker-news.firebaseio.com/v0/topstories.json?print=pretty';
    public $toEmail;
    protected $ccEmail;
    private $fromEmail;
    private $password;

    public function __construct($toEmail, $ccEmail, $fromEmail, $password)
    {
        $this->toEmail = $toEmail;
        $this->ccEmail = $ccEmail;
        $this->fromEmail = $fromEmail;
        $this->password = $password;
    }

    /**
     * Entry method that organises the data
     *
     */
    public function story()
    {
        try {
            // Get story IDs
            $storyIds = json_decode($this->fetch($this->storyIdsUrl));
            if (empty($storyIds) || gettype($storyIds) != 'array') {
                return false;
            }

            $stories = [];

            // Loop to fetch stories and add to email template and also save to CSV file
            foreach ($storyIds as $key => $storyId) {
                if ($key > 19) {
                    break;
                }

                $story = json_decode($this->fetch("https://hacker-news.firebaseio.com/v0/item/{$storyId}.json"), true);
                $stories[] = ['title' => isset($story['title']) ? $story['title'] : '', 'url' => isset($story['url']) ? $story['url'] : ''];
            }

            // Generate and save CSV data
            $this->generateCSV($stories);

            // Send email to necessary parties
            $emailData = [
                'to' => $this->toEmail,
                'cc' => $this->ccEmail,
                'stories' => $stories,
                'attachment' => './story_csv/story.csv',
                'from' => $this->fromEmail,
                'password' => $this->password,
            ];
            $this->sendEmail($emailData);
        } catch (\Throwable $th) {
            echo $th->getMessage();
        }
    }

    /**
     * Uses PHP CURL to fetch data from remote URL
     *
     */
    public function fetch($apiUrl)
    {
        // Initialize a CURL session.
        $ch = curl_init();

        // Return Page contents.
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

        // grab URL and pass it to the variable.
        curl_setopt($ch, CURLOPT_URL, $apiUrl);

        $result = curl_exec($ch);

        return $result;
    }

    /**
     * Generate and save content to CSV file
     *
     * @param Array $data
     *
     */
    public function generateCSV($data)
    {
        // Extract array keys to be used as heading for CSV file
        $keys = array_keys($data[0]);
        $csv = implode(',', $keys) . PHP_EOL;

        // Remove unwanted characters and add commas to form CSV string
        foreach ($data as $story) {
            $story['title'] = preg_replace("/\r|\n/", "", $story['title']);
            $story['url'] = preg_replace("/\r|\n/", "", $story['url']);

            $csv .= implode(',', $story) . PHP_EOL;
        }

        file_put_contents('./story_csv/story.csv', $csv);
    }

    /**
     * Send emails
     *
     * @param Array $data
     *
     */
    public function sendEmail($data)
    {
        $mail = new PHPMailer(true);

        $mail->SMTPDebug = 2;
        $mail->isSMTP();
        $mail->Host = 'smtp.googlemail.com;';
        $mail->SMTPAuth = true;
        $mail->Username = $data['from'];
        $mail->Password = $data['password'];
        $mail->SMTPSecure = 'ssl';
        $mail->Port = 465;

        $mail->setFrom($data['from']);
        $mail->addAddress($data['to']);
        $mail->addAddress($data['cc']);
        $mail->addAttachment($data['attachment']);

        $mail->isHTML(true);
        $mail->Subject = 'Storytime stories';
        $mail->Body = $this->emailTemplate($data['stories']);

        $mail->send();
    }

    /**
     * Generate html email template
     *
     * @param Array $data
     */
    public function emailTemplate($data)
    {
        $html = "";

        // generate list items
        foreach ($data as $story) {
            $html .= "<li>
                <p>
                    <b>Title</b>: {$story['title']}
                </p>
                <p>
                    <b>Url</b>: {$story['url']}
                </p>
            </li>";
        }

        // Add list item data into template
        $template = file_get_contents('./email_template/template.html');
        $template = str_replace('{stories}', $html, $template);

        return $template;
    }
}
