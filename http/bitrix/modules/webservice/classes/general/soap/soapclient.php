<?php

class CSOAPClient
{
    /// The name or IP of the server to communicate with
    var $Server;
    /// The path to the SOAP server
    var $Path;
    /// The port of the server to communicate with.
    var $Port;
    /// How long to wait for the call.
    var $Timeout = 0;
    /// HTTP login for HTTP authentification
    var $Login;
    /// HTTP password for HTTP authentification
    var $Password;
    
    var $SOAPRawRequest;
    var $SOAPRawResponse;
    
    function CSOAPClient( $server, $path = '/', $port = 80 )
    {
        $this->Login = "";
        $this->Password = "";
        $this->Server = $server;
        $this->Path = $path;
        $this->Port = $port;
        if ( is_numeric( $port ) )
            $this->Port = $port;
        elseif( strtolower( $port ) == 'ssl' )
            $this->Port = 443;
        else
            $this->Port = 80;
    }

    /*!
      Sends a SOAP message and returns the response object.
    */
    function send( $request )
    {
    	if ( $this->Port == 443)
    	{
			$this->ErrorString = "<b>Error:</b> CSOAPClient::send() : SSL port on request server no supported by current impl. of SOAPClient.";
			return 0;
    	}
    	
        if ( $this->Port != 443 )
        {
            if ( $this->Timeout != 0 )
            {
                $fp = fsockopen( $this->Server,
                                 $this->Port,
                                 $this->errorNumber,
                                 $this->errorString,
                                 $this->Timeout );
            }
            else
            {
                $fp = fsockopen( $this->Server,
                                 $this->Port,
                                 $this->errorNumber,
                                 $this->errorString );
            }

            if ( $fp == 0 )
            {
                $this->ErrorString = '<b>Error:</b> CSOAPClient::send() : Unable to open connection to ' . $this->Server . '.';
                return 0;
            }

            $payload = $request->payload();

            $authentification = "";
            if ( ( $this->login() != "" ) )
            {
                $authentification = "Authorization: Basic " . base64_encode( $this->login() . ":" . $this->password() ) . "\r\n" ;
            }
            
            $name = $request->name();
            $namespace = $request->get_namespace();
            if ($namespace[strlen($namespace)-1] != "/")
            	$namespace .= "/";            

            $HTTPRequest = "POST " . $this->Path . " HTTP/1.0\r\n" .
                "User-Agent: BITRIX SOAP Client\r\n" .
                "Host: " . $this->Server . "\r\n" .
                $authentification .
                "Content-Type: text/xml\r\n" .
                "SOAPAction: \"" . $request->get_namespace() . $request->name() . "\"\r\n" .
                "Content-Length: " . (defined('BX_UTF') && BX_UTF == 1 && function_exists('mb_strlen') ? mb_strlen($payload, 'latin1') : strlen($payload))  . "\r\n\r\n" .
                $payload;
			
			$this->SOAPRawRequest = $HTTPRequest;
            if ( !fwrite( $fp, $HTTPRequest /*, strlen( $HTTPRequest )*/ ) )
            {
                $this->ErrorString = "<b>Error:</b> could not send the SOAP request. Could not write to the socket.";
                $response = 0;
                return $response;
            }

            $rawResponse = "";
            // fetch the SOAP response
            while ( $data = fread( $fp, 32768 ) )
            {
                $rawResponse .= $data;
            }

            // close the socket
            fclose( $fp );
        }
        
		$this->SOAPRawResponse = $rawResponse;
        $response = new CSOAPResponse();
        $response->decodeStream( $request, $rawResponse );
        return $response;
    }

    function setTimeout( $timeout )
    {
        $this->Timeout = $timeout;
    }

    function setLogin( $login  )
    {
        $this->Login = $login;
    }
    
    function getRawRequest()
    {
    	return $this->SOAPRawRequest;
    }
    
    function getRawResponse()
    {
    	return $this->SOAPRawResponse;
    }

    function login()
    {
        return $this->Login;
    }

    function setPassword( $password  )
    {
        $this->Password = $password;
    }

    function password()
    {
        return $this->Password;
    }
}

?>
