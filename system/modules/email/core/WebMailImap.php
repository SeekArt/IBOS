<?php

namespace application\modules\email\core;

class WebMailImap extends WebMailBase
{

    /**
     * 链接服务器，返回链接对象
     * @param type $host
     * @param type $user
     * @param type $password
     * @param type $authMethod
     */
    public function connect($host, $user, $password, $ssl = false, $port = '', $authMethod = 'plain')
    {
        $this->clearError();
        //strip slashes
        $user = stripslashes($user);
        $password = stripslashes($password);
        $result = false;
        //initialize connection
        $conn = new ICWebMailConnection();
        $conn->error = "";
        $conn->errorNum = 0;
        $conn->selected = "";
        $conn->host = $host;

        //check input
        if (empty($host)) {
            $this->setError("Invalid host");
        }
        if (empty($user)) {
            $this->setError("Invalid user");
        }
        if (empty($password)) {
            $this->setError("Invalid password");
        }
        $error = $this->getError();
        if (!empty($error)) {
            return false;
        }

        //check for SSL
        if ($ssl) {
            $host = "ssl://" . $host;
        }

        //open socket connection
        $conn->fp = @fsockopen($host, $port);
        if ($conn->fp) {
            $line = $this->readLine($conn->fp, 300);
            if (strcasecmp($authMethod, "check") == 0) {
                //check for supported auth methods
                //default to plain text auth
                $authMethod = "plain";
                //check for CRAM-MD5
                fputs($conn->fp, "cp01 CAPABILITY\r\n");
                do {
                    $line = trim(chop($this->readLine($conn->fp, 100)));
                    $a = explode(" ", $line);
                    if ($line[0] == "*") {
                        while (list($k, $w) = each($a)) {
                            if ((strcasecmp($w, "AUTH=CRAM_MD5") == 0) || (strcasecmp($w, "AUTH=CRAM-MD5") == 0)) {
                                $authMethod = "auth";
                            }
                        }
                    }
                } while ($a[0] != "cp01");
            }

            if (strcasecmp($authMethod, "auth") == 0) {
                $conn->message .= "Trying CRAM-MD5\n";
                //do CRAM-MD5 authentication
                fputs($conn->fp, "a000 AUTHENTICATE CRAM-MD5\r\n");
                $line = trim(chop($this->readLine($conn->fp, 1024)));
                if ($line[0] == "+") {
                    $conn->message .= 'Got challenge: ' . htmlspecialchars($line) . "\n";
                    //got a challenge string, try CRAM-5
                    $result = $this->authenticate($conn, $user, $password, substr($line, 2));
                    $conn->message .= "Tried CRAM-MD5: $result \n";
                } else {
                    $conn->message .= 'No challenge (' . htmlspecialchars($line) . "), try plain\n";
                    $auth = "plain";
                }
            }

            if ((!$result) || (strcasecmp($auth, "plain") == 0)) {
                //do plain text auth
                $result = $this->login($conn, $user, $password);
                $conn->message .= "Tried PLAIN: $result \n";
            }
            if (!$result) {
                $this->setError($conn->error);
            }
        } else {
            $this->setError("Could not connect to {$host} at port {$port}");
            return false;
        }

        if ($result) {
            return $conn;
        } else {
            return false;
        }
    }

    public function login(&$conn, $user, $password)
    {
        fputs($conn->fp, "a001 LOGIN {$user} \"{$password}\"\r\n");
        do {
            $line = $this->readReply($conn->fp);
        } while (!$this->startsWith($line, "a001 "));
        $a = explode(" ", $line);
        if (strcmp($a[1], "OK") == 0) {
            $result = $conn->fp;
            $conn->error .= "";
            $conn->errorNum = 0;
        } else {
            $result = false;
            fclose($conn->fp);
            $conn->error .= "Authentication failed (LOGIN):<br>\"" . htmlspecialchars($line) . "\"";
            $conn->errorNum = -2;
        }
        return $result;
    }

