<?php

/**
 * webhook.php
 *
 * A lightweight library to send simple messages to a Slack group using Slack Webhooks. 
 * Does NOT currently support Slack's Message Format and can only send basic text messages. 
 * 
 * Contains tools for formatting links. Later versions will implement Slack's Message Format
 *
 * @category   Intergration
 * @package    SlackWebhook
 * @author     Lachee
 * @copyright  2016 Lachee
 * @license    https://creativecommons.org/licenses/by/3.0/au/ Attribution 3.0 Australia (CC BY 3.0 AU)
 */
class SlackWebhook {
	private $webhook;
	private $ignore_ssl = false;
	private $default_channel;
	private $use_markdown = true;
	var $botname;
	var $boticon;
	function __construct($webhook_url, $botname, $boticon, $default_channel = "#-lobby-") {
		$this->webhook = $webhook_url;
		$this->botname = $botname;
		
		$this->boticon = $boticon;
		$this->default_channel = $default_channel;
	}
	function enableMarkdown($enable) {
		$this->use_markdown = $enable;
	}
	function usingMarkdown() {
		return $this->use_markdown;
	}
	
	/**
	 * Is the icon a emoji?
	 * 
	 * @return boolean
	 */
	function usingEmoji() {
		if (strpos($this->boticon, 'http') !== false)
			return false;
		
		return true;
	}
	
	/**
	 * Tells the CURL to ignore any SSL that slack may want from us.
	 * 
	 * @param boolean $ignoreSSL        	
	 */
	function setIgnoreSSL($ignoreSSL) {
		$this->ignore_ssl = $ignoreSSL;
	}
	
	/**
	 * Sets the default channel
	 * 
	 * @param string $channel        	
	 */
	function setChannel($channel) {
		$this->default_channel = $channel;
	}
	
	/**
	 * Fetches the default channel
	 * 
	 * @return string
	 */
	function getChannel() {
		return $this->default_channel;
	}
	
	/**
	 * Sends a message to slack via a channel.
	 * Returns true on success.
	 *  	
	 * @param string $message   
	 * @param string $title    
	 * @param string $channel       	
	 * @param SlackAttachment[] $attachments
	 * 
	 * @return array(boolean, message)
	 */
	function send($message, $title = "", $channel = "", $attachments = array()) {
		
		// Prepare the channel
		if (empty($channel))
			$channel = $this->default_channel;
			
		// prepare the payload
		$payload = $this->_preparePayload($title, $channel, $message,  $attachments);
		return $this->_sendPayload($payload);
	}
	
	
	function createLink($source, $name = "") {
		// clean the source up
		$source = str_replace("<", "%3C", $source);
		$source = str_replace(">", "%3E", $source);
		$source = str_replace("|", "%7C", $source);
		
		if (empty($name)) {
			$name = $source;
		} else {
			// clean the name up
			$name = str_replace("<", "%3C", $name);
			$name = str_replace(">", "%3E", $name);
			$name = str_replace("|", "%7C", $name);
		}
		
		return "<{$source}|{$name}>";
	}
	
	/**
	 * Sends the generated json payload
	 * @param string $payload
	 * @return array(boolean, message)
	 */
	private function _sendPayload($payload) {

		// Prepare the curl channel
		$ch = curl_init();
		
		// Set the address
		curl_setopt($ch, CURLOPT_URL, $this->webhook);
		
		// Set the fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array(
				'payload' => $payload
		));
		
		// Should we ignore ssl?
		if ($this->ignore_ssl)
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
				
			// Send the message off
			$content = curl_exec($ch);
		
			// Did we fail to get anything back?
			if (FALSE === $content) {
				return array(
						false,
						curl_error($ch),
						curl_errno($ch)
				);
			}
		
			// Did we fail to send the message?
			if ($content != "ok") {
				return array(
						false,
						$content
				);
			}
		
			// We where succesfull
			return array(
					true,
					$content
			);
	}
	
	/**
	 * Creates a JSON formated payload
	 *
	 * @param string $channel
	 * @param string $message
	 * @return string
	 */
	private function _preparePayload($title, $channel, $message, $attachments = array()) {
		//Prepare the payload
		$payload = array();
		$payload['channel'] = $channel;
		$payload['username'] = $this->botname;
		$payload['text'] = $message;
		$payload['mrkdown'] = $this->usingMarkdown();
		
		//Prepare the attachments, if we have any
		if (count($attachments) > 0) {
			
			//Create a empty array
			$payload['attachments'] = array();
			
			//For each attachment object, we need to convert it to an array and add it.
			foreach($attachments as $att) {
				$payload['attachments'][] = $att->createAssocArray();
			}
		}
		
		//Are we using a emoji or a url?
		$payload[$this->usingEmoji() ? 'icon_emoji' : 'icon_url'] = $this->boticon;
		
		//Set the title if its not empty. If we have no name, we will make the title the name
		if (!empty($title)) {
			if (empty($payload['username'])) {
				$payload['username'] = $title;
			} else {
				$payload['username'] .= " - {$title}";
			}
		}
		
		//Encode the json and send it back
		$json = json_encode($payload);
		return $json;
	}
}

