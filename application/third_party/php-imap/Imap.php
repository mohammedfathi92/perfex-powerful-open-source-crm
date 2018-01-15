<?php
/**
 * Helper class for imap access
 *
 * @package    protocols
 * @copyright  Copyright (c) Tobias Zeising (http://www.aditu.de)
 * @license    GPLv3 (http://www.gnu.org/licenses/gpl-3.0.html)
 * @author     Tobias Zeising <tobias.zeising@aditu.de>
 */
class Imap
{
    private static $charsetAliases = [
        '5601' => 'EUC-KR',
        '646' => 'us-ascii',
        '850' => 'IBM850',
        '852' => 'IBM852',
        '855' => 'IBM855',
        '857' => 'IBM857',
        '862' => 'IBM862',
        '864' => 'IBM864',
        '864i' => 'IBM864i',
        '866' => 'IBM866',
        'ansi-1251' => 'windows-1251',
        'ansi_x3.4-1968' => 'us-ascii',
        'arabic' => 'ISO-8859-6',
        'ascii' => 'us-ascii',
        'asmo-708' => 'ISO-8859-6',
        'chinese' => 'GB2312',
        'cns11643' => 'x-euc-tw',
        'cp-866' => 'IBM866',
        'cp1250' => 'windows-1250',
        'cp1251' => 'windows-1251',
        'cp1252' => 'windows-1252',
        'cp1253' => 'windows-1253',
        'cp1254' => 'windows-1254',
        'cp1255' => 'windows-1255',
        'cp1256' => 'windows-1256',
        'cp1257' => 'windows-1257',
        'cp1258' => 'windows-1258',
        'cp819' => 'ISO-8859-1',
        'cp850' => 'IBM850',
        'cp852' => 'IBM852',
        'cp855' => 'IBM855',
        'cp857' => 'IBM857',
        'cp862' => 'IBM862',
        'cp864' => 'IBM864',
        'cp864i' => 'IBM864i',
        'cp866' => 'IBM866',
        'cp932' => 'Shift_JIS',
        'csIBM850' => 'IBM850',
        'csIBM852' => 'IBM852',
        'csIBM855' => 'IBM855',
        'csIBM857' => 'IBM857',
        'csIBM862' => 'IBM862',
        'csIBM864' => 'IBM864',
        'csIBM866' => 'IBM866',
        'csMacintosh' => 'x-mac-roman',
        'csbig5' => 'Big5',
        'cseucjpkdfmtjapanese' => 'EUC-JP',
        'csgb2312' => 'GB2312',
        'csibm864i' => 'IBM864i',
        'csiso103t618bit' => 'T.61-8bit',
        'csiso111ecmacyrillic' => 'ISO-IR-111',
        'csiso2022jp' => 'ISO-2022-JP',
        'csiso2022jp2' => 'ISO-2022-JP',
        'csiso2022kr' => 'ISO-2022-KR',
        'csiso58gb231280' => 'GB2312',
        'csiso88596e' => 'ISO-8859-6-E',
        'csiso88596i' => 'ISO-8859-6-I',
        'csiso88598e' => 'ISO-8859-8-E',
        'csiso88598i' => 'ISO-8859-8-I',
        'csisolatin1' => 'ISO-8859-1',
        'csisolatin2' => 'ISO-8859-2',
        'csisolatin3' => 'ISO-8859-3',
        'csisolatin4' => 'ISO-8859-4',
        'csisolatin5' => 'ISO-8859-9',
        'csisolatin6' => 'ISO-8859-10',
        'csisolatinarabic' => 'ISO-8859-6',
        'csisolatincyrillic' => 'ISO-8859-5',
        'csisolatingreek' => 'ISO-8859-7',
        'csisolatinhebrew' => 'ISO-8859-8',
        'csksc56011987' => 'EUC-KR',
        'csshiftjis' => 'Shift_JIS',
        'csueckr' => 'EUC-KR',
        'csunicode' => 'UTF-16BE',
        'csunicode11' => 'UTF-16BE',
        'csunicode11utf7' => 'UTF-7',
        'csunicodeascii' => 'UTF-16BE',
        'csunicodelatin1' => 'UTF-16BE',
        'csviqr' => 'VIQR',
        'csviscii' => 'VISCII',
        'cyrillic' => 'ISO-8859-5',
        'ecma-114' => 'ISO-8859-6',
        'ecma-118' => 'ISO-8859-7',
        'ecma-cyrillic' => 'ISO-IR-111',
        'elot_928' => 'ISO-8859-7',
        'gb_2312' => 'GB2312',
        'gb_2312-80' => 'GB2312',
        'gbk' => 'x-gbk',
        'greek' => 'ISO-8859-7',
        'greek8' => 'ISO-8859-7',
        'hebrew' => 'ISO-8859-8',
        'ibm-864' => 'IBM864',
        'ibm-864i' => 'IBM864i',
        'ibm819' => 'ISO-8859-1',
        'ibm874' => 'windows-874',
        'iso-10646' => 'UTF-16BE',
        'iso-10646-j-1' => 'UTF-16BE',
        'iso-10646-ucs-2' => 'UTF-16BE',
        'iso-10646-ucs-4' => 'UTF-32BE',
        'iso-10646-ucs-basic' => 'UTF-16BE',
        'iso-10646-unicode-latin1' => 'UTF-16BE',
        'iso-2022-cn-ext' => 'ISO-2022-CN',
        'iso-2022-jp-2' => 'ISO-2022-JP',
        'iso-8859-8i' => 'ISO-8859-8-I',
        'iso-ir-100' => 'ISO-8859-1',
        'iso-ir-101' => 'ISO-8859-2',
        'iso-ir-103' => 'T.61-8bit',
        'iso-ir-109' => 'ISO-8859-3',
        'iso-ir-110' => 'ISO-8859-4',
        'iso-ir-126' => 'ISO-8859-7',
        'iso-ir-127' => 'ISO-8859-6',
        'iso-ir-138' => 'ISO-8859-8',
        'iso-ir-144' => 'ISO-8859-5',
        'iso-ir-148' => 'ISO-8859-9',
        'iso-ir-149' => 'EUC-KR',
        'iso-ir-157' => 'ISO-8859-10',
        'iso-ir-58' => 'GB2312',
        'iso8859-1' => 'ISO-8859-1',
        'iso8859-10' => 'ISO-8859-10',
        'iso8859-11' => 'ISO-8859-11',
        'iso8859-13' => 'ISO-8859-13',
        'iso8859-14' => 'ISO-8859-14',
        'iso8859-15' => 'ISO-8859-15',
        'iso8859-2' => 'ISO-8859-2',
        'iso8859-3' => 'ISO-8859-3',
        'iso8859-4' => 'ISO-8859-4',
        'iso8859-5' => 'ISO-8859-5',
        'iso8859-6' => 'ISO-8859-6',
        'iso8859-7' => 'ISO-8859-7',
        'iso8859-8' => 'ISO-8859-8',
        'iso8859-9' => 'ISO-8859-9',
        'iso88591' => 'ISO-8859-1',
        'iso885910' => 'ISO-8859-10',
        'iso885911' => 'ISO-8859-11',
        'iso885912' => 'ISO-8859-12',
        'iso885913' => 'ISO-8859-13',
        'iso885914' => 'ISO-8859-14',
        'iso885915' => 'ISO-8859-15',
        'iso88592' => 'ISO-8859-2',
        'iso88593' => 'ISO-8859-3',
        'iso88594' => 'ISO-8859-4',
        'iso88595' => 'ISO-8859-5',
        'iso88596' => 'ISO-8859-6',
        'iso88597' => 'ISO-8859-7',
        'iso88598' => 'ISO-8859-8',
        'iso88599' => 'ISO-8859-9',
        'iso_8859-1' => 'ISO-8859-1',
        'iso_8859-15' => 'ISO-8859-15',
        'iso_8859-2' => 'ISO-8859-2',
        'iso_8859-3' => 'ISO-8859-3',
        'iso_8859-4' => 'ISO-8859-4',
        'iso_8859-5' => 'ISO-8859-5',
        'iso_8859-6' => 'ISO-8859-6',
        'iso_8859-7' => 'ISO-8859-7',
        'iso_8859-8' => 'ISO-8859-8',
        'iso_8859-9' => 'ISO-8859-9',
        'korean' => 'EUC-KR',
        'ks_c_5601-1987' => 'EUC-KR',
        'ks_c_5601-1989' => 'EUC-KR',
        'ksc5601' => 'EUC-KR',
        'ksc_5601' => 'EUC-KR',
        'l1' => 'ISO-8859-1',
        'l2' => 'ISO-8859-2',
        'l3' => 'ISO-8859-3',
        'l4' => 'ISO-8859-4',
        'l5' => 'ISO-8859-9',
        'l6' => 'ISO-8859-10',
        'latin1' => 'ISO-8859-1',
        'latin2' => 'ISO-8859-2',
        'latin3' => 'ISO-8859-3',
        'latin4' => 'ISO-8859-4',
        'latin5' => 'ISO-8859-9',
        'latin6' => 'ISO-8859-10',
        'mac' => 'x-mac-roman',
        'macintosh' => 'x-mac-roman',
        'ms_kanji' => 'Shift_JIS',
        'shift-jis' => 'Shift_JIS',
        'sun_eu_greek' => 'ISO-8859-7',
        't.61' => 'T.61-8bit',
        'tis620' => 'TIS-620',
        'unicode-1-1-utf-7' => 'UTF-7',
        'unicode-1-1-utf-8' => 'UTF-8',
        'unicode-2-0-utf-7' => 'UTF-7',
        'visual' => 'ISO-8859-8',
        'windows-31j' => 'Shift_JIS',
        'x-cp1250' => 'windows-1250',
        'x-cp1251' => 'windows-1251',
        'x-cp1252' => 'windows-1252',
        'x-cp1253' => 'windows-1253',
        'x-cp1254' => 'windows-1254',
        'x-cp1255' => 'windows-1255',
        'x-cp1256' => 'windows-1256',
        'x-cp1257' => 'windows-1257',
        'x-cp1258' => 'windows-1258',
        'x-euc-jp' => 'EUC-JP',
        'x-iso-10646-ucs-2-be' => 'UTF-16BE',
        'x-iso-10646-ucs-2-le' => 'UTF-16LE',
        'x-iso-10646-ucs-4-be' => 'UTF-32BE',
        'x-iso-10646-ucs-4-le' => 'UTF-32LE',
        'x-sjis' => 'Shift_JIS',
        'x-unicode-2-0-utf-7' => 'UTF-7',
        'x-x-big5' => 'Big5',
        'zh_cn.euc' => 'GB2312',
        'zh_tw-big5' => 'Big5',
        'zh_tw-euc' => 'x-euc-tw',
    ];

