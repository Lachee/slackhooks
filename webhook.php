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
	
	var $botname;
	var $boticon;
	
	function __construct($webhook_url, $botname, $boticon, $default_channel = "#-lobby-")
	{
		$this->webhook = $webhook_url;
		$this->botname = $botname;
		
		$this->boticon = $boticon;
		$this->default_channel = $default_channel;
	}

	/**
	 * Is the icon a emoji?
	 * @return boolean
	 */
	function usingEmoji() {
		if (strpos($this->boticon, 'http') !== false) 
			return false;
		
		return true;
	}
	
	/**
	 * Tells the CURL to ignore any SSL that slack may want from us.
	 * @param boolean $ignoreSSL
	 */
	function setIgnoreSSL($ignoreSSL) {
		$this->ignore_ssl = $ignoreSSL;
	}
	
	/**
	 * Sets the default channel
	 * @param string $channel
	 */
	function setChannel($channel) {
		$this->default_channel = $channel;
	}
	
	/**
	 * Fetches the default channel
	 * @return string
	 */
	function getChannel() {
		return $this->default_channel;
	}
	
	/**
	 * Sends a message to slack via a channel. Returns true on success.
	 * @param string $channel
	 * @param string $message
	 * @return boolean
	 */
	function send($message, $title = "", $channel = "") {
		
		//Prepare the channel
		if (empty($channel)) 
			$channel = $this->default_channel;
		
		//prepare the payload
		$payload = $this->_preparePayload($title, $channel, $message);

		//Prepare the curl channel
		$ch = curl_init();
		
		//Set the address
		curl_setopt($ch, CURLOPT_URL, $this->webhook);
		
		//Set the fields
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, array('payload' => $payload));
				
		//Should we ignore ssl?
		if ($this->ignore_ssl) 
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		
		//Send the message off
		$content = curl_exec($ch);
		
		//Did we fail to get anything back?
		if (FALSE === $content) {
			return array(false, curl_error($ch), curl_errno($ch));
		}
		
		//Did we fail to send the message?
		if ($content != "ok") {
			return array(false, $content);
		}
		
		//We where succesfull
		return array(true, $content);
	}
	
	
	function createLink($source, $name=""){
		//clean the source up
		$source = str_replace("<", "%3C", $source);
		$source = str_replace(">", "%3E", $source);
		$source = str_replace("|", "%7C", $source);
		
		if (empty($name)) {
			$name = $source;
		}else{
			//clean the name up
			$name = str_replace("<", "%3C", $name);
			$name = str_replace(">", "%3E", $name);
			$name = str_replace("|", "%7C", $name);
		}
		
		return "<{$source}|{$name}>";
	}
	
	/**
	 * Creates a JSON formated payload
	 * @param string $channel
	 * @param string $message
	 * @return string
	 */
	private function _preparePayload($title, $channel, $message) {
		$payload = array();
		$payload['channel'] = $channel;
		$payload['username'] = $this->botname;
		$payload['text'] = $message;
		
		if ($this->usingEmoji())
			$payload['icon_emoji'] = $this->boticon;
		else 
			$payload['icon_url'] = $this->boticon;
					
		if (!empty($title)) {
			if (empty($payload['username']))
				$payload['username'] = $title;
			else 
				$payload['username'] .= " - {$title}";
		}
			
		$json = json_encode($payload);
		return $json;
	}
}