    public function fetchHeaderIndex(&$conn, $mailbox, $messageSet, $indexField)
    {
        $c = 0;
        $result = array();
        $fp = $conn->fp;
        if (empty($indexField)) {
            $indexField = "DATE";
        }
        $indexField = strtoupper($indexField);
        $fields_a = array(
            "DATE" => 6,
            "FROM" => 1,
            "TO" => 1,
            "SUBJECT" => 1,
            "UID" => 2,
            "SIZE" => 2,
            "SEEN" => 3,
            "RECENT" => 4,
            "DELETED" => 5
        );

        $mode = $fields_a[$indexField];
        if (!($mode > 0)) {
            return false;
        }

        /*  Do "SELECT" command */
        if (!$this->select($conn, $mailbox)) {
            return false;
        }

        /* FETCH date,from,subject headers */
        if ($mode == 1) {
            $key = "fhi" . ($c++);
            $request = $key . " FETCH {$messageSet} (BODY.PEEK[HEADER.FIELDS ($indexField)])\r\n";
            if (!fputs($fp, $request)) {
                return false;
            }
            do {
                $line = chop($this->readLine($fp, 200));
                $a = explode(" ", $line);
                if (($line[0] == "*") && ($a[2] == "FETCH") && ($line[strlen($line) - 1] != ")")) {
                    $id = $a[1];
                    $str = $line = chop($this->readLine($fp, 300));
                    while ($line[strlen($line) - 1] != ")") {  //caution, this line works only in this particular case
                        $line = chop($this->readLine($fp, 300));
                        if ($line[0] != ")") {
                            if (ord($line[0]) <= 32) {   //continuation from previous header line
                                $str .= " " . trim($line);
                            }
                            if ((ord($line[0]) > 32) || (strlen($line[0]) == 0)) {
                                list($field, $string) = $this->splitHeaderLine($str);
                                if (strcasecmp($field, "date") == 0) {
                                    $result[$id] = $this->strToTime($string);
                                } else {
                                    $result[$id] = strtoupper(str_replace("\"", "", $string));
                                }
                                $str = $line;
                            }
                        }
                    }
                }
            } while (!$this->startsWith($a[0], $key));
        } else if ($mode == 6) {
            $key = "fhi" . ($c++);
            $request = $key . " FETCH {$messageSet} (INTERNALDATE)\r\n";
            if (!fputs($fp, $request)) {
                return false;
            }
            do {
                $line = chop($this->readLine($fp, 200));
                if ($line[0] == "*") {
                    //original: "* 10 FETCH (INTERNALDATE "31-Jul-2002 09:18:02 -0500")"
                    $paren_pos = strpos($line, "(");
                    $foo = substr($line, 0, $paren_pos);
                    $a = explode(" ", $foo);
                    $id = $a[1];
                    $open_pos = strpos($line, "\"") + 1;
                    $close_pos = strrpos($line, "\"");
                    if ($open_pos && $close_pos) {
                        $len = $close_pos - $open_pos;
                        $time_str = substr($line, $open_pos, $len);
                        $result[$id] = strtotime($time_str);
                    }
                } else {
                    $a = explode(" ", $line);
                }
            } while (!$this->startsWith($a[0], $key));
        } else {
            if ($mode >= 3) {
                $field_name = "FLAGS";
            } else if ($indexField == "SIZE") {
                $field_name = "RFC822.SIZE";
            } else {
                $field_name = $indexField;
            }
            /* 			FETCH uid, size, flags		 */
            $key = "fhi" . ($c++);
            $request = $key . " FETCH $messageSet ($field_name)\r\n";
            if (!fputs($fp, $request)) {
                return false;
            }
            do {
                $line = chop($this->readLine($fp, 200));
                $a = explode(" ", $line);
                if (($line[0] == "*") && ($a[2] == "FETCH")) {
                    $line = str_replace("(", "", $line);
                    $line = str_replace(")", "", $line);
                    $a = explode(" ", $line);
                    /*  Caution, bad assumptions, next several lines */
                    $id = $a[1];
                    if ($mode == 2) {
                        $result[$id] = $a[4];
                    } else {
                        $haystack = strtoupper($line);
                        $result[$id] = (strpos($haystack, $indexField) > 0 ? "F" : "N");
                    }
                }
            } while (!$this->startsWith($line, $key));
        }
        return $result;
    }