    /**
     * imap connection
     */
    protected $imap = false;

    /**
     * mailbox url string
     */
    protected $mailbox = "";

    /**
     * currentfolder
     */
    protected $folder = "Inbox";

    /**
     * initialize imap helper
     *
     * @return void
     * @param $mailbox imap_open string
     * @param $username
     * @param $password
     * @param $encryption ssl or tls
     */
    public function __construct($mailbox, $username, $password, $encryption = false)
    {
        $enc = '';
        if ($encryption!=null && isset($encryption) && $encryption=='ssl') {
            $enc = '/imap/ssl/novalidate-cert';
        } elseif ($encryption!=null && isset($encryption) && $encryption=='tls') {
            $enc = '/imap/tls/novalidate-cert';
        }
        $this->mailbox = "{" . $mailbox . $enc . "}";
        $this->imap = @imap_open($this->mailbox, $username, $password);
    }

    /**
     * close connection
     */
    public function __destruct()
    {
        if ($this->imap!==false) {
            imap_close($this->imap);
        }
    }

    /**
     * returns true after successfull connection
     *
     * @return bool true on success
     */
    public function isConnected()
    {
        return $this->imap !== false;
    }

    /**
     * returns last imap error
     *
     * @return string error message
     */
    public function getError()
    {
        return imap_last_error();
    }

