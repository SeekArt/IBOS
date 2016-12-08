<?php

namespace application\modules\email\core;

/**
 * Description of WebEmail
 *
 * @author gzdzl
 */
class WebEmail
{

    private $imap = false;
    private $folder = 'INBOX';
    private $mailbox;

    /**
     *
     * @param string $host
     * @param integer $port
     * @param string $user
     * @param string $pass
     * @param boolean $ssl
     * @param string $type
     * @param string $folder
     */
    public function __construct($host, $port, $user, $pass, $ssl = false,
                                $type = 'pop')
    {
        $this->mailbox = '{' . $host . ':' . $port . '/' . $type;
        if ($ssl) {
            $this->mailbox = $this->mailbox . '/ssl';
        }
        $this->mailbox = $this->mailbox . '}';
        set_time_limit(120);
        //error_reporting(0);
        $this->imap = imap_open($this->mailbox, $user, $pass);
    }

    public function isConnected()
    {
        return $this->imap !== false;
    }

    function __destruct()
    {
        if ($this->imap !== false)
            imap_close($this->imap);
    }

    public function countMessages()
    {
        return imap_num_msg($this->imap);
    }

    public function getError()
    {
        return imap_last_error();
    }

    public function selectFolder($folder)
    {
        $result = imap_reopen($this->imap, $this->mailbox . $folder);
        if ($result === true) {
            $this->folder = $folder;
        }
        return $result;
    }

    public function getFolders()
    {
        $folders = imap_list($this->imap, $this->mailbox, "*");
        return str_replace($this->mailbox, "", $folders);
    }

    public function countUnreadMessages()
    {
        $result = imap_search($this->imap, 'UNSEEN');
        if ($result === false) {
            return 0;
        }
        return count($result);
    }

    public function getUnreadMessages($withbody = true)
    {
        $emails = array();
        $result = imap_search($this->imap, 'UNSEEN');
        if ($result) {
            foreach ($result as $k => $i) {
                $emails[] = $this->formatMessage($i, $withbody);
            }
        }
        return $emails;
    }

    public function getMessages($withbody = true)
    {
        $count = $this->countMessages();
        $emails = array();
        for ($i = 1; $i <= $count; $i++) {
            $emails[] = $this->formatMessage($i, $withbody);
        }

        return $emails;
    }

    public function getMessage($id, $withbody = true)
    {
        return $this->formatMessage($id, $withbody);
    }

    protected function formatMessage($id, $withbody = true)
    {
        $header = imap_headerinfo($this->imap, $id);

        // fetch unique uid
        $uid = imap_uid($this->imap, $id);

        // get email data
        $subject = '';
        if (isset($header->subject) && strlen($header->subject) > 0) {
            foreach (imap_mime_header_decode($header->subject) as $obj) {
                $subject .= $obj->text;
            }
        }
//        file_put_contents('qqemail.txt',
//                mb_detect_encoding($subject,
//                        array('ASCII', 'UTF-8', 'GB2312', 'BIG5', 'GBK')));
        //qq  > EUC-CN(GB2312)
        //163 > UTF-8
        $subject = $this->convertToUtf8($subject);
        $email = array(
            'to' => isset($header->to) ? $this->arrayToAddress($header->to) : '',
            'from' => $this->toAddress($header->from[0]),
            'date' => $header->date,
            'subject' => $subject,
            'uid' => $uid,
            'unread' => strlen(trim($header->Unseen)) > 0,
            'answered' => strlen(trim($header->Answered)) > 0
        );
        if (isset($header->cc)) {
            $email['cc'] = $this->arrayToAddress($header->cc);
        }

        // get email body
        if ($withbody === true) {
            $body = $this->getBody($uid);
            $email['body'] = $body['body'];
            $email['html'] = $body['html'];
        }

        // get attachments
        $mailStruct = imap_fetchstructure($this->imap, $id);
        $attachments = $this->attachments2name($this->getAttachments($this->imap,
            $id, $mailStruct, ""));
        if (count($attachments) > 0) {

            foreach ($attachments as $val) {
                foreach ($val as $k => $t) {
                    if ($k == 'name') {
                        //file_put_contents('t.txt', var_export($t, true));
                        $decodedName = imap_mime_header_decode($t);
                        //if ($decodedName[0]->charset == 'GBK')
                        //    $t = iconv('GBK', 'UTF-8', $decodedName[0]->text);
                        //else {
                        //$t = $this->convertToUtf8($decodedName[0]->text);
                        $t = iconv('GBK', 'UTF-8', $decodedName[0]->text);
                        //}
                        //file_put_contents('decodedName.txt',
                        //        var_export($decodedName, true));
                        //$enc = mb_detect_encoding($decodedName[0]->text);
                        //utf-8
                        //file_put_contents('text.txt',
                        //        $enc . ' ' . $decodedName[0]->text);
                    }
                    $arr[$k] = $t;
                }
                $email['attachments'][] = $arr;
            }
        }
        return $email;
    }

