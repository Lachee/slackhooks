# Slack Hooks #
A lightweight library to send messages via PHP to Slack. Simple and easy to use. All you need is the webhook.php file included and you are ready to go!

Read the example.php script for examples on how to use the features of the SlackWebhook class and how you can implemented it in your own projects. Further information on how specific formatting can be done can be found on slacks developers api page: https://api.slack.com/docs/messages. This project supports most of the features that slack provides except for buttons. 

Example of attachments: http://take.ms/bQfUT

Feel free to modify, add and change as you wish. I will be happy to accept any merges that add/fixes any issues.

## Setup ##
It is incredibly easy to use this library! 
1. Download or copy the contents of webhook.php to a file of your choosing.
2. Include that file in the top of your PHP document:
	``` php
    <?php
		include_once('webhook.php');
    ?>
	```
3. Profit?

To be fair, there are some other minor steps to get it working. For example, you must grab your Incoming Webhook URL from slack. It should look like this:
```https://hooks.slack.com/services/XXXXXXXXX/YYYYYYYYY/ZZZZZZZZZZZZZZZZZZZZZZZZ```

Once you have your hook, you are ready to go! Follow the tutorials below to find more details!

## Basic Use ##
Here is a quick tutorial for basic use. This PHP library makes it incredibly easy to use!
First of all, we need to define what our Slack Incoming Webhook URL is. We are going to use the following in all tutorials:
```php 
$webhook_url = "https://hooks.slack.com/services/XXXXXXXXX/YYYYYYYYY/ZZZZZZZZZZZZZZZZZZZZZZZZ";
```
Once you have that ready, we will begin! Here is a small snippet for sending a basic message with a ghost icon, like so http://take.ms/rLTbi

```php
<?php
    //Make sure we include the library. For Async to work, it must be on the top!
	include_once('webhook.php');
    
    //Prepare our webhook url    
	$webhook_url = "https://hooks.slack.com/services/XXXXXXXXX/YYYYYYYYY/ZZZZZZZZZZZZZZZZZZZZZZZZ";
    
    //Create the webhook object
    //args: Webhook URL, default name, default icon, [optional] default channel
    $webhook = new SlackWebhook($webhook_url, "Lachee Bot", ":ghost:");
    
    //Send the message
    $result = $webhook->send("Hello World!");
    echo ($result[0] ? "Success" : "Failure") . ": " . $result[1];
    
?>
```

### Include

There we have it! That is all you need to do to send a message. Now, for a break down of what is happening.
First, we are making sure we are including the library and preparing the URL for use. Pretty obvious stuff TBH.
```php
include_once('webhook.php');
$webhook_url = "https://hooks.slack.com/services/XXXXXXXXX/YYYYYYYYY/ZZZZZZZZZZZZZZZZZZZZZZZZ";
```

### Init

Now, here is where the fun begins. We are going to create a new SlackWebhook object. This object is what sends the messages and helps us format them too! It takes 3 parameters in its constructor and a optional 4th. More on this later.

```php
$webhook = new SlackWebhook($webhook_url, "Lachee Bot", ":ghost:");
``` 
    
 ### Send
 Now for the good stuff! With a SlackWebhook object, you can simply call the send() function with your message. There are lots of different ways to send messages, but this is a basic "Hello World". 
 
 This function will return an array of size 2 to 3. Element 0 is a success flag. True when it has succeded, False otherwise. Element 1 is the success/failure message. Mainly for debuging purposes they give some insite to errors. Element 2 is sometimes there and is the error code. If the cURL errors for example, they errno is returned.
 ```php
   $result = $webhook->send("Hello World!");
```

Done! If this is all you wanted to do with your PHP script, you are ready to go! If you want to do more, continue reading!

## Advance Use
### Constructor
The constructor takes 3 arguments with a optional 4th. They are listed below and are used to customise your message slightly.

```php
$webhook = new SlackWebhook($webhook_url, "Lachee Bot", ":ghost:", "@Lachee");
$webhook = new SlackWebhook($webhook_url, "Lachee Bot", "http://example.com/myfancyicon.png", "#-lobby-");
``` 