    /**
     * select given folder
     *
     * @return bool successfull opened folder
     * @param $folder name
     */
    public function selectFolder($folder)
    {
        $result = imap_reopen($this->imap, $this->mailbox . $folder);
        if ($result === true) {
            $this->folder = $folder;
        }

        return $result;
    }

    /**
     * returns all available folders
     *
     * @return array with foldernames
     */
    public function getFolders()
    {
        $folders = imap_list($this->imap, $this->mailbox, "*");

        return str_replace($this->mailbox, "", $folders);
    }

    /**
     * returns the number of messages in the current folder
     *
     * @return int message count
     */
    public function countMessages()
    {
        return imap_num_msg($this->imap);
    }

    /**
     * returns the number of unread messages in the current folder
     *
     * @return int message count
     */
    public function countUnreadMessages()
    {
        $result = imap_search($this->imap, 'UNSEEN');
        if ($result===false) {
            return 0;
        }

        return count($result);
    }

    /**
     * returns unseen emails in the current folder
     *
     * @return array messages
     * @param $withbody without body
     */
    public function getUnreadMessages($withbody=true)
    {
        $emails = array();
        $result = imap_search($this->imap, 'UNSEEN');
        if ($result) {
            foreach ($result as $k=>$i) {
                $emails[]= $this->formatMessage($i, $withbody);
            }
        }

        return $emails;
    }

