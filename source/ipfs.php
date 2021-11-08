<?php

namespace g33kme\ipfs;

class IPFS {

    /**
     * Let's construct!
     */
    public function __construct() {

    }

    public static function serverAlive() {
        $data = IPFS::version();
        return !empty($data) ? true : false;
    }

    public static function version() {

        return IPFS::query(array(
            'method' => 'POST',
            'url' => '/version'
        ));

    }

    public static function add($x=false) {

        return IPFS::query(array(
            'method' => 'POST',
            'data' => false,
            'file' => $x['file'],
            'url' => '/add',
            'jsonencode' => false,
            'httpheader' => array('application/octet-stream')

        ));

    }

    public static function query($x=false) {

        //GENERAL::ppr($x);

        $x['method'] = !empty($x['method']) ? $x['method'] : 'POST';
        $x['url'] = IPFS_API.$x['url'];

        $x['jsonencode'] = !isset($x['jsonencode']) ? true : $x['jsonencode'];
        $x['httpheader'] = !empty($x['httpheader']) ? $x['httpheader'] : array('Accept: application/json', 'Content-type: application/json');

        $response = GENERAL::curl(array(

            'printcurl' => false,
            'debug' => false,
            'info' => false,

            'method' => $x['method'],
            'url' => $x['url'],
            'data' => $x['data'],
            'file' => $x['file'],

            'jsonencode' => $x['jsonencode'],
            'httpheader' => $x['httpheader']
        ));

        //echo 'real response: '.$response;
        return json_decode($response, 1);

    }

    /**
	 * cURL Request
     */
	public static function curl($x=false) {

        if($x['printcurl']) {
            print_r($x);
        }

		$curl = curl_init();

		if($x['debug']) {
		     $fp = fopen('./curl.log', 'w');
             curl_setopt($curl, CURLOPT_VERBOSE, 1);
             curl_setopt($curl, CURLOPT_STDERR, $fp);
		}


		//Async
		if($x['async']) {

			curl_setopt($curl, CURLOPT_FRESH_CONNECT, true);

			//Do not set TIMEOUT_MS to short, script should be of course executed ;-)
			curl_setopt($curl, CURLOPT_NOSIGNAL, 1);
			curl_setopt($curl, CURLOPT_TIMEOUT_MS, 337); //l33t ^^
		}


		//Maybe create some file for sending
		//Attention! here you set the param key to "file" for sending to a remote API, you may need another key
		if(!empty($x['file'])) {
			if(is_file($x['file'])) {
				if(is_array($x['file'])) {
					//TODO

				} else {
					//Create curl file and add to data
					//echo 'File: '.$x['file'];

					$x['filemime'] = !empty($x['filemime']) ? $x['filemime'] : 'application/octet-stream';
					$x['filename'] = !empty($x['filename']) ? $x['filename'] : basename($x['file']);

					$cfile = curl_file_create($x['file'], $x['filemime'], $x['filename']);

					//maybeyou have to change the name instead of "file" ...
					$x['data']['file'] = $cfile;
				}
			}
		}

		//To set any key for file
		//Here you can set any key for file
		if(!empty($x['createfile'])) {
			if(is_file($x['createfile']['value'])) {

                $cfile = curl_file_create($x['createfile']['value'], $x['createfile']['mimetype']);
                $x['data'][$x['createfile']['key']] = $cfile;
            }
		}


		switch($x['method']) {

			case "POST":

				curl_setopt($curl, CURLOPT_POST, true);

				if(!empty($x['data'])) {

					//JSON Encode for REST API
					if($x['jsonencode']) {

                        $dataJsonEncoded = json_encode($x['data']);

						curl_setopt($curl, CURLOPT_POSTFIELDS, $dataJsonEncoded);

						if($x['printcurl']) {
                            echo $dataJsonEncoded;
                        }


					} else {
						curl_setopt($curl, CURLOPT_POSTFIELDS, $x['data']);

						if($x['printcurl']) {
                            print_r($x['data']);
                        }

					}
				}
			break;

			case "PUT":
				curl_setopt($curl, CURLOPT_PUT, 1);
			break;

			case "DELETE":
				curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'DELETE');
			break;

			default:
				if(!empty($x['data'])) {
					$x['url'] = sprintf("%s?%s", $x['url'], http_build_query($x['data']));
				}
			break;
		}

		// Optional Authentication:
		if(!empty($x['httpauthuser'])) {
			curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
			curl_setopt($curl, CURLOPT_USERPWD, $x['httpauthuser'].':'.$x['httpauthpass']);
		}

		if(!empty($x['httpheader'])) {
			curl_setopt($curl, CURLOPT_HTTPHEADER, $x['httpheader']);
		}

		curl_setopt($curl, CURLOPT_URL, $x['url']);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);

		$response = curl_exec($curl);
		$errno    = curl_errno($curl);
		$errmsg   = curl_error($curl);

		if($x['info']) {
		    $info = curl_getinfo($curl);
            print_r($info);
		}

		curl_close($curl);

		// ensure the request succeeded
		if ($errno != 0) {
			//Do not die on error!
			//die('Error: '.$errno.$errmsg);
			//echo 'curl error: '.$errno.', '.$errmsg;
		}

		//echo 'RealResponse: '.$response;
		return $response;

	}

	public static function cleanStop($stop) {
		return array_unique(array_values(array_filter($stop)));
	}

    public static function randHash($lng, $numericOnly=false) {

        mt_srand(crc32(microtime()));

        if($numericOnly) {
            $b = "1234567890";
        } else {
            $b = "abcdefghijklmnpqrstuvwxyz1234567890";
        }

       $str_lng = strlen($b)-1;
       $rand= "";

       for($i=0;$i<$lng;$i++)
          $rand.= $b{mt_rand(0, $str_lng)};

       return $rand;
    }

    public static function cleanRequest() {

		$done = array();
		foreach($_REQUEST as $key => $value) {
			if(is_array($value)) {

				foreach($value as $k => $v) {

					$valueClean = trim(strip_tags($v));
					$valueClean = addslashes($valueClean);
					$done[$k] = $valueClean;
				}

			} else {

				$valueClean = trim(strip_tags($value));
				$valueClean = addslashes($valueClean);
				$done[$key] = $valueClean;

			}
		}
		return $done;

	}

}

?>