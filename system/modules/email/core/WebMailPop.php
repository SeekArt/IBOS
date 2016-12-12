<?php

namespace application\modules\email\core;

use application\core\utils\File;

class WebMailPop extends WebMailBase
{

    /**
     * email test
     * @param string $host
     * @param string $user
     * @param string $pass
     * @param boolean $ssl
     * @return boolean
     */
    protected function validationEmail($host, $user, $pass, $ssl = false)
    {
        //防止超时
        set_time_limit(120);
        if ($ssl) {
            $fp = @fsockopen($host, 995, $errno, $errstr);
        } else {
            $fp = @fsockopen($host, 110, $errno, $errstr);
        }

        if (!$fp) {
            return false;
        } else {
            stream_set_blocking($fp, 1);
            $trash = fgets($fp);
            unset($trash);
            fwrite($fp, "USER $user\r\n");
            $str = fgets($fp, 128);
            if (preg_match("/^\+OK/", $str)) {
                fwrite($fp, "PASS $pass\r\n");
                //错误时有可能超时，所以设置最长运行时间为120秒
                $passx = fgets($fp, 128);
                if (preg_match("/^\+OK(.+)/", $passx)) {
                    $auth = true;
                } else {
                    $auth = false;
                }
            } else {
                $auth = false;
            }
            fwrite($fp, "QUIT\r\n");
            fclose($fp);
            file_put_contents('auth.txt', var_export($auth, true));
            return $auth;
        }
    }