    /**
     * returns all emails in the current folder
     *
     * @return array messages
     * @param $withbody without body
     */
    public function getMessages($withbody = true)
    {
        $count = $this->countMessages();
        $emails = array();
        for ($i=1; $i<=$count; $i++) {
            $emails[]= $this->formatMessage($i, $withbody);
        }
        // sort emails descending by date
        // usort($emails, function($a, $b) {
        // try {
        // $datea = new \DateTime($a['date']);
        // $dateb = new \DateTime($b['date']);
        // } catch(\Exception $e) {
        // return 0;
        // }
        // if ($datea == $dateb)
        // return 0;
        // return $datea < $dateb ? 1 : -1;
        // });
        return $emails;
    }

    /**
     * returns email by given id
     *
     * @return array messages
     * @param $id
     * @param $withbody without body
     */
    public function getMessage($id, $withbody = true)
    {
        return $this->formatMessage($id, $withbody);
    }

    /**
     * @param $id
     * @param bool $withbody
     * @return array
     */
    protected function formatMessage($id, $withbody=true)
    {
        $header = imap_headerinfo($this->imap, $id);
        // fetch unique uid
        $uid = imap_uid($this->imap, $id);

        // get email data
        $subject = '';
        $charset = '';
        if (isset($header->subject) && strlen($header->subject) > 0) {
            foreach (imap_mime_header_decode($header->subject) as $obj) {
                $charset .= $obj->charset;
                $subject .= $obj->text;
            }
        }
        $subject = $this->convertToUtf8($subject, $charset);
        $email = array(
        'to'       => isset($header->to) ? $this->arrayToAddress($header->to) : '',
        'from'     => $this->toAddress($header->from[0]),
        'date'     => $header->date,
        'subject'  => $subject,
        'uid'       => $uid,
        'unread'   => strlen(trim($header->Unseen))>0,
        'answered' => strlen(trim($header->Answered))>0,
        'deleted' => strlen(trim($header->Deleted))>0
    );
        if (isset($header->cc)) {
            $email['cc'] = $this->arrayToAddress($header->cc);
        }
        // get email body
        if ($withbody===true) {
            $body = $this->getBody($uid);
            $email['body'] = $body['body'];
            $email['html'] = $body['html'];
        }
        // get attachments
        $mailStruct = imap_fetchstructure($this->imap, $id);
        $attachments = $this->attachments2name($this->getAttachments($this->imap, $id, $mailStruct, ""));
        if (count($attachments)>0) {
            foreach ($attachments as $val) {
                foreach ($val as $k=>$t) {
                    if ($k == 'name') {
                        $decodedName = imap_mime_header_decode($t);
                        $t = $this->convertToUtf8($decodedName[0]->text, $decodedName[0]->charset);
                    }
                    $arr[$k] = $t;
                }
                $email['attachments'][] = $arr;
            }
        }

        return $email;
    }

    /**
     * delete given message
     *
     * @return bool success or not
     * @param $id of the message
     */
    public function deleteMessage($id)
    {
        return $this->deleteMessages(array($id));
    }