/**
 * A attachment for a Slack Message.
 * See https://api.slack.com/docs/message-attachments for more details about each element.
 * 
 * @author Lachee
 *        
 */
class SlackAttachment {
	private $fallback;
	private $text;
	private $timestamp;
	private $title = "";
	private $title_link = "";
	private $pretext = "";
	private $color = "";
	private $footer = "";
	private $footer_icon = "";
	private $image_url = "";
	private $thumb_url = "";
	private $author_name = "";
	private $author_link = "";
	private $author_icon = "";
	private $fields = array();
	function __construct($body, $fallback) {
		$this->fallback = $fallback;
		$this->text = $body;
		$this->timestamp = time();
	}
	
	/**
	 * Fields are defined as an array, and hashes contained within it will be displayed in a table inside the message attachment.
	 * 
	 * @param string $title        	
	 * @param string $text        	
	 * @param string $isShort        	
	 */
	function addField($title, $text, $isShort = false) {
		$this->fields[] = array(
				'title' => $title,
				'value' => $text,
				'short' => $isShort
		);
	}
	
	/**
	 * Sets the time this attachment was made.
	 * 
	 * @param epochtime $timestamp        	
	 */
	function setTimestamp($timestamp) {
		$this->timestamp = $timestamp;
	}
	
	/**
	 * Sets the author of the attachment with optional links and icons.
	 * 
	 * @param string $name        	
	 * @param string $link        	
	 * @param string $icon        	
	 */
	function setAuthor($name, $link = "", $icon = "") {
		$this->author_name = $name;
		$this->author_link = $link;
		$this->author_icon = $icon;
	}
	
	/**
	 * Adds a image to the attachment.
	 * A valid URL to an image file that will be displayed inside a message attachment. We currently support the following formats: GIF, JPEG, PNG, and BMP.
	 * Large images will be resized to a maximum width of 400px or a maximum height of 500px, while still maintaining the original aspect ratio.
	 * 
	 * @param string $url        	
	 */
	function setImage($url) {
		$this->image_url = $url;
	}
	
	/**
	 * Adds a icon to the attachment
	 *
	 * A valid URL to an image file that will be displayed as a thumbnail on the right side of a message attachment. We currently support the following formats: GIF, JPEG, PNG, and BMP.
	 * The thumbnail's longest dimension will be scaled down to 75px while maintaining the aspect ratio of the image. The filesize of the image must also be less than 500 KB.
	 * For best results, please use images that are already 75px by 75px.
	 *
	 * @param unknown $url        	
	 */
	function setThumbnail($url) {
		$this->thumb_url = $url;
	}
	
	/**
	 * Sets a small footer with a thumbnail icon on the bottom of the message.
	 * Limited 300 characters
	 * 
	 * @param string $footer        	
	 * @param string $footer_icon        	
	 */
	function setFooter($footer, $footer_icon = "") {
		$this->footer = $footer;
		$this->footer_icon = $footer_icon;
	}
	
	/**
	 * Sets the title of the attachment with an optional URL
	 * 
	 * @param string $title        	
	 * @param string $url        	
	 */
	function setTitle($title, $url = "") {
		$this->title = $title;
		$this->title_link = $url;
	}
	
	/**
	 * This is a small message the appears above the attachment
	 * 
	 * @param string $message        	
	 */
	function setPretext($message) {
		$this->pretext = $message;
	}
	
	/**
	 * Sets the hexidecimal colour code of the side bar.
	 * 'good', 'warning' and 'danger' are also accepted.
	 * 
	 * @param string $color        	
	 */
	function setColor($color) {
		$this->color = $color;
	}
	
	/**
	 * Creates an associated array of this attachment.
	 * This is used by the Webhook.
	 * 
	 * @return associated array
	 */
	function createAssocArray() {
		$arr = array(
				'fallback' => $this->fallback,
				'text' => $this->text,
				'fields' => $this->fields,
				'ts' => $this->timestamp
		);
		
		if (!empty($this->pretext)) {
			$arr['pretext'] = $this->pretext;
		}
		if (!empty($this->color)) {
			$arr['color'] = $this->color;
		}
		
		if (!empty($this->title)) {
			$arr['title'] = $this->title;
		}
		if (!empty($this->title_link)) {
			$arr['title_link'] = $this->title_link;
		}
		if (!empty($this->footer)) {
			$arr['footer'] = $this->footer;
		}
		if (!empty($this->footer_icon)) {
			$arr['footer_icon'] = $this->footer_icon;
		}
		
		if (!empty($this->image_url)) {
			$arr['image_url'] = $this->image_url;
		}
		if (!empty($this->thumb_url)) {
			$arr['thumb_url'] = $this->thumb_url;
		}
		
		if (!empty($this->author_name)) {
			$arr['author_name'] = $this->author_name;
		}
		if (!empty($this->author_link)) {
			$arr['author_link'] = $this->author_link;
		}
		if (!empty($this->author_icon)) {
			$arr['author_icon'] = $this->author_icon;
		}
		
		return $arr;
	}
}