Param  		 	|Req| Purpose
----------------|---|-------------
Webhook URL  	| Y | The URL that Slack has generated for your incoming webhooks
Bot Name  		| Y	| The default name for the bot. It will automatically get added to every message.
Bot Icon   		| Y	| The Slack Emoji icon or a URL to a icon for your bot to use as a avatar.
Default Channel | N	| A optional variable that will set the default channel to send messages to. By default its the #-lobby- channel.

### Send
The send function is the heart of operations. It will push your messages onto the Slack stack. It comes in 4 flavours.
```php
$webhook->send("message");													//Sends "Message"
$webhook->send("message", "title");											//Appends " - title" to botname
$webhook->send("message", "title", "$channel");								//Sends it to #channel
$webhook->send("message", "title", "$channel", array($an_attachment));		//Adds block attachments
```
Param  		 	|Req| Purpose
----------------|---|-------------
Message	     	| Y | The message to send to slack.
Title    		| N	| The title to append to the botname. If left empty, nothing is appended.
Channel   		| N	| The slack channel to send too. If left empty, the default channel is used.
Attachments		| N	| An array of SlackAttachment objects. Defaults to a empty array. See [Slack Attachment](https://api.slack.com/docs/message-attachments) for more details on how they work and what formatting options are available (There are lots and lots and lots).

### SendAsync
Same as Send however asynchronously. PHP does not nativley support Async cURL calls however, so some trickery has to be used. The async call will send a cURL message to itself. Apond recieving this response, it will close the connection and then send the slack payload onwards to slack, like a forwarding agent. 

This adds a extra process for every message sent and will end when the Slack message is finished. This could be a security flaw that you might not wish to have, so you can disable it with ```SlackWebhook::$ALLOW_ASYNC``` static. 

If it is not working, please note that the include for the library should be one of the first libraries to execute. This will give best performance as it avoids running unessary scripts and avoids conflics with sessions and HTTP requests. 


### CreateLink
Slack uses a its own Markdown formatting for its links. You cannot simply send a href or a normal markdown. The format Slack uses is as such: ```<http://example.com/|Example>``` which would result as such: [Example](http://example.com/). SlackWebhook adds a tool that will format URL's into this markdown for you and strip away any <, > or | and replaces them with their HTML equivilant. It is recommended using this method to avoid accidental url unescaping.
```php
$link = $webhook->createLink("http://www.google.com", "Visit Google");
echo $link;		//output: <http://google.com|Visit Google>
```

### Ignore SSL
This feature should be avoided and is not recommended. However, if you are having problems with cURL or running on a machine that cannot access certificates, use this function to enable/disable the ```CURLOPT_SSL_VERIFYPEER``` parameter on cURL.
```php
//We are ignoring SSL for development puroses. Handy if you are running a non-secure webserver.
$webhook->setIgnoreSSL(true);
```

## Slack Attachments
This is a class in itself and I am far to lazy to explain every single feature. As a short summary, make sure you visit [Slack's Attachment API](https://api.slack.com/docs/message-attachments). 

In short, this feature allows for more complex messages to be sent to Slack such as those fancy coloured blocks you see and attachments of images. Be sure to check it out :) Here is a quick example of every function in its fullness:
```php
//Slack Attachment Example. Note that many of these functions have optional defaults.
$attachment = new SlackAttachment("Text that appears within the attachment", "plain-text summary of the attachment");

$attachment->setColor("#36a64f");
$attachment->setPretext("Text that appears above the attachment");
$attachment->setImage("https://imgs.xkcd.com/comics/exploits_of_a_mom.png");
$attachment->setThumbnail("https://example.com/icon.png");

$attachment->setAuthor("Bobby Tables");
$attachment->setAuthor("Bobby Tables", "https://xkcd.com/327/", "http://flickr.com/icons/bobby.jpg");

$attachment->setTitle("Slack API Documentation");
$attachment->setTitle("Slack API Documentation", "https://api.slack.com/docs/message-attachments");

$attachment->addField("Proprity", "#FFFF00");
$attachment->addField("Proprity", "High", true);

$attachment->setFooter("Slack API");
$attachment->setFooter("Slack API", "https://platform.slack-edge.com/img/default_application_icon.png");

```

Be sure to check out the inline documentation in the webhhok.php file. Documentation for Slack Attachments start at line 318 :P

Thank you for reading all of this and I hope you make wonderfull applications. Be sure to fork and merge any fixes or suggestions you would like to see be implemented.

---