    /**
     * delete messages
     *
     * @return bool success or not
     * @param $ids array of ids
     */
    public function deleteMessages($ids)
    {
        if (imap_mail_move($this->imap, implode(",", $ids), $this->getTrash(), CP_UID) == false) {
            return false;
        }

        return imap_expunge($this->imap);
    }

    /**
     * move given message in new folder
     *
     * @return bool success or not
     * @param $id of the message
     * @param $target new folder
     */
    public function moveMessage($id, $target)
    {
        return $this->moveMessages(array($id), $target);
    }

    /**
     * move given message in new folder
     *
     * @return bool success or not
     * @param $ids array of message ids
     * @param $target new folder
     */
    public function moveMessages($ids, $target)
    {
        if (imap_mail_move($this->imap, implode(",", $ids), $target, CP_UID)===false) {
            return false;
        }

        return imap_expunge($this->imap);
    }

    /**
     * mark message as read
     *
     * @return bool success or not
     * @param $id of the message
     * @param $seen true = message is read, false = message is unread
     */
    public function setUnseenMessage($id, $seen = true)
    {
        $header = $this->getMessageHeader($id);
        if ($header==false) {
            return false;
        }

        $flags = "";
        $flags .= (strlen(trim($header->Answered))>0 ? "\\Answered " : '');
        $flags .= (strlen(trim($header->Flagged))>0 ? "\\Flagged " : '');
        $flags .= (strlen(trim($header->Deleted))>0 ? "\\Deleted " : '');
        $flags .= (strlen(trim($header->Draft))>0 ? "\\Draft " : '');

        $flags .= (($seen == true) ? '\\Seen ' : ' ');
        //echo "\n<br />".$id.": ".$flags;
        imap_clearflag_full($this->imap, $id, '\\Seen', ST_UID);

        return imap_setflag_full($this->imap, $id, trim($flags), ST_UID);
    }