    public function fetchHeader(&$conn, $mailbox, $id)
    {
        $a = $this->fetchHeaders($conn, $mailbox, $id);
        if (is_array($a)) {
            return $a[$id];
        } else {
            return false;
        }
    }

    public function fetchHeaders(&$conn, $mailbox, $messageSet)
    {
        $c = 0;
        $result = array();
        $fp = $conn->fp;

        /*  Do "SELECT" command */
        if (!$this->select($conn, $mailbox)) {
            return false;
        }

        /* FETCH date,from,subject headers */
        $key = "fh" . ($c++);
        $request = $key . " FETCH {$messageSet} (BODY.PEEK[HEADER.FIELDS (DATE FROM TO SUBJECT REPLY-TO CC CONTENT-TRANSFER-ENCODING CONTENT-TYPE MESSAGE-ID)])\r\n";

        if (!fputs($fp, $request)) {
            return false;
        }
        do {
            $line = chop($this->readLine($fp, 200));
            $a = explode(" ", $line);
            if (($line[0] == "*") && ($a[2] == "FETCH")) {
                $id = $a[1];
                $result[$id] = new ICWebMailBasicHeader();
                $result[$id]->id = $id;
                $result[$id]->subject = "";
                /*
                  Start parsing headers.  The problem is, some header "lines" take up multiple lines.
                  So, we'll read ahead, and if the one we're reading now is a valid header, we'll
                  process the previous line.  Otherwise, we'll keep adding the strings until we come
                  to the next valid header line.
                 */
                $i = 0;
                $lines = array();
                do {
                    $line = chop($this->readLine($fp, 300));
                    if (!empty($line) && ord($line[0]) <= 32) {
                        $lines[$i] .= (empty($lines[$i]) ? "" : "\n") . trim(chop($line));
                    } else {
                        $i++;
                        $lines[$i] = trim(chop($line));
                    }
                } while (!empty($line) && $line[0] != ")");

                //process header, fill iilBasicHeader obj.
                //	initialize
                /* if ( isset( $headers ) && is_array( $headers ) ) {
                  reset( $headers );
                  while ( list($k, $bar) = each( $headers ) ) {
                  $headers[$k] = "";
                  }
                  } */
                //	create array with header field:data
                $headers = array();
                while (list($lines_key, $str) = each($lines)) {
                    list($field, $string) = $this->splitHeaderLine($str);
                    if (!empty($field) && !empty($string)) {
                        $field = strtolower($field);
                        $headers[$field] = $string;
                    }
                }
                $result[$id]->date = $headers["date"];
                $result[$id]->timestamp = $this->strToTime($headers["date"]);
                $result[$id]->from = $headers["from"];
                $result[$id]->to = isset($headers["to"]) ? str_replace("\n", " ", $headers["to"]) : '';
                $result[$id]->subject = isset($headers["subject"]) ? str_replace("\n", " ", $headers["subject"]) : '';
                $result[$id]->replyto = isset($headers["reply-to"]) ? str_replace("\n", " ", $headers["reply-to"]) : '';
                $result[$id]->cc = isset($headers["cc"]) ? str_replace("\n", " ", $headers["cc"]) : '';
                $result[$id]->encoding = isset($headers["content-transfer-encoding"]) ? str_replace("\n", " ", $headers["content-transfer-encoding"]) : '';
                $result[$id]->ctype = isset($headers["content-type"]) ? str_replace("\n", " ", $headers["content-type"]) : '';
//				list($result[$id]->ctype, $foo) = explode( ";", $headers["content-type"] );
                $messageID = isset($headers['message-id']) ? $headers["message-id"] : 0;
                $messageID && $result[$id]->messageID = substr(substr($messageID, 1), 0, strlen($messageID) - 2);
            }
        } while (strcmp($a[0], $key) != 0);

        /*
          FETCH uid, size, flags
          Sample reply line: "* 3 FETCH (UID 2417 RFC822.SIZE 2730 FLAGS (\Seen \Deleted))"
         */
        $command_key = "fh" . ($c++);
        $request = $command_key . " FETCH {$messageSet} (UID RFC822.SIZE FLAGS INTERNALDATE)\r\n";
        if (!fputs($fp, $request)) {
            return false;
        }
        do {
            $line = chop($this->readLine($fp, 200));
            if ($line[0] == "*") {
                //get outter most parens
                $open_pos = strpos($line, "(") + 1;
                $close_pos = strrpos($line, ")");
                if ($open_pos && $close_pos) {
                    //extract ID from pre-paren
                    $pre_str = substr($line, 0, $open_pos);
                    $pre_a = explode(" ", $line);
                    $id = $pre_a[1];

                    //get data
                    $len = $close_pos - $open_pos;
                    $str = substr($line, $open_pos, $len);

                    //swap parents with quotes, then explode
                    $str = preg_replace("[()]", "\"", $str);
                    $a = $this->explodeQuotedString(" ", $str);
                    $flags_str = $time_str = '';
                    //did we get the right number of replies?
                    $parts_count = count($a);
                    if ($parts_count >= 8) {
                        for ($i = 0; $i < $parts_count; $i = $i + 2) {
                            if (strcasecmp($a[$i], "UID") == 0) {
                                $result[$id]->uid = $a[$i + 1];
                            } else if (strcasecmp($a[$i], "RFC822.SIZE") == 0) {
                                $result[$id]->size = $a[$i + 1];
                            } else if (strcasecmp($a[$i], "INTERNALDATE") == 0) {
                                $time_str = $a[$i + 1];
                            } else if (strcasecmp($a[$i], "FLAGS") == 0) {
                                $flags_str = $a[$i + 1];
                            }
                        }

                        // process flags
                        $flags_str = preg_replace("[\\\"]", "", $flags_str);
                        $flags_a = explode(" ", $flags_str);
                        //echo "<!-- ID: $id FLAGS: ".implode(",", $flags_a)." //-->\n";

                        $result[$id]->seen = false;
                        $result[$id]->recent = false;
                        $result[$id]->deleted = false;
                        $result[$id]->answered = false;
                        if (is_array($flags_a)) {
                            reset($flags_a);
                            while (list($key, $val) = each($flags_a)) {
                                if (strcasecmp($val, "Seen") == 0) {
                                    $result[$id]->seen = true;
                                } else if (strcasecmp($val, "Deleted") == 0) {
                                    $result[$id]->deleted = true;
                                } else if (strcasecmp($val, "Recent") == 0) {
                                    $result[$id]->recent = true;
                                } else if (strcasecmp($val, "Answered") == 0) {
                                    $result[$id]->answered = true;
                                }
                            }
//							$result[$id]->flags = $flags;
                        }
                        if (!empty($time_str)) {
                            //get timezone
                            $time_str = substr($time_str, 0, -1);
                            $time_zone_str = substr($time_str, -5); //extract timezone
                            $time_str = substr($time_str, 1, -6); //remove quotes
                            $time_zone = (int)substr($time_zone_str, 1, 2); //get first two digits
                            if ($time_zone_str[0] == "-") {
                                $time_zone = $time_zone * -1; //minus?
                            }
                            //calculate timestamp
                            $timestamp = strtotime($time_str); //return's server's time
                            $timestamp -= $time_zone * 3600; //compensate for tz, get GMT
                            $result[$id]->timestamp = $timestamp;
                        }
                    } else {
                        //echo "<!-- ERROR: $id : $str //-->\n";
                    }
                }
            }
        } while (strpos($line, $command_key) === false);

        return $result;
    }

