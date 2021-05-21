<?php
 {
					class whatsAppBot{
					//specify instance URL and token
					var $APIurl = 'https://api.chat-api.com/instance265151/';
					var $token = 'o6xr3fhed0latcn3';

					public function __construct(){
						
					//get the JSON body from the instance
					$json = file_get_contents('php://input');
					$decoded = json_decode($json,true);

					//write parsed JSON-body to the file for debugging
					ob_start();
					var_dump($decoded);
					$input = ob_get_contents();
					ob_end_clean();
					file_put_contents('input_requests.log',$input.PHP_EOL,FILE_APPEND);

					if(isset($decoded['messages'])){
						
					//check every new message
					foreach($decoded['messages'] as $message){
					//delete excess spaces and split the message on spaces. The first word in the message is a command, other words are parameters
					$text = explode(' ',trim($message['body']));
					echo trim($message['body']);
					//current message shouldn't be send from your bot, because it calls recursion
					if(!$message['fromMe']){
						
						
						$isWelcome = (preg_match('/^.?(Hello|Hi|Hey).?/i', $message['body']) == 1);
						if ($isWelcome){
							$this->welcome($message['chatId']);
						} else 
						{
							$isRequestTea = (preg_match('/^.?(1|Tea).?/i', $message['body']) == 1);
							$isRequestCoffee = (preg_match('/^.?(2|Coffee).?/i', $message['body']) == 1);
							if ($isRequestTea || $isRequestCoffee){
								$this->sugarQuantity($message['chatId']);
							} else 
							{
								$isRequestSugarQuantity = (preg_match('/^.?(None|Free|One|Two|Three).?/i', $message['body']) == 1); 
								if ($isRequestSugarQuantity ){
									$this->preferMilk($message['chatId']);
								} else 
								{
									$isPreferMilk = (preg_match('/^.?(Yes|No).?/i', $message['body']) == 1); 
									if ($isPreferMilk ){
										$this->Thanks($message['chatId']);
									} else 
									{
										// $this->Incorrect($message['chatId']);
									}
								}
							}
						} 
					}
					}
					}}

					//this function calls function sendRequest to send a simple message
					//@param $chatId [string] [required] - the ID of chat where we send a message
					//@param $text [string] [required] - text of the message
					public function welcome($chatId){ 
						$this->sendMessage($chatId, 
						"How can i help? \n".
						"Do you like to drink \n".
						"1. Tea \n".
						"2. Coffee\n"
						);
					}
					public function Incorrect($chatId){ 
						$this->sendMessage($chatId,
						"Incorrect command\n" 
						);
					}

					 public function sugarQuantity($chatId){ 
						$this->sendMessage($chatId, 
						"How many sugar? \n" .
						"[None|Free|One|Two|Three]"
						);
					}
					public function preferMilk($chatId){ 
						$this->sendMessage($chatId, 
						"Do you like Milk? \n" .
						"[Yes|No]"
						);
					}
					public function Thanks($chatId){ 
						$this->sendMessage($chatId, 
						"Ok. Thank you \n" 
						);
					}
					//sends Id of the current chat. it is called when the bot gets the command "chatId"
					//@param $chatId [string] [required] - the ID of chat where we send a message
					public function showchatId($chatId){
						$this->sendMessage($chatId,'chatId: '.$chatId);
					}
					//sends current server time. it is called when the bot gets the command "time"
					//@param $chatId [string] [required] - the ID of chat where we send a message
					public function time($chatId){
						$this->sendMessage($chatId,date('d.m.Y H:i:s'));
					}
					//sends your nickname. it is called when the bot gets the command "me"
					//@param $chatId [string] [required] - the ID of chat where we send a message
					//@param $name [string] [required] - the "senderName" property of the message
					public function me($chatId,$name){
						$this->sendMessage($chatId,$name);
					}
					//sends a file. it is called when the bot gets the command "file"
					//@param $chatId [string] [required] - the ID of chat where we send a message
					//@param $format [string] [required] - file format, from the params in the message body (text[1], etc)
					public function file($chatId,$format){
						$availableFiles = array(
						'doc' => 'document.doc',
						'gif' => 'gifka.gif',
						'jpg' => 'jpgfile.jpg',
						'png' => 'pngfile.png',
						'pdf' => 'presentation.pdf',
						'mp4' => 'video.mp4',
						'mp3' => 'mp3file.mp3'
						);

						if(isset($availableFiles[$format])){
						$data = array(
						'chatId'=>$chatId,
						'body'=>'https://domain.com/PHP/'.$availableFiles[$format],
						'filename'=>$availableFiles[$format],
						'caption'=>'Get your file '.$availableFiles[$format]
						);
						$this->sendRequest('sendFile',$data);}
					}

					//sends a voice message. it is called when the bot gets the command "ptt"
					//@param $chatId [string] [required] - the ID of chat where we send a message
					public function ptt($chatId){
						$data = array(
						'audio'=>'https://domain.com/PHP/ptt.ogg',
						'chatId'=>$chatId
						);
						$this->sendRequest('sendAudio',$data);
					}

					//sends a location. it is called when the bot gets the command "geo"
					//@param $chatId [string] [required] - the ID of chat where we send a message
					public function geo($chatId){
						$data = array(
						'lat'=>51.51916,
						'lng'=>-0.139214,
						'address'=>'Ваш адрес',
						'chatId'=>$chatId
						);
						$this->sendRequest('sendLocation',$data);
					}

					//creates a group. it is called when the bot gets the command "group"
					//@param chatId [string] [required] - the ID of chat where we send a message
					//@param author [string] [required] - "author" property of the message
					public function group($author){
						$phone = str_replace('@c.us','',$author);
						$data = array(
						'groupName'=>'Group with the bot PHP',
						'phones'=>array($phone),
						'messageText'=>'It is your group. Enjoy'
						);
						$this->sendRequest('group',$data);
					}

					public function sendMessage($chatId, $text){
						$data = array('chatId'=>$chatId,'body'=>$text);
						$this->sendRequest('message',$data);
					}

					public function sendRequest($method,$data){
						$url = $this->APIurl.$method.'?token='.$this->token;
						if(is_array($data)){ $data = json_encode($data);}
						$options = stream_context_create(['http' => [
						'method'  => 'POST',
						'header'  => 'Content-type: application/json',
						'content' => $data]]);
						$response = file_get_contents($url,false,$options);
						file_put_contents('requests.log',$response.PHP_EOL,FILE_APPEND);
					}
				}
					
					//execute the class when this file is requested by the instance
					new whatsAppBot();
		}