    public function connect($host, $user, $password, $ssl = false, $port = '', $authMethod = 'plain')
    {
        $this->clearError();
        $result = false;
        //strip slashes
        $user = stripslashes($user);
        $password = stripslashes($password);

        //check for SSL
        if ($ssl) {
            $host = "ssl://" . $host;
        }
        if (empty($port)) {
            $port = 110;
        }

        //initialize connection object
        $conn = new ICWebMailConnection();
        $conn->error = "";
        $conn->errorNum = 0;
        $conn->login = $user;
        $conn->password = $password;
        $conn->host = $host;
        $conn->selected = "";

        /**
         * email test
         */
//        return $this->validationEmail($host, $user, $password, $ssl);
        //initiate connection
        $conn->fp = @fsockopen($host, $port);
        if ($conn->fp) {
            $this->log("Socket connection established");
            $line = $this->readLine($conn->fp, 300); // "+OK QQMail POP3 Server v1.0 Service Ready(QQMail v2.0)"
            //check for time stamp
            //it's the last "word" and has the form:
            //    <somestring>
            $sig_a = explode(" ", chop($line)); // 8个
            $last = count($sig_a) - 1; // 7
            $timestamp = $sig_a[$last]; // v2.0)
            //echo "<!-- last word: \"$timestamp\" //-->\n";
            $lastLetter = strlen($timestamp) - 1; // 4
            if (($timestamp[0] == "<") && ($timestamp[$lastLetter] == ">")) {
                $tryApop = true;
            } else { // 走这
                $tryApop = false;
                $timestamp = "";
            }

            if ($authMethod != "plain") { // 不走
                fputs($conn->fp, "AUTH\r\n");
                $line = $this->readLine($conn->fp, 300);
                if ($line[0] == "+") {
                    //auth command succeeded
                    //get list of auth methods
                    do {
                        $line = $this->readLine($conn->fp, 300);
                        $newAuth = strtolower(chop($line));
                        $authMethods[$newAuth] = 1;
                    } while ($line[0] != ".");
                    //echo "<!-- got auth methods //-->\n";
                    if ($authMethod == "check") {
                        $authMethod = "auth";
                    }
                } else {
                    //auth command failed
                    //revert to plain text
                    $authMethod = "plain";
                }
            }

            //try AUTH CRAM-MD5
            if (($authMethod == "auth") && ($authMethods["cram-md5"])) { // 不走
                //echo "<!-- doing CRAM-MD5 //-->\n";
                $result = $this->cramMD5($conn, $user, $password);
                $conn->message .= "CRAM_MD5: $result\n";
            }

            // try APOP?
            if ((!$result) && ($tryApop) && ($timestamp) && $authMethod != "plain") { // 不走
                $result = $this->APOP($conn, $user, $password, $timestamp);
                $conn->message .= "\nAPOP: $tryApop $result\n";
            }

            if (!$result) {
                //do plain auth
                fputs($conn->fp, "USER $user\r\n");
                $line = trim(chop($this->readLine($conn->fp, 1024))); // +ok
                if ($this->startsWith($line, "+OK")) {
                    fputs($conn->fp, "PASS $password\r\n");
                    $line = trim(chop($this->readLine($conn->fp, 1024)));
                    if ($this->startsWith($line, "+OK")) {
                        $result = true; // 到这
                    } else {
                        $this->setError(htmlspecialchars($line));
                        $this->close($conn);
                        return false;
                    }
                } else {
                    $this->setError("Unknown user: {$user} \"" . htmlspecialchars($line) . "\"<br>Messages:" . $conn->message);
                    $this->close($conn);
                    return false;
                }
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

    public function countMessages(&$conn, $mailbox)
    {
        $num = -1;
        $fp = $conn->fp;
        if (@fputs($fp, "STAT\r\n")) {
            $line = chop(fgets($fp, 300));
            $a = explode(" ", $line);
            $num = (int)$a[1];
//			$size = (int) $a[2];
        }
        return $num;
    }

    public function fetchHeader(&$conn, $mailbox, $id)
    {
        $c = 0;
        $fp = $conn->fp;

        $index = $this->fList($fp);

        fputs($fp, "TOP {$id} 0\r\n");
        $line = fgets($fp, 128); // +ok
        if ($line[0] == "+") {
            //initialize new iilBasicHeader object
            $result = new ICWebMailBasicHeader();
            $result->size = $index[$id];
            $result->id = $id;

            //fetch header into array
            do {
                $line = chop($this->readLine($fp, 300));
                if (strlen($line) > 2) {
                    if (ord($line[0]) <= 32) {
                        $lines[$c] .= (empty($lines[$c]) ? "" : "\n") . trim($line);
                    } else {
                        $c++;
                        $lines[$c] = $line;
                    }
                }
            } while (isset($line[0]) && $line[0] != ".");

            //process header, fill iilBasicHeader obj.
            $numlines = count($lines);
            for ($i = 0; $i < $numlines; $i++) {
                if (!isset($lines[$i])) {
                    continue;
                }
                //echo $lines[$i]."<br>\n";
                list($field, $string) = $this->splitHeaderLine($lines[$i]);
                if (strcasecmp($field, "date") == 0) {
                    $result->date = $string;
                    $result->timestamp = $this->strToTime($string);
                } else if (strcasecmp($field, "from") == 0) {
                    $result->from = str_replace("\n", " ", $string);
                } else if (strcasecmp($field, "to") == 0) {
                    $result->to = $string;
                } else if (strcasecmp($field, "subject") == 0) {
                    $result->subject = str_replace("\n", "", $string);
                } else if (strcasecmp($field, "reply-to") == 0) {
                    $result->replyto = $string;
                } else if (strcasecmp($field, "cc") == 0) {
                    $result->cc = str_replace("\n", " ", $string);
                } else if (strcasecmp($field, "Content-Transfer-Encoding") == 0) {
                    $result->encoding = $string;
                } else if (strcasecmp($field, "message-id") == 0) {
                    $result->messageID = substr(substr($string, 1), 0, strlen($string) - 2);
                }
            }
            return $result;
        } else {
            return false;
        }
    }

    public function fetchHeaders(&$conn, $mailbox, $messageSet)
    {
        if (strpos($messageSet, ":") > 0) {
            $a = explode(":", $messageSet);
            $from_i = (int)$a[0];
            $to_i = (int)$a[1];
        } else if (strpos($messageSet, ",") > 0) {
            $a = explode(",", $messageSet);
            $n = count($a);
            $from_i = (int)$a[0];
            $to_i = (int)$a[$n - 1];
        } else {
            $from_i = $to_i = $messageSet;
        }

        $fp = $conn->fp;
        $index = $this->fList($fp);
        $c = 0;
        $lines = array();
        for ($id = $from_i; (($id <= $to_i) && ($fp)); $id++) {
            //echo "[".microtime().":begin $id]"; flush();
            fputs($fp, "TOP $id 0\r\n");
            $line = fgets($fp, 128);

            //echo "[".microtime().":requested]"; flush();
            //echo "Requested $id ..."; flush();
            if ($line[0] == "+") {
                //initialize new iilBasicHeader object
                $result[$id] = new ICWebMailBasicHeader();
                $result[$id]->size = $index[$id];
                $result[$id]->id = $id;
                $result[$id]->subject = "";
                $result[$id]->seen = false;
                $result[$id]->recent = false;
                $result[$id]->deleted = false;
                $result[$id]->answered = false;
                //fetch header into array
                do {
                    socket_set_timeout($fp, 10);
                    $line = chop($this->readLine($fp, 300));
                    if (strlen($line) > 2) {
                        if (!empty($line) && ord($line[0]) <= 32) {
                            $lines[$c] .= (empty($lines[$c]) ? "" : "\n") . trim($line);
                        } else {
                            $c++;
                            $lines[$c] = $line;
                        }
                    }
                } while (!empty($line) && ($line[0] != ".") && ($fp));
                //	initialize
                if (isset($header_fields) && is_array($header_fields)) {
                    reset($header_fields);
                    while (list($k, $bar) = each($header_fields)) {
                        $header_fields[$k] = "";
                    }
                }
                //	create array with header field:data
//				$numlines = count( $lines );
                while (list($k, $string) = each($lines)) {
                    $pos = strpos($string, ":");
                    if ($pos > 0) {
                        $field = strtolower(substr($string, 0, $pos));
                        $string = substr($string, $pos + 1);
                    } else {
                        $field = "";
                    }
                    $header_fields[$field] = $string;
                }

                //	fill in object
                $result[$id]->date = $header_fields["date"];
//				$result[$id]->timestamp = strtotime( $result[$id]->date );
                $result[$id]->timestamp = $this->strToTime($result[$id]->date);
                $result[$id]->from = $header_fields["from"];
                $result[$id]->to = isset($header_fields["to"]) ? $header_fields["to"] : '';
                $result[$id]->subject = isset($header_fields["subject"]) ? $header_fields["subject"] : '';
                $result[$id]->replyto = isset($header_fields["reply-to"]) ? $header_fields["reply-to"] : '';
                $result[$id]->cc = isset($header_fields["cc"]) ? $header_fields["cc"] : '';
                $messageID = isset($header_fields['message-id']) ? $header_fields["message-id"] : 0;
                $messageID && $result[$id]->messageID = substr(substr($messageID, 1), 0, strlen($messageID) - 2);
                $result[$id]->encoding = isset($header_fields["content-transfer-encoding"]) ? $header_fields["content-transfer-encoding"] : '';
                $result[$id]->ctype = isset($header_fields["content-type"]) ? $header_fields["content-type"] : '';
            }
        }
        return $result;
    }

    protected function fList($fp)
    {
        fputs($fp, "LIST\r\n");
        $line = fgets($fp, 128);
        if ($line[0] == "+") {
            do {
                $line = fgets($fp, 128);
                if ($line[0] != ".") {
                    $a = explode(" ", $line);
                    $id = (int)$a[0];
                    $size = (int)$a[1];
                    $index[$id] = $size;
                }
            } while ($line[0] != ".");
        }
        return $index;
    }

    public function fetchHeaderIndex(&$conn, $mailbox, $messageSet, $indexField)
    {
        return false;
    }

    public function select(&$conn, $mailbox)
    {
        return true;
    }

    public function fetchPartBody(&$conn, $mailbox, $id, $part)
    {
        return $this->handlePartBody($conn, $id, $part, "FetchBody");
    }

    public function printPartBody(&$conn, $mailbox, $id, $part)
    {
        return $this->handlePartBody($conn, $id, $part, "PrintBody");
    }

    public function printBase64Body(&$conn, $mailbox, $id, $part)
    {
        return $this->handlePartBody($conn, $id, $part, "Base64Decode");
    }

    public function actionPrintBody($line)
    {
        echo chop($line) . "\n";
        flush();
        return "";
    }

    public function actionFetchBody($line)
    {
        return chop($line) . "\n";
    }

    public function actionBase64Decode($line)
    {
        echo base64_decode(chop($line));
        flush();
        return "";
    }

    public function actionFetchHeader($line)
    {
        return "";
    }

    protected function handlePartBody(&$conn, $id, $part, $action)
    {
        $this->openMessage($conn, $id);
        //echo "Message opened\n"; flush();
        if (($conn->cacheMode == "x") || ($conn->cacheMode == "w")) {
            $fp = $conn->fp;
            fputs($fp, "RETR {$id}\r\n");
            $line = fgets($fp, 100);
            if ($line[0] != "+") {
                $conn->errorNum = -10;
                $conn->error = "POP3 error:" . $line;
            }
        }
        if ($conn->fp) {
            $result = $this->fetchBodyPart($conn, "", $line, $part, $part_blah, $action, $total_size, $bytes);
        } else {
            $conn->error = "Bad fp";
        }
        $this->closeMessage($conn);
        return $result;
    }

    protected function fetchBodyPart(&$conn, $boundary, &$last_line, $the_part, &$part, $action, $bytes_total, &$bytes_read)
    {
        if ($the_part == 0) {
            $the_part = "";
        }
        $original_boundary = $boundary;
        $raw_header = "";
        $raw_text = "";

        if ($conn->cacheMode == "r") {
            $fp = $conn->cacheFP;
        } else {
            $fp = $conn->fp;
        }
        // read headers from file
        $lines = array();
        $count = 0;
        do {
            $line = $this->readLine($conn);
            $bytes_read += strlen($line);
            $raw_header .= $line;
            $line = chop($line);
            if (!empty($line)) {
                $c = 0;
                if (ord($line[0]) <= 32) {
                    $lines[$count] .= " " . trim($line);
                } else {
                    $count++;
                    $lines[$count] = $line;
                }
            }
        } while (!empty($line));
        if ((strcmp($part, $the_part) == 0) && (strcmp($action, "FetchHeader") == 0)) {
            $str = $raw_header;
        }
        // parse header into associative array
        $header = $this->contentHeaderArray($lines);
        // generate bodystructure string(s)
        if (strcasecmp($header["content-type"]["major"], "multipart") == 0) {
            $params = $header["content-type"]["parameters"];
            while (list($k, $v) = each($params)) {
                if (strcasecmp($v, "\"boundary\"") == 0) {
                    $boundary = "--" . str_replace("\"", "", $params[$k + 1]);
                }
            }
            do {
                $line = $this->readLine($conn);
                $bytes_read += strlen($line);
            } while (!$this->startsWith($line, $boundary));
            //parse body parts
            $part_num = 0;
            do {
                $part_num++;
                $next_part = $part . (!empty($part) ? "." : "") . $part_num;
                $str .= $this->fetchBodyPart($conn, $boundary, $last_line, $the_part, $next_part, $action, $bytes_total, $bytes_read);
                $end = (((strlen($last_line) - strlen($boundary)) > 0) || (chop($last_line) == "."));
            } while ((!$end) && (!feof($fp)) && (chop($last_line) != "."));

            //read up to next message boundary
            if (chop($last_line) != ".") {
                do {
                    $line = $this->readLine($conn);
                    $bytes_read += strlen($line);
                    $end = (($this->startsWith($line, $original_boundary)) || (chop($last_line) == "."));
                } while ((!$end) && (!feof($fp)) && (chop($line) != "."));
                $last_line = chop($line);
            }
        } else if (strcasecmp($header["content-type"]["major"], "message") == 0) {
            //read blank lines (up to and including first line, which hopefully isn't important)
            do {
                $line = $this->readLine($conn);
            } while ($this->startsWith($line, "\n"));
            $str .= $this->fetchBodyPart($conn, $boundary, $last_line, $the_part, $part, $action, $bytes_total, $bytes_read);
        } else {
            // read actual data
            if (strcmp($part, $the_part) == 0) {
                $this_is_it = ture;
                $handler = "action" . $action;
            } else {
                $this_is_it = false;
            }
            do {
                $line = $this->readLine($conn);
                //$str .= $line;
                $bytes_read += strlen($line);
                if (($this_is_it) && (!$this->startsWith($line, $boundary)) && (chop($line) != ".")) {
                    $str .= $this->$handler($line);
                }
                $line = chop($line);
            } while ((!$this->startsWith($line, $boundary)) && ((!feof($fp)) && ($line != ".")));
            $last_line = $line;
        }
        return $str;
    }

    public function fetchStructureString(&$conn, $folder, $id)
    {
        $fp = $conn->fp;
        $this->openMessage($conn, $id);
        if (($conn->cacheMode == "x") || ($conn->cacheMode == "w")) {
            fputs($fp, "RETR $id\r\n");
            $line = fgets($fp, 100);
            if ($line[0] != "+") {
                $conn->errorNum = -10;
                $conn->error = "POP3 error:" . $line;
            }
        }

        if ($conn->fp) {
            $str = $this->readNParse($conn, "", $line);
        }

        $this->closeMessage($conn);
        return $str;
    }

    public function fetchPartHeader(&$conn, $mailbox, $id, $part)
    {
        return $this->handlePartBody($conn, $id, $part, "FetchHeader");
    }

    protected function closeMessage(&$conn)
    {
        if (($conn->cacheMode == "r") || ($conn->cacheMode == "w")) {
            fclose($conn->cacheFP);
        }
        $conn->cacheMode = "x";
        $conn->messageID = "";
    }

    protected function readNParse($conn, $boundary, &$last_line)
    {

        $original_boundary = $boundary;
        if ($conn->cacheMode == "r") {
            $fp = $conn->cacheFP;
        } else {
            $fp = $conn->fp;
        }
        // read headers from file
        $lines = $this->readHeader($fp);
        if (count($lines) == 0) {
            return "";
        }
        if ($conn->cacheMode == "w") {
            fputs($conn->cacheFP, implode("\n", $lines) . "\n\n");
        }
        // parse header into associative array
        $header = $this->contentHeaderArray($lines);
        // generate bodystructure string(s)
        if (strcasecmp($header["content-type"]["major"], "multipart") == 0) {
            $params = $header["content-type"]["parameters"];
            while (list($k, $v) = each($params)) {
                if (strcasecmp($v, "\"boundary\"") == 0) {
                    $boundary = "--" . str_replace("\"", "", $params[$k + 1]);
                }
            }
            do {
                $line = $this->readLine($conn);
            } while (!$this->startsWith($line, $boundary));
            $str = "(";
            //parse body parts
            do {
                $str .= $this->readNParse($conn, $boundary, $last_line);
                $end = (((strlen($last_line) - strlen($boundary)) > 0) || (chop($last_line) == "."));
            } while ((!$end) && (!feof($fp)) && ($line != "."));
            $str .= " \"" . $header["content-type"]["minor"] . "\" (" . implode(" ", $params) . ") NIL NIL)";
            //if next boundary encountered
            if ((chop($line) != ".") && (chop($last_line) != ".")) {
                //read up to next message boundary
                do {
                    $line = $this->readLine($conn);
                    $end = (($this->startsWith($line, $original_boundary)) || (chop($last_line) == "."));
                } while ((!$end) && (!feof($fp)) && (chop($line) != "."));
                $last_line = chop($line);
            }
        } else if (strcasecmp($header["content-type"]["major"], "message") == 0) {
            //read blank lines (up to and including first line, which hopefully isn't important)
            do {
                $line = $this->readLine($conn);
            } while ($this->startsWith($line, "\n"));
            //format structure string
            $str = '("' . $header["content-type"]["major"] . '" "' . $header["content-type"]["minor"] . '"';
            $str .= ' NIL NIL NIL';
            $str .= ' "' . $header["content-transfer-encoding"]["data"] . '"';
            $byte_count = 'NIL';
            $str .= " $byte_count NIL ";
            //recursively parse content
            $str .= $this->readNParse($conn, $boundary, $last_line);
            //more structure stuff
            $line_count = 'NIL';
            $str .= " $line_count NIL ";
            if (!empty($header["content-disposition"]["data"])) {
                $param_a = $header["content-disposition"]["parameters"];
                $str .= "(\"" . $header["content-disposition"]["data"] . "\" ";
                if ((is_array($param_a)) && (count($param_a) > 0)) {
                    $str .= "(" . implode(" ", $param_a) . ")";
                } else {
                    $str .= "NIL";
                }
                $str .= ") ";
            } else {
                $str .= "NIL ";
            }
            $str .= ' NIL)';
        } else {
            // read actual data
            $content_size = 0;
            $num_lines = 0;
            do {
                $line = $this->readLine($conn);
                $content_size += strlen($line);
                $num_lines++;
                $line = chop($line);
            } while ((!$this->startsWith($line, $boundary)) && ((!feof($fp)) && ($line != ".")));
            $last_line = $line;

            // generate bodystructure string
            $str = "(";
            $str .= "\"" . $header["content-type"]["major"] . "\" ";
            $str .= "\"" . $header["content-type"]["minor"] . "\" ";
            if ((is_array($header["content-type"]["parameters"])) && (count($header["content-type"]["parameters"]) > 0)) {
                $str .= "(" . implode(" ", $header["content-type"]["parameters"]) . ") ";
            } else {
                $str .= "NIL ";
            }
            if ($header["content-id"]["data"]) {
                $str .= "\"" . $header["content-id"]["data"] . "\" ";
            } else {
                $str .= "NIL ";
            }
            $str .= "NIL ";
            $str .= "\"" . $header["content-transfer-encoding"]["data"] . "\" ";
            $str .= $content_size . " ";
            if (strcasecmp($header["content-type"]["major"], "text") == 0) {
                $str .= $num_lines . " ";
            }
            $str .= "NIL ";
            if (!empty($header["content-disposition"]["data"])) {
                $param_a = $header["content-disposition"]["parameters"];
                $str .= "(\"" . $header["content-disposition"]["data"] . "\" ";
                if ((is_array($param_a)) && (count($param_a) > 0)) {
                    $str .= "(" . implode(" ", $param_a) . ")";
                } else {
                    $str .= "NIL";
                }
                $str .= ") ";
            } else {
                $str .= "NIL ";
            }
            $str .= "NIL ";
            $str = substr($str, 0, strlen($str) - 1);
            $str .= ")";
        }

        return $str;
    }

    protected function contentHeaderArray($lines)
    {
        //initialize header variables with default (fall back) values
        $header["content-type"]["major"] = "text";
        $header["content-type"]["minor"] = "plain";
        $header["content-transfer-encoding"]["data"] = "8bit";
        while (list($key, $line) = each($lines)) {
            list($field, $data) = $this->splitHeaderLine($line);
            // is this a content header?
            if ($this->startsWith($field, "Content")) {
                $field = strtolower($field);
                // parse line, add "data" and "parameters" to header[]
                $header[$field] = $this->parseContentHeader($data);
                // need some special care for "content-type" header line
                if (strcasecmp($field, "content-type") == 0) {
                    $typeStr = $header["content-type"]["data"];
                    //split major and minor content types
                    $slashPos = strpos($typeStr, "/");
                    $major_type = substr($typeStr, 0, $slashPos);
                    $minor_type = substr($typeStr, $slashPos + 1);
                    $header["content-type"]["major"] = strtolower($major_type);
                    $header["content-type"]["minor"] = strtolower($minor_type);
                }
            }
        }
        return $header;
    }

    protected function parseContentHeader($data)
    {
        $parameters = array();
        $pos = strpos($data, ";");
        if ($pos === false) {
            //no';'? then no parameters, all we have is main data
            $major_data = $data;
        } else {
            //every thing before first ';' is main data
            $major_data = substr($data, 0, $pos);
            $data = substr($data, $pos + 1);
            //go through parameter list (delimited by ';')
            $parameters_a = explode(";", $data);
            while (list($key, $val) = each($parameters_a)) {
                // split param name from param data
                $val = trim(chop($val));
                $eqpos = strpos($val, "=");
                $p_field = substr($val, 0, $eqpos);
                $p_data = substr($val, $eqpos + 1);
                $field = trim(chop($p_field));
                $p_data = trim(chop($p_data));
                // add quotes
                if ($p_data[0] != "\"") {
                    $p_data = "\"" . $p_data . "\"";
                }
                $p_field = "\"" . $p_field . "\"";
                // add to array
                array_push($parameters, $p_field);
                array_push($parameters, $p_data);
            }
        }
        $result["data"] = trim(chop($major_data));
        if (count($parameters) > 0) {
            $result["parameters"] = $parameters;
        } else {
            $result["parameters"] = "NIL";
        }
        return $result;
    }

    protected function readHeader($fp)
    {
        $lines = array();
        $c = 0;
        do {
            $line = chop($this->readLine($fp, 300));
            if (!empty($line)) {
                if (ord($line[0]) <= 32) {
                    $lines[$c] .= " " . trim($line);
                } else {
                    $c++;
                    $lines[$c] = $line;
                }
            }
        } while (!empty($line));
        return $lines;
    }

    protected function openMessage(&$conn, $id)
    {
        /*
          POST-CONDITIONS:
          true: if cache file has been opened
          false: if cache file is not open
          $conn->cacheMode:
          x: failed
          w: open cache for writing
          r: cache opened for reading
         */

        $conn->messageID = $this->getMessageID($conn->fp, $id);
        $messageID = $conn->messageID;
        $conn->errorNum = 0;
        $conn->cacheMode = "x";
        if (!empty($messageID)) {
            $cacheDir = File::getTempPath();
            $cachePath = $cacheDir . "/" . urlencode($messageID);
            if (file_exists(realpath($cachePath))) {
                $mode = "r";
            } else {
                $mode = "w";
            }
            $conn->cacheFP = fopen($cachePath, $mode);
            if ($conn->cacheFP) {
                $conn->cacheMode = $mode;
                return true;
            } else {
                $conn->errorNum = -3;
                $conn->error = "Couldn't open cache for reading";
            }
        } else {
            $conn->errorNum = -1;
            $conn->error = "Invalid messageID";
        }
        return false;
    }

    protected function getMessageID($fp, $id)
    {
        $messageID = "";
        fputs($fp, "TOP {$id} 0\r\n");
        $line = fgets($fp, 128);
        if ($line[0] == "+") {
            do {
                //go through headers...
                $line = chop($this->readLine($fp, 300));
                $a = $this->splitHeaderLine($line);
                if (strcasecmp($a[0], "message-id") == 0) {
                    $messageID = trim(chop($a[1]));
                    $messageID = substr(substr($messageID, 1), 0, strlen($messageID) - 2);
                }
            } while ($line[0] != ".");
        }
        return $messageID;
    }

    /**
     *
     * @param string $string
     * @return int
     */
    public function parseResult($string)
    {
        if ((($string[0] == "+") || ($string[0] == "-")) && ($string[1] != " ")) {
            $string = $string[0] . " " . substr($string, 1);
        }
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

    /**
     *
     * @param type $conn
     * @param type $user
     * @param type $pass
     * @return boolean
     */
    public function cramMD5(&$conn, $user, $pass)
    {
        $conn->message .= "Doing CRAM-MD5\n";
        fputs($conn->fp, "AUTH CRAM-MD5\r\n");
        $line = chop($this->readLine($conn->fp, 1024));
        if ($line[0] == "+") {
            $encChallenge = substr($line, 2);
        } else {
            //didn't get valid challenge
            $conn->message .= 'Didn\'t get challenge, got: "' . htmlspecialchars($line) . "\"\n";
            return false;
        }

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
            $conn->error = "";
            $conn->errorNum = 0;
            $conn->message .= "CRAM-MD5 returning true\n";
            return true;
        } else {
            $conn->error = "Authentication failed (AUTH): <br>\"" . htmlspecialchars($line) . "\"";
            $conn->errorNum = -2;
            $conn->message .= "CRAM-MD5 failed: " . htmlspecialchars($line) . "\n";
            return false;
        }
    }

    public function APOP($conn, $user, $password, $timestamp)
    {
        $digest = md5($timestamp . $password);
        fputs($conn->fp, "APOP {$user} {$digest}\r\n");
        $line = $this->readLine($conn->fp, 1024);
        if ($line[0] == "+") {
            return true;
        } else {
            return false;
        }
    }

    // check if $string starts with $match
    public function startsWith($string, $match)
    {
        if ((empty($string)) || (empty($match)))
            return false;

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

    public function close(&$conn)
    {
        if (@fputs($conn->fp, "QUIT\r\n")) {
            fgets($conn->fp, 1024);
            fclose($conn->fp);
        }
    }

}