    public function fetchPartHeader(&$conn, $mailbox, $id, $part)
    {
        $fp = $conn->fp;
        $result = false;
        if (($part == 0) || (empty($part))) {
            $part = "HEADER";
        } else {
            $part .= ".MIME";
        }
        if ($this->select($conn, $mailbox)) {
            $key = "fh";
            $request = $key . " FETCH {$id} (BODY.PEEK[{$part}])\r\n";
            if (!fputs($fp, $request)) {
                return false;
            }
            do {
                $line = chop($this->readLine($fp, 200));
                if (!empty($line)) {
                    $a = explode(" ", $line);
                    if (($line[0] == "*") && ($a[2] == "FETCH") && ($line[strlen($line) - 1] != ")")) {
                        $line = $this->readLine($fp, 300);
                        while (chop($line) != ")") {
                            $result .= $line;
                            $line = $this->readLine($fp, 300);
                        }
                    }
                }
            } while (strcmp($a[0], $key) != 0);
        }

        return $result;
    }

    public function fetchPartBody(&$conn, $mailbox, $id, $part)
    {
        return $this->handlePartBody($conn, $mailbox, $id, $part, 1);
    }

    public function printPartBody(&$conn, $mailbox, $id, $part)
    {
        return $this->handlePartBody($conn, $mailbox, $id, $part, 2);
    }

