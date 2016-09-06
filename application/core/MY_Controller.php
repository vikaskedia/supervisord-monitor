<?php
class MY_Controller extends CI_Controller{

	public function _request($server,$method,$request=array()){
		$servers = $this->config->item('supervisor_servers');
		if(!$servers[$server]) die("Invalid server: ".$server);
		
		$config = $servers[$server];

		//print_r($config);

		$this->load->library('xmlrpc',array(),$server);
		$this->{$server}->initialize();
		$this->{$server}->server($config['url'],$config['port']);
		$this->{$server}->method('supervisor.'.$method);
		$this->{$server}->timeout($this->config->item('timeout'));
		
		if(isset($config['username']) && isset($config['password'])){
			$this->{$server}->setCredentials($config['username'], $config['password']);
		}
		$this->{$server}->request($request);

		if(!$this->{$server}->send_request()){
				$response['error'] = $this->{$server}->display_error();
		}else{
				$response = $this->{$server}->display_response();
		}	
		/*
		The response is an array containing all the services and thier state from the supervisor for 1 superversior deamon
		so if there are 10 supervisor daemons this will be called 10 times.
		echo "printing response";
		
		*/
		//print_r($response);
		if( $config['alert-email']!=NULL){
		    for($i=0;$i<sizeof($response);$i++){
                      $currentState = trim($response[$i]['statename']);
		      //echo "The current state is $currentState <br>";		    
		      $comparisonValue = substr_compare($currentState,"RUNNING",0,8);
		      if($comparisonValue != 0){
		        echo $comparisonValue;			  
		        echo "sending alert email";
			echo "Subject: Service " . $response[$i]['name'] . " on server " . $config['url'] . " is down";
		      }
		    }
		
		}
		return $response;
	}

}