    public function deleteMessage($id)
    {
        return $this->deleteMessages(array($id));
    }

    public function deleteMessages($ids)
    {
        if (imap_mail_move($this->imap, implode(",", $ids), $this->getTrash(),
                CP_UID) == false
        ) {
            return false;
        }
        return imap_expunge($this->imap);
    }

    public function moveMessage($id, $target)
    {
        return $this->moveMessages(array($id), $target);
    }

    public function moveMessages($ids, $target)
    {
        if (imap_mail_move($this->imap, implode(",", $ids), $target, CP_UID) === false) {
            return false;
        }
        return imap_expunge($this->imap);
    }

    public function setUnseenMessage($id, $seen = true)
    {
        $header = $this->getMessageHeader($id);
        if ($header == false) {
            return false;
        }

        $flags = "";
        $flags .= (strlen(trim($header->Answered)) > 0 ? "\\Answered " : '');
        $flags .= (strlen(trim($header->Flagged)) > 0 ? "\\Flagged " : '');
        $flags .= (strlen(trim($header->Deleted)) > 0 ? "\\Deleted " : '');
        $flags .= (strlen(trim($header->Draft)) > 0 ? "\\Draft " : '');

        $flags .= (($seen == true) ? '\\Seen ' : ' ');
        //echo "\n<br />".$id.": ".$flags;
        imap_clearflag_full($this->imap, $id, '\\Seen', ST_UID);
        return imap_setflag_full($this->imap, $id, trim($flags), ST_UID);
    }

    public function getAttachment($id, $index = 0)
    {
        // find message
        $attachments = false;
        $messageIndex = imap_msgno($this->imap, $id);
        $header = imap_headerinfo($this->imap, $messageIndex);
        $mailStruct = imap_fetchstructure($this->imap, $messageIndex);
        $attachments = $this->getAttachments($this->imap, $messageIndex,
            $mailStruct, "");

        if ($attachments == false) {
            return false;
        }

        // find attachment
        if ($index > count($attachments)) {
            return false;
        }
        $attachment = $attachments[$index];

        // get attachment body
        $partStruct = imap_bodystruct($this->imap, imap_msgno($this->imap, $id),
            $attachment['partNum']);
        $filename = $partStruct->dparameters[0]->value;
        $message = imap_fetchbody($this->imap, $id, $attachment['partNum'],
            FT_UID);

        switch ($attachment['enc']) {
            case 0:
            case 1:
                $message = imap_8bit($message);
                break;
            case 2:
                $message = imap_binary($message);
                break;
            case 3:
                $message = imap_base64($message);
                break;
            case 4:
                $message = quoted_printable_decode($message);
                break;
        }

        return array(
            "name" => $attachment['name'],
            "size" => $attachment['size'],
            "content" => $message
        );
    }

    public function addFolder($name)
    {
        return imap_createmailbox($this->imap, $this->mailbox . $name);
    }

    public function removeFolder($name)
    {
        return imap_deletemailbox($this->imap, $this->mailbox . $name);
    }

    public function renameFolder($name, $newname)
    {
        return imap_renamemailbox($this->imap, $this->mailbox . $name,
            $this->mailbox . $newname);
    }

    /**
     * clean folder content of selected folder
     *
     * @return bool success or not
     */
    public function purge()
    {
        // delete trash and spam
        if ($this->folder == $this->getTrash() || strtolower($this->folder) == "spam") {
            if (imap_delete($this->imap, '1:*') === false) {
                return false;
            }
            return imap_expunge($this->imap);

            // move others to trash
        } else {
            if (imap_mail_move($this->imap, '1:*', $this->getTrash()) == false) {
                return false;
            }
            return imap_expunge($this->imap);
        }
    }

    /**
     * returns all email addresses
     *
     * @return array with all email addresses or false on error
     */
    public function getAllEmailAddresses()
    {
        $saveCurrentFolder = $this->folder;
        $emails = array();
        foreach ($this->getFolders() as $folder) {
            $this->selectFolder($folder);
            foreach ($this->getMessages(false) as $message) {
                $emails[] = $message['from'];
                $emails = array_merge($emails, $message['to']);
                if (isset($message['cc'])) {
                    $emails = array_merge($emails, $message['cc']);
                }
            }
        }
        $this->selectFolder($saveCurrentFolder);
        return array_unique($emails);
    }

    /**
     * save email in sent
     *
     * @return void
     * @param $header
     * @param $body
     */
    public function saveMessageInSent($header, $body)
    {
        return imap_append($this->imap, $this->mailbox . $this->getSent(),
            $header . "\r\n" . $body . "\r\n", "\\Seen");
    }

    private function getTrash()
    {
        foreach ($this->getFolders() as $folder) {
            if (strtolower($folder) === "trash" || strtolower($folder) === "papierkorb") {
                return $folder;
            }
        }

        // no trash folder found? create one
        $this->addFolder('Trash');

        return 'Trash';
    }