    public function printBase64Body(&$conn, $mailbox, $id, $part)
    {
        return $this->handlePartBody($conn, $mailbox, $id, $part, 3);
    }

    public function fetchStructureString(&$conn, $folder, $id)
    {
        $fp = $conn->fp;
        $result = false;
        if ($this->select($conn, $folder)) {
            $key = "F1247";
            if (fputs($fp, "$key FETCH $id (BODYSTRUCTURE)\r\n")) {
                do {
                    $line = chop($this->readLine($fp, 5000));
                    if (!empty($line)) {
                        if ($line[0] == "*") {
                            if (preg_match('/\}$/', $line)) {
                                preg_match('/(.+)\{([0-9]+)\}/', $line, $match);
                                $result = $match[1];
                                do {
                                    $line = chop($this->readLine($fp, 100));
                                    if (!preg_match("/^$key/", $line)) {
                                        $result .= $line;
                                    } else {
                                        $done = true;
                                    }
                                } while (!$done);
                            } else {
                                $result = $line;
                            }
                            list($pre, $post) = explode("BODYSTRUCTURE ", $result);
                            $result = substr($post, 0, strlen($post) - 1);  //truncate last ')' and return
                        }
                    }
                } while (!preg_match("/^{$key}/", $line));
            }
        }
        return $result;
    }