    /**
     * return content of messages attachment
     *
     * @return binary attachment
     * @param $id of the message
     * @param $index of the attachment (default: first attachment)
     */
    public function getAttachment($id, $index = 0)
    {
        // find message
        $attachments = false;
        $messageIndex = imap_msgno($this->imap, $id);
        $header = imap_headerinfo($this->imap, $messageIndex);
        $mailStruct = imap_fetchstructure($this->imap, $messageIndex);
        $attachments = $this->getAttachments($this->imap, $messageIndex, $mailStruct, "");

        if ($attachments==false) {
            return false;
        }

        // find attachment
        if ($index > count($attachments)) {
            return false;
        }
        $attachment = $attachments[$index];

        // get attachment body
        $partStruct = imap_bodystruct($this->imap, imap_msgno($this->imap, $id), $attachment['partNum']);
        $filename = $partStruct->dparameters[0]->value;
        $message = imap_fetchbody($this->imap, $id, $attachment['partNum'], FT_UID);

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
                "content" => $message);
    }

    /**
     * add new folder
     *
     * @return bool success or not
     * @param $name of the folder
     * @param $subscribe immediately subscribe to folder
     */
    public function addFolder($name, $subscribe = false)
    {
        $success = imap_createmailbox($this->imap, $this->mailbox . $name);
        if ($success && $subscribe) {
            $success = imap_subscribe($this->imap, $this->mailbox . $name);
        }

        return $success;
    }

    /**
     * remove folder
     *
     * @return bool success or not
     * @param $name of the folder
     */
    public function removeFolder($name)
    {
        return imap_deletemailbox($this->imap, $this->mailbox . $name);
    }

    /**
     * rename folder
     *
     * @return bool success or not
     * @param $name of the folder
     * @param $newname of the folder
     */
    public function renameFolder($name, $newname)
    {
        return imap_renamemailbox($this->imap, $this->mailbox . $name, $this->mailbox . $newname);
    }

    /**
     * clean folder content of selected folder
     *
     * @return bool success or not
     */
    public function purge()
    {
        // delete trash and spam
        if ($this->folder==$this->getTrash() || strtolower($this->folder)=="spam") {
            if (imap_delete($this->imap, '1:*')===false) {
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
        return imap_append($this->imap, $this->mailbox . $this->getSent(), $header . "\r\n" . $body . "\r\n", "\\Seen");
    }

    /**
     * explicitly close imap connection
     */
    public function close()
    {
        if ($this->imap!==false) {
            imap_close($this->imap);
        }
    }

    // protected helpers

    /**
     * get trash folder name or create new trash folder
     *
     * @return trash folder name
     */
    protected function getTrash()
    {
        foreach ($this->getFolders() as $folder) {
            if (strtolower($folder)==="trash" || strtolower($folder)==="papierkorb" || strtolower($folder) === 'inbox.trash') {
                return $folder;
            }
        }
        $trash_folder_name = 'Trash';
        if (_startsWith($folder, 'INBOX')) {
            $trash_folder_name = 'INBOX.Trash';
        }
        // no trash folder found? create one
        $this->addFolder($trash_folder_name);

        return $trash_folder_name;
    }

    /**
     * get sent folder name or create new sent folder
     *
     * @return sent folder name
     */
    protected function getSent()
    {
        foreach ($this->getFolders() as $folder) {
            if (strtolower($folder)==="sent" || strtolower($folder)==="gesendet") {
                return $folder;
            }
        }

        // no sent folder found? create one
        $this->addFolder('Sent');

        return 'Sent';
    }

    /**
     * fetch message by id
     *
     * @return header
     * @param $id of the message
     */
    protected function getMessageHeader($id)
    {
        $count = $this->countMessages();
        for ($i=1; $i<=$count; $i++) {
            $uid = imap_uid($this->imap, $i);
            if ($uid==$id) {
                $header = imap_headerinfo($this->imap, $i);

                return $header;
            }
        }

        return false;
    }

    /**
     * convert attachment in array(name => ..., size => ...).
     *
     * @return array
     * @param $attachments with name and size
     */
    protected function attachments2name($attachments)
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

    /**
     * convert imap given address in string
     *
     * @return string in format "Name <email@bla.de>"
     * @param $headerinfos the infos given by imap
     */
    protected function toAddress($headerinfos)
    {
        $email = "";
        $name = "";
        $charset = "";
        if (isset($headerinfos->mailbox) && isset($headerinfos->host)) {
            $email = $headerinfos->mailbox . "@" . $headerinfos->host;
        }
        if (!empty($headerinfos->personal)) {
            $name = imap_mime_header_decode($headerinfos->personal);
            $charset = $name[0]->charset;
            $name = $name[0]->text;
        } else {
            $name = $email;
        }

        $name = $this->convertToUtf8($name, $charset);

        return $name . " <" . $email . ">";
    }

    /**
     * converts imap given array of addresses in strings
     *
     * @return array with strings (e.g. ["Name <email@bla.de>", "Name2 <email2@bla.de>"]
     * @param $addresses imap given addresses as array
     */
    protected function arrayToAddress($addresses)
    {
        $addressesAsString = array();
        foreach ($addresses as $address) {
            $addressesAsString[] = $this->toAddress($address);
        }

        return $addressesAsString;
    }

    /**
     * returns body of the email. First search for html version of the email, then the plain part.
     *
     * @return string email body
     * @param $uid message id
     */
    protected function getBody($uid)
    {
        $body = '';
        list($body, $charset) = $this->get_part($this->imap, $uid, "TEXT/HTML");
        $html = true;
        // if HTML body is empty, try getting text body
        if ($body == "") {
            list($body, $charset) = $this->get_part($this->imap, $uid, "TEXT/PLAIN");
            $html = false;
        }
        $body = $this->convertToUtf8($body, $charset);

        return array( 'body' => $body, 'html' => $html);
    }

    /**
     * convert to utf8 if necessary.
     *
     * @return true or false
     * @param $string utf8 encoded string
     */
    public function convertToUtf8($text, $fromCharset)
    {
        $utf8Aliases = array(
            'utf8' => true,
            'utf-8' => true,
            'UTF8' => true,
            'UTF-8' => true,
            'utf-8utf-8' => true,
            'default'=>true,
        );

        if (isset($utf8Aliases[$fromCharset])) {
            return $text;
        }

        $originalFromCharset = $fromCharset;
        $lowercaseFromCharset = strtolower($fromCharset);
        if (isset(self::$charsetAliases[$lowercaseFromCharset])) {
            $fromCharset = self::$charsetAliases[$lowercaseFromCharset];
        }

        if ($fromCharset == 'iso-8859-8-i') {
            $fromCharset = 'iso-8859-8';
        }

        $iconvDecodedText = iconv($fromCharset, 'UTF-8//IGNORE', $text);
        if (false === $iconvDecodedText) {
            $iconvDecodedText = iconv($originalFromCharset, 'UTF-8', $text);
        }

        if (false !== $iconvDecodedText) {
            return $iconvDecodedText;
        }

        $decodedText = mb_convert_encoding($text, 'UTF-8', $fromCharset);

        return $decodedText;
    }

    /**
     * returns a part with a given mimetype
     * taken from http://www.sitepoint.com/exploring-phps-imap-library-2/
     *
     * @return string email body
     * @param $imap imap stream
     * @param $uid message id
     * @param $mimetype
     */
    protected function get_part($imap, $uid, $mimetype, $structure = false, $partNumber = false)
    {
        if (!$structure) {
            $structure = imap_fetchstructure($imap, $uid, FT_UID);
        }
        if ($structure) {
            if ($structure->parameters) {
                foreach ($structure->parameters as $x) {
                    $params[strtolower($x->attribute)] = $x->value;
                }
            }

            $charset = isset($params['charset']) ? $params['charset'] : null;

            if ($mimetype == $this->get_mime_type($structure)) {
                if (!$partNumber) {
                    $partNumber = 1;
                }
                $text = imap_fetchbody($imap, $uid, $partNumber, FT_UID | FT_PEEK);
                switch ($structure->encoding) {
                case 3: return array(imap_base64($text), $charset);
                case 4: return array(imap_qprint($text), $charset);
                default: return array($text, $charset);
           }
            }

            // multipart
            if ($structure->type == 1) {
                foreach ($structure->parts as $index => $subStruct) {
                    $prefix = "";
                    if ($partNumber) {
                        $prefix = $partNumber . ".";
                    }
                    list($data, $charset2) = $this->get_part($imap, $uid, $mimetype, $subStruct, $prefix . ($index + 1));
                    $charset = ($charset) ? $charset : $charset2;
                    if ($data) {
                        return array($data, $charset);
                    }
                }
            }
        }

        return false;
    }

    /**
     * extract mimetype
     * taken from http://www.sitepoint.com/exploring-phps-imap-library-2/
     *
     * @return string mimetype
     * @param $structure
     */
    protected function get_mime_type($structure)
    {
        $primaryMimetype = array("TEXT", "MULTIPART", "MESSAGE", "APPLICATION", "AUDIO", "IMAGE", "VIDEO", "OTHER");

        if ($structure->subtype) {
            return $primaryMimetype[(int) $structure->type] . "/" . $structure->subtype;
        }

        return "TEXT/PLAIN";
    }

    /**
     * get attachments of given email
     * taken from http://www.sitepoint.com/exploring-phps-imap-library-2/
     *
     * @return array of attachments
     * @param $imap stream
     * @param $mailNum email
     * @param $part
     * @param $partNum
     */
    protected function getAttachments($imap, $mailNum, $part, $partNum)
    {
        $attachments = array();

        if (isset($part->parts)) {
            foreach ($part->parts as $key => $subpart) {
                if ($partNum != "") {
                    $newPartNum = $partNum . "." . ($key + 1);
                } else {
                    $newPartNum = ($key+1);
                }
                $result = $this->getAttachments(
                    $imap,
                    $mailNum,
                    $subpart,
                    $newPartNum
                );
                if (count($result) != 0) {
                    array_push($attachments, $result);
                }
            }
        } elseif (isset($part->disposition)) {
            if (strtolower($part->disposition) == "attachment") {
                $partStruct = imap_bodystruct(
                    $imap,
                    $mailNum,
                    $partNum
                );
                $attachmentDetails = array(
                    "name"    => $part->dparameters[0]->value,
                    "partNum" => $partNum,
                    "enc"     => $partStruct->encoding,
                    "size"    => $part->bytes
                );

                return $attachmentDetails;
            }
        }

        return $attachments;
    }

    /**
     * Return general mailbox statistics
     *
     * @return bool | StdClass object
     */
    public function getMailboxStatistics()
    {
        return $this->isConnected() ? imap_mailboxmsginfo($this->imap) : false ;
    }
}
