<?php


// ===== Include the library
include_once('webhook.php');


// ===== Prepare settings
//This is the hook url slack generates for you
$webhook_url = "https://hooks.slack.com/services/XXXXXXXXX/YYYYYYYYY/ZZZZZZZZZZZZZZZZZZZZZZZZ";

//[optional] The bots name. If left blank, slacks default is used
$botname = "Robo Lachee";

//[optional] Url or emoji of the bots icon. If left blank, slacks default is used.
$boticon = ":ghost:";

//[optional] The default channel to send messages to. If left black, #-lobby- is used.
// This is used when the channel is not passed
$channel = "@Lachee";


// ===== Implementation
//Create the slack webhook object
$webhook = new SlackWebhook($webhook_url, $botname, $boticon, $channel);

//We are ignoring SSL for development puroses. Handy if you are running a non-secure webserver.
$webhook->setIgnoreSSL(true);

// ====== Send a message =======
$success = $webhook->send("Hello World!");
echo "Hello World: {$success[0]}<br>";
echo "<img src='http://take.ms/rLTbi' /><br><br>";

// ====== Send a message with a title ======
$success = $webhook->send("Hello World!", "Title");
echo "Hello World Title: {$success[0]}<br>";
echo "<img src='http://take.ms/T751l' /><br><br>";

// ====== Send a message to a specific channel ========
$success = $webhook->send("Hello World!", "", "#-lobby-");
echo "Channel Select: {$success[0]}<br>";

// ====== Send a message to a specific channel and a title ========
$success = $webhook->send("Hello World!", "Title", "#-lobby-");
echo "Channel Select Title: {$success[0]}<br>";

// ====== Preparing Links ========
$link = $webhook->createLink("http://www.google.com");
$success = $webhook->send("Here is a link: {$link}");
echo "Links: {$success[0]}<br>";

// ====== Preparing Links with titles ========
$link = $webhook->createLink("http://www.google.com", "Visit Google");
$success = $webhook->send("Here is a link: {$link}");
echo "Named Links: {$success[0]}<br>";
echo "<img src='http://take.ms/ViUx1' /><br><br>";

// ======= Attachments =======
//First we need to prepare an attachment
$attachment = new SlackAttachment("Text that appears within the attachment", "plain-text summary of the attachment");
$attachment->setColor("#36a64f");
$attachment->setPretext("Text that appears above the attachment");
$attachment->setAuthor("Bobby Tables", "https://xkcd.com/327/", "http://flickr.com/icons/bobby.jpg");
$attachment->setTitle("Slack API Documentation", "https://api.slack.com/docs/message-attachments");
$attachment->addField("Proprity", "High", true);
$attachment->addField("Urgency", "Low", true);
$attachment->setImage("https://imgs.xkcd.com/comics/exploits_of_a_mom.png");
$attachment->setFooter("Slack API", "https://platform.slack-edge.com/img/default_application_icon.png");

//Now send it off
$success = $webhook->send("This is s atest attachment", "", "", array($attachment));
echo "Attachments: {$success[0]}<br>";
echo "<img src='http://take.ms/bQfUT'/><br>";