    private function getSent()
    {
        foreach ($this->getFolders() as $folder) {
            if (strtolower($folder) === "sent" || strtolower($folder) === "gesendet") {
                return $folder;
            }
        }

        // no sent folder found? create one
        $this->addFolder('Sent');

        return 'Sent';
    }

    private function getMessageHeader($id)
    {
        $count = $this->countMessages();
        for ($i = 1; $i <= $count; $i++) {
            $uid = imap_uid($this->imap, $i);
            if ($uid == $id) {
                $header = imap_headerinfo($this->imap, $i);
                return $header;
            }
        }
        return false;
    }

    private function attachments2name($attachments)
    {
        $names = array();
        foreach ($attachments as $attachment) {
            $names[] = array(
                'name' => $attachment['name'],
                'size' => $attachment['size']
            );
        }
        return $names;
    }

    private function toAddress($headerinfos)
    {
        $email = "";
        $name = "";
        if (isset($headerinfos->mailbox) && isset($headerinfos->host)) {
            $email = $headerinfos->mailbox . "@" . $headerinfos->host;
        }

        if (!empty($headerinfos->personal)) {
            $name = imap_mime_header_decode($headerinfos->personal);
            $name = $name[0]->text;
        } else {
            $name = $email;
        }

        $name = $this->convertToUtf8($name);

        return $name . " <" . $email . ">";
    }

    private function arrayToAddress($addresses)
    {
        $addressesAsString = array();
        foreach ($addresses as $address) {
            $addressesAsString[] = $this->toAddress($address);
        }
        return $addressesAsString;
    }

    private function getBody($uid)
    {
        $body = $this->get_part($this->imap, $uid, "TEXT/HTML");
        $html = true;
        // if HTML body is empty, try getting text body
        if ($body == "") {
            $body = $this->get_part($this->imap, $uid, "TEXT/PLAIN");
            $html = false;
        }
        $body = $this->convertToUtf8($body);
        return array('body' => $body, 'html' => $html);
    }

    function convertToUtf8($str)
    {
//        if (mb_detect_encoding($str, "UTF-8, ISO-8859-1, GBK") != "UTF-8")
//            $str = utf8_encode($str);
//        $str = iconv('UTF-8', 'UTF-8//IGNORE', $str);
//        return $str;
        $encode = mb_detect_encoding($str);
        if ($encode != "UTF-8") {
            $str = iconv($encode, 'UTF-8', $str);
        }
        return $str;
    }

    private function get_part($imap, $uid, $mimetype, $structure = false,
                              $partNumber = false)
    {
        if (!$structure) {
            $structure = imap_fetchstructure($imap, $uid, FT_UID);
        }
        if ($structure) {
            if ($mimetype == $this->get_mime_type($structure)) {
                if (!$partNumber) {
                    $partNumber = 1;
                }
                $text = imap_fetchbody($imap, $uid, $partNumber,
                    FT_UID | FT_PEEK);
                switch ($structure->encoding) {
                    case 3:
                        return imap_base64($text);
                    case 4:
                        return imap_qprint($text);
                    default:
                        return $text;
                }
            }

            // multipart 
            if ($structure->type == 1) {
                foreach ($structure->parts as $index => $subStruct) {
                    $prefix = "";
                    if ($partNumber) {
                        $prefix = $partNumber . ".";
                    }
                    $data = $this->get_part($imap, $uid, $mimetype, $subStruct,
                        $prefix . ($index + 1));
                    if ($data) {
                        return $data;
                    }
                }
            }
        }
        return false;
    }

    private function get_mime_type($structure)
    {
        $primaryMimetype = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO",
            "IMAGE", "VIDEO", "OTHER");

        if ($structure->subtype) {
            return $primaryMimetype[(int)$structure->type] . "/" . $structure->subtype;
        }
        return "TEXT/PLAIN";
    }

    private function getAttachments($imap, $mailNum, $part, $partNum)
    {
        $attachments = array();

        if (isset($part->parts)) {
            foreach ($part->parts as $key => $subpart) {
                if ($partNum != "") {
                    $newPartNum = $partNum . "." . ($key + 1);
                } else {
                    $newPartNum = ($key + 1);
                }
                $result = $this->getAttachments($imap, $mailNum, $subpart,
                    $newPartNum);
                if (count($result) != 0) {
                    array_push($attachments, $result);
                }
            }
        } else if (isset($part->disposition)) {
            if (strtolower($part->disposition) == "attachment") {
                $partStruct = imap_bodystruct($imap, $mailNum, $partNum);
                $attachmentDetails = array(
                    "name" => $part->dparameters[0]->value,
                    "partNum" => $partNum,
                    "enc" => $partStruct->encoding,
                    "size" => $part->bytes
                );
                return $attachmentDetails;
            }
        }

        return $attachments;
    }

}