    public function select(&$conn, $mailbox)
    {
        $fp = $conn->fp;
        if (empty($mailbox)) {
            return false;
        }
        if (strcmp($conn->selected, $mailbox) == 0) {
            return true;
        }

        if (fputs($fp, "sel1 SELECT \"$mailbox\"\r\n")) {
            do {
                $line = chop($this->readLine($fp, 300));
            } while (!$this->startsWith($line, "sel1"));
            $a = explode(" ", $line);
            if (strcasecmp($a[1], "OK") == 0) {
                $conn->selected = $mailbox;
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    public function countMessages(&$conn, $mailbox)
    {
        $num = -1;
        $fp = $conn->fp;
        if (fputs($fp, "cm1 SELECT \"{$mailbox}\"\r\n")) {
            do {
                $line = chop($this->readLine($fp, 300));
                $a = explode(" ", $line);
                if ((count($a) == 3) && (strcasecmp($a[2], "EXISTS") == 0)) {
                    $num = (int)$a[1];
                }
            } while (!$this->startsWith($a[0], "cm1"));
        }
        return $num;
    }

    public function authenticate(&$conn, $user, $pass, $encChallenge)
    {
        // initialize ipad, opad
        for ($i = 0; $i < 64; $i++) {
            $ipad .= chr(0x36);
            $opad .= chr(0x5C);
        }
        // pad $pass so it's 64 bytes
        $padLen = 64 - strlen($pass);
        for ($i = 0; $i < $padLen; $i++) {
            $pass .= chr(0);
        }
        // generate hash
        $hash = md5($this->_XOR($pass, $opad) . pack("H*", md5($this->_XOR($pass, $ipad) . base64_decode($encChallenge))));
        // generate reply
        $reply = base64_encode($user . " " . $hash);
        // send result, get reply
        fputs($conn->fp, $reply . "\r\n");
        $line = $this->readLine($conn->fp, 1024);

        // process result
        if ($this->parseResult($line) == 0) {
            $conn->error .= "";
            $conn->errorNum = 0;
            return $conn->fp;
        } else {
            $conn->error .= "Authentication failed (AUTH): <br>\"" . htmlspecialchars($line) . "\"";
            $conn->errorNum = -2;
            return false;
        }
    }

    /**
     *
     * @param type $string
     * @return int
     */
    public function parseResult($string)
    {
        $a = explode(" ", $string);
        if (count($a) > 2) {
            if (strcasecmp($a[1], "OK") == 0) {
                return 0;
            } else if (strcasecmp($a[1], "NO")) {
                return -1;
            } else if (strcasecmp($a[1], "BAD")) {
                return -2;
            }
        } else {
            return -3;
        }
    }

    // check if $string starts with $match
    public function startsWith($string, $match)
    {
        if ($string[0] == $match[0]) {
            $pos = strpos($string, $match);
            if ($pos === false) {
                return false;
            } else if ($pos == 0) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    protected function handlePartBody(&$conn, $mailbox, $id, $part, $mode)
    {
        /* modes:
          1: return string
          2: print
          3: base64 and print
         */
        $fp = $conn->fp;
        $result = false;
        if (($part == 0) || (empty($part))) {
            $part = "TEXT";
        }

        if ($this->select($conn, $mailbox)) {
//			$reply_key = "* " . $id;
            // format request
            $key = "ftch" . " ";
            $request = $key . "FETCH {$id} (BODY.PEEK[{$part}])\r\n";
            // send request
            if (!fputs($fp, $request)) {
                return false;
            }
            // receive reply line
            do {
                $line = chop($this->readLine($fp, 1000));
                $a = explode(" ", $line);
            } while ($a[2] != "FETCH");
            $len = strlen($line);
            if ($line[$len - 1] == ")") {
                //one line response, get everything between first and last quotes
                $from = strpos($line, "\"") + 1;
                $to = strrpos($line, "\"");
                $len = $to - $from;
                if ($mode == 1) {
                    $result = substr($line, $from, $len);
                } else if ($mode == 2) {
                    echo substr($line, $from, $len);
                } else if ($mode == 3) {
                    echo base64_decode(substr($line, $from, $len));
                }
            } else if ($line[$len - 1] == "}") {
                //multi-line request, find sizes of content and receive that many bytes
                $from = strpos($line, "{") + 1;
                $to = strrpos($line, "}");
                $len = $to - $from;
                $sizeStr = substr($line, $from, $len);
                $bytes = (int)$sizeStr;
                $received = 0;
                while ($received < $bytes) {
                    $remaining = $bytes - $received;
                    $line = $this->readLine($fp, 1024);
                    $len = strlen($line);
                    if ($len > $remaining) {
                        substr($line, 0, $remaining);
                    }
                    $received += strlen($line);
                    if ($mode == 1) {
                        $result .= chop($line) . "\n";
                    } else if ($mode == 2) {
                        echo chop($line) . "\n";
                        flush();
                    } else if ($mode == 3) {
                        echo base64_decode($line);
                        flush();
                    }
                }
            }
            // read in anything up until 'til last line
            do {
                $line = $this->readLine($fp, 1024);
            } while (!$this->startsWith($line, $key));

            // flag as "seen"
            if ($result) {
                return substr($result, 0, strlen($result) - 1);
            } else {
                return false;
            }
        } else {
            echo "Select failed.";
        }

        if ($mode == 1) {
            return $result;
        } else {
            return $received;
        }
    }

    public function close(&$conn)
    {
        if (fputs($conn->fp, "I LOGOUT\r\n")) {
            fgets($conn->fp, 1024);
            fclose($conn->fp);
        }
    }

}
