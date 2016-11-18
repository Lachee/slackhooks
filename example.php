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
echo "Success: {$success[0]}<br>";

// ====== Send a message with a title ======
$success = $webhook->send("Hello World!", "Title");
echo "Success: {$success[0]}<br>";

// ====== Send a message to a specific channel ========
$success = $webhook->send("Hello World!", "", "#-lobby-");
echo "Success: {$success[0]}<br>";

// ====== Send a message to a specific channel and a title ========
$success = $webhook->send("Hello World!", "Title", "#-lobby-");
echo "Success: {$success[0]}<br>";

// ====== Preparing Links ========
$link = $webhook->createLink("http://www.google.com");
$success = $webhook->send("Here is a link: {$link}");
echo "Success: {$success[0]}<br>";

// ====== Preparing Links with titles ========
$link = $webhook->createLink("http://www.google.com", "Visit Google");
$success = $webhook->send("Here is a link: {$link}");
echo "Success: {$success[0]}<br>";
