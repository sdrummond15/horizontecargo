<?php

namespace AcyMailing\Helpers;

use AcyMailing\Classes\HistoryClass;
use AcyMailing\Classes\ListClass;
use AcyMailing\Classes\MailClass;
use AcyMailing\Classes\RuleClass;
use AcyMailing\Classes\UserClass;
use AcyMailing\Libraries\acymObject;
use AcyMailing\Types\CharsetType;

class BounceHelper extends acymObject
{
    var $server;
    var $username;
    var $password;
    var $port;
    var $connectMethod;
    var $secureMethod;
    var $selfSigned;
    var $timeout;

    var $allowed_extensions = [];
    var $nbMessages = 0;
    var $report = false;
    var $mailer;
    var $mailbox;
    var $_message;
    var $userClass;
    var $blockedUsers = [];
    var $deletedUsers = [];
    var $bounceMessages = [];
    var $usePear = false;
    var $detectEmail;
    var $detectEmail2 = '/(([a-z0-9\-]+\.)+[a-z0-9]{2,8})\/([a-z0-9!#$%&\'*+\/=?^_`{|}~-]+(?:\.[a-z0-9!#$%&\'*+\/=?^_`{|}~-]+)*)/i';
    var $messages = [];
    var $stoptime = 0;
    var $mod_security2 = false;
    var $obend = 0;
    private $allCharsets;

    private $ruleClass;
    private $mailClass;
    private $historyClass;
    private $encodingHelper;

    public function __construct()
    {
        parent::__construct();

        $this->mailer = new MailerHelper();
        $this->ruleClass = new RuleClass();
        $this->userClass = new UserClass();
        $this->mailClass = new MailClass();
        $this->historyClass = new HistoryClass();
        $this->encodingHelper = new EncodingHelper();

        $this->mailer->report = false;
        $this->mailer->alreadyCheckedAddresses = true;

        $charsetType = new CharsetType();
        $this->allCharsets = $charsetType->charsets;

        $this->allowed_extensions = explode(',', $this->config->get('allowed_files', ''));
        $this->detectEmail = '/'.acym_getEmailRegex(false, true).'/i';
    }

    public function init()
    {
        $this->server = $this->config->get('bounce_server');
        $this->username = $this->config->get('bounce_username');
        $this->password = $this->config->get('bounce_password');
        $this->port = $this->config->get('bounce_port', '');
        $this->connectMethod = $this->config->get('bounce_connection');
        $this->secureMethod = $this->config->get('bounce_secured', '');
        $this->selfSigned = $this->config->get('bounce_certif', false);
        $this->timeout = $this->config->get('bounce_timeout');

        if ($this->connectMethod == 'pear') {
            $this->usePear = true;
            include_once ACYM_INC.'pear'.DS.'pop3.php';

            return true;
        }

        if (extension_loaded('imap') || function_exists('imap_open')) {
            return true;
        }

        $prefix = PHP_SHLIB_SUFFIX === 'dll' ? 'php_' : '';
        $EXTENSION = $prefix.'imap.'.PHP_SHLIB_SUFFIX;

        if (function_exists('dl')) {
            $fatalMessage = 'The system tried to load dynamically the '.$EXTENSION.' extension';
            $fatalMessage .= '<br />If you see this message, that means the system could not load this PHP extension';
            $fatalMessage .= '<br />Please enable the PHP Extension '.$EXTENSION;
            ob_start();
            echo $fatalMessage;
            dl($EXTENSION);
            $warnings = str_replace($fatalMessage, '', ob_get_clean());
            if (extension_loaded('imap') || function_exists('imap_open')) {
                return true;
            }
        }

        if ($this->report) {
            acym_display(
                'The extension "'.$EXTENSION.'" could not be loaded, please change your PHP configuration to enable it or use the pop3 method without imap extension',
                'error'
            );
            if (!empty($warnings)) {
                acym_display($warnings, 'warning');
            }
        }

        return false;
    }

    public function connect()
    {
        if ($this->usePear) {
            return $this->connectPear();
        }

        return $this->connectImap();
    }

    private function connectPear()
    {
        ob_start();

        $this->mailbox = new \Net_POP3();

        $timeout = $this->timeout;
        if (!empty($timeout)) {
            $this->mailbox->_timeout = $timeout;
        }

        $port = intval($this->port);
        $secure = $this->secureMethod;
        if (empty($port)) {
            if ($secure == 'ssl') {
                $port = '995';
            } else {
                $port = '110/pop3/notls';
            }
        }

        $serverName = trim($this->server);

        if (!empty($secure) && !strpos($serverName, '://')) {
            $serverName = $secure.'://'.$serverName;
        }

        if (!$this->mailbox->connect($serverName, $port)) {
            $warnings = ob_get_clean();
            if ($this->report) {
                acym_enqueueMessage(acym_translation_sprintf('ACYM_ERROR_CONNECTING', $this->server.' : '.$port), 'error');
            }
            if (!empty($warnings) && $this->report) {
                acym_display($warnings, 'warning');
            }

            return false;
        }

        $login = $this->mailbox->login(trim($this->username), trim($this->password), 'USER');
        if (empty($login) || isset($login->code)) {
            $warnings = ob_get_clean();
            if ($this->report) {
                acym_enqueueMessage(acym_translation_sprintf('ACYM_ERROR_LOGIN', $this->username.':'.$this->password), 'error');
            }
            if (!empty($warnings) && $this->report) {
                acym_display($warnings, 'warning');
            }

            return false;
        }

        ob_end_clean();

        return true;
    }

    private function connectImap()
    {
        if (empty($this->server)) {
            acym_enqueueMessage(acym_translation('ACYM_CONFIGURE_BOUNCE'), 'warning');

            return false;
        }

        ob_start();
        $buff = imap_alerts();
        $buff = imap_errors();

        $timeout = intval($this->timeout);
        if (!empty($timeout)) {
            imap_timeout(IMAP_OPENTIMEOUT, $timeout);
        }

        $port = intval($this->port);
        $secure = $this->secureMethod;
        $protocol = $this->connectMethod;
        $serverName = '{'.trim($this->server);
        if (empty($port)) {
            if ($secure == 'ssl' && $protocol == 'imap') {
                $port = '993';
            } elseif ($secure == 'ssl' && $protocol == 'pop3') {
                $port = '995';
            } elseif ($protocol == 'imap') {
                $port = '143';
            } elseif ($protocol == 'pop3') {
                $port = '110';
            }
        }

        if (!empty($port)) {
            $serverName .= ':'.$port;
        }

        if (!empty($secure)) {
            $serverName .= '/'.$secure;
        }

        $email = trim($this->username);
        if (strpos($email, '\\') !== false) {
            list($user, $authuser) = explode('\\', $email);
            list($x, $domain) = explode('@', $user);
            $serverName .= '/authuser='.$user.'/user='.$authuser.'@'.$domain;
        }

        if ($this->selfSigned) {
            $serverName .= '/novalidate-cert';
        }

        if (!empty($protocol)) {
            $serverName .= '/service='.$protocol;
        }
        $serverName .= '}';
        $this->mailbox = imap_open($serverName, trim($this->username), trim($this->password), OP_SILENT);
        $warnings = ob_get_clean();

        if ($this->report) {
            if (!$this->mailbox) {
                acym_enqueueMessage(acym_translation_sprintf('ACYM_ERROR_CONNECTING', $serverName), 'error');
            }
            if (!empty($warnings)) {
                acym_enqueueMessage($warnings, 'warning');
            }
        }


        return $this->mailbox ? true : false;
    }

    public function getNBMessages()
    {
        if ($this->usePear) {
            $this->nbMessages = $this->mailbox->numMsg();
        } else {
            $this->nbMessages = imap_num_msg($this->mailbox);
        }

        return $this->nbMessages;
    }

    public function getMessage($msgNB)
    {
        if ($this->usePear) {
            $message = new \stdClass();
            $message->headerString = $this->mailbox->getRawHeaders($msgNB);
            if (empty($message->headerString)) {
                return false;
            }
        } else {
            $message = imap_headerinfo($this->mailbox, $msgNB);
        }

        return $message;
    }

    public function deleteMessage($msgNB)
    {
        if ($this->usePear) {
            $this->mailbox->deleteMsg($msgNB);
        } else {
            imap_delete($this->mailbox, $msgNB);
            imap_expunge($this->mailbox);
        }
    }

    public function close()
    {
        if ($this->usePear) {
            $this->mailbox->disconnect();
        } else {
            imap_close($this->mailbox);
        }
    }

    private function decodeMessage()
    {
        if ($this->usePear) {
            return $this->decodeMessagePear();
        } else {
            return $this->decodeMessageImap();
        }
    }

    private function decodeMessagePear()
    {
        $this->_message->headerinfo = $this->mailbox->getParsedHeaders($this->_message->messageNB);
        if (empty($this->_message->headerinfo['subject'])) {
            return false;
        }
        $this->_message->text = '';
        $this->_message->html = $this->mailbox->getBody($this->_message->messageNB);

        if (!empty($this->_message->headerinfo['content-type']) && strpos($this->_message->headerinfo['content-type'], 'boundary') !== false) {
            $matches = [];
            preg_match('#boundary="([^"]+)"#i', $this->_message->headerinfo['content-type'], $matches);

            if (!empty($matches[1]) && strpos($this->_message->html, $matches[1]) !== false) {
                $inlineImages = [];

                $segments = explode('--'.$matches[1], $this->_message->html);
                foreach ($segments as $segment) {
                    if (strpos($segment, 'Content-Type: text/plain') !== false) {
                        $matches = [];
                        preg_match('#boundary="([^"]+)"#i', $segment, $matches);

                        if (!empty($matches[1])) {
                            $parts = explode('--'.$matches[1], $segment);

                            foreach ($parts as $onePart) {
                                $content = trim(preg_replace('#(charset=".*?\r\n)|Content-(Type|ID|Disposition|Transfer-Encoding):.*?\r\n#is', "", $onePart));

                                if (strpos($onePart, 'Content-Transfer-Encoding') !== false) {
                                    preg_match('#Content-Transfer-Encoding: (.+)#i', $onePart, $encoding);
                                    $encoding = trim($encoding[1]);
                                    $content = mb_convert_encoding($content, 'UTF-8', $encoding);
                                }

                                if (strpos($onePart, "Content-Type: text/plain") !== false) {
                                    $this->_message->text .= ' '.$content;
                                }
                                if (strpos($onePart, "Content-Type: text/html") !== false) {
                                    $this->_message->html .= ' '.$content;
                                }
                            }
                        } else {
                            $this->_message->html .= ' '.trim(preg_replace('#(charset=".*?\r\n)|Content-(Type|ID|Disposition|Transfer-Encoding):.*?\r\n#is', "", $segment));
                        }
                    } elseif (preg_match("#Content-Type: .*?/(png|jpg|jpeg|gif)#i", $segment) !== false) {
                        preg_match('#name="([^"]+)"#i', $segment, $filename);
                        if (empty($filename) || empty($filename[1])) {
                            continue;
                        }
                        $name = trim($filename[1]);
                        $filename = $name;
                        $extensionPos = strrpos($filename, '.');

                        if ($extensionPos === false) {
                            continue;
                        }

                        $extension = substr($filename, $extensionPos + 1);

                        $filename = preg_replace('#[^a-zA-Z0-9]#Uis', '_', substr($filename, 0, $extensionPos));

                        $uploadFolder = str_replace(['/', '\\'], DS, acym_getFilesFolder());
                        $pathToUpload = ACYM_ROOT.trim($uploadFolder, DS).DS;

                        if (file_exists($pathToUpload.$filename.'.'.$extension)) {
                            $fileNumber = 1;
                            while (file_exists($pathToUpload.$filename.'_('.$fileNumber.').'.$extension)) {
                                $fileNumber++;
                            }
                            $filename = $filename.'_('.$fileNumber.')';
                        }

                        preg_match('#Content-ID: <([^>]+)>#i', $segment, $contentID);
                        if (empty($contentID) || empty($contentID[1])) {
                            continue;
                        }
                        $contentID = trim($contentID[1]);

                        $data = trim(substr($segment, strpos($segment, "\r\n\r\n")));
                        if (strpos($segment, 'Content-Transfer-Encoding: base64') !== false) {
                            $data = base64_decode($data);
                        }


                        try {
                            if (acym_writeFile($pathToUpload.$filename.'.'.$extension, $data)) {
                                $inlineImages['cid:'.$contentID] = acym_rootURI().$uploadFolder.'/'.$filename.'.'.$extension;
                            }
                        } catch (\Exception $e) {
                            $this->display(acym_translation_sprintf('ACYM_ERROR_UPLOAD_ATTACHMENT', $filename.'.'.$extension, $e->getMessage()), false);
                        }
                    }
                }

                if (!empty($inlineImages)) {
                    $this->_message->html = str_replace(array_keys($inlineImages), $inlineImages, $this->_message->html);
                }
            }
        }

        $this->_message->subject = $this->decodeHeader($this->_message->headerinfo['subject']);
        if (empty($this->_message->header)) {
            $this->_message->header = new \stdClass();
        }
        $this->_message->header->sender_email = @$this->_message->headerinfo['return-path'];
        if (is_array($this->_message->header->sender_email)) {
            $this->_message->header->sender_email = reset($this->_message->header->sender_email);
        }
        if (preg_match($this->detectEmail, $this->_message->header->sender_email, $results)) {
            $this->_message->header->sender_email = $results[0];
        }
        $this->_message->header->sender_name = strip_tags(@$this->_message->headerinfo['from']);
        $this->_message->header->reply_to_email = $this->_message->header->sender_email;
        $this->_message->header->reply_to_name = $this->_message->header->sender_name;
        $this->_message->header->from_email = $this->_message->header->sender_email;
        $this->_message->header->from_name = $this->_message->header->sender_name;

        return true;
    }

    private function decodeMessageImap()
    {
        $this->_message->structure = imap_fetchstructure($this->mailbox, $this->_message->messageNB);

        if (empty($this->_message->structure)) {
            return false;
        }
        $this->_message->headerinfo = imap_fetchheader($this->mailbox, $this->_message->messageNB);

        $this->_message->html = '';
        $this->_message->text = '';

        if ($this->_message->structure->type == 1) {
            $this->_message->contentType = 2;
            if ($this->_message->structure->subtype == "MIXED") {
                $allParts = $this->explodeBodyMixed($this->_message->structure);
            } else {
                $allParts = $this->explodeBody($this->_message->structure);
            }

            $text = '';
            foreach ($allParts as $num => $onePart) {
                $decodedContent = $this->decodeContent(imap_fetchbody($this->mailbox, $this->_message->messageNB, $num), $onePart);
                if ($onePart->subtype == 'HTML') {
                    $this->_message->html .= $decodedContent;
                } else {
                    if ($onePart->subtype == 'PLAIN') {
                        $text .= $decodedContent."\n";
                    }
                    $this->_message->text .= $decodedContent."\n\n- - -\n\n";
                }
            }

            if (!empty($this->action) && empty($this->_message->html)) {
                $this->_message->html = $text."\n";
            }
            if (!empty($this->inlineImages)) {
                $this->_message->html = str_replace(array_keys($this->inlineImages), $this->inlineImages, $this->_message->html);
            }
        } else {
            if ($this->_message->structure->subtype == 'HTML') {
                $this->_message->contentType = 1;
                $this->_message->html = $this->decodeContent(imap_body($this->mailbox, $this->_message->messageNB), $this->_message->structure);
            } else {
                $this->_message->contentType = 0;
                $this->_message->text = $this->decodeContent(imap_body($this->mailbox, $this->_message->messageNB), $this->_message->structure);
            }
        }

        $this->_message->subject = $this->decodeHeader($this->_message->subject);

        $this->decodeAddressImap('sender');
        $this->decodeAddressImap('from');
        $this->decodeAddressImap('reply_to');
        $this->decodeAddressImap('to');

        return true;
    }

    public function handleMessages()
    {
        $maxMessages = min($this->nbMessages, $this->config->get('bounce_max', 0));
        if (empty($maxMessages)) {
            $maxMessages = min($this->nbMessages, 500);
        }

        if ($this->report) {
            if (function_exists('apache_get_modules')) {
                $modules = apache_get_modules();
                $this->mod_security2 = in_array('mod_security2', $modules);
            }

            @ini_set('output_buffering', 'off');
            @ini_set('zlib.output_compression', 0);


            if (!headers_sent()) {
                while (ob_get_level() > 0 && $this->obend++ < 3) {
                    @ob_end_flush();
                }
            }

            $disp = "<div style='position:fixed; top:3px;left:3px;background-color : white;border : 1px solid grey; padding : 3px;font-size:14px'>";
            $disp .= acym_translation('ACYM_BOUNCE_HANDLING');
            $disp .= ':  <span id="counter">0</span> / '.$maxMessages;
            $disp .= '</div>';
            $disp .= '<script type="text/javascript" language="javascript">';
            $disp .= 'var mycounter = document.getElementById("counter");';
            $disp .= 'function setCounter(val){ mycounter.innerHTML=val;}';
            $disp .= '</script>';
            echo $disp;
            if (function_exists('ob_flush')) {
                @ob_flush();
            }
            if (!$this->mod_security2) {
                @flush();
            }
        }

        $rules = $this->ruleClass->getAll(null, true);

        $msgNB = $maxMessages;
        $listClass = new ListClass();
        $this->allLists = $listClass->getAll('id');

        $replyemail = $this->config->get('reply_email');
        $fromemail = $this->config->get('from_email');
        $bouncemail = $this->config->get('bounce_email');
        $removeEmails = '#('.str_replace(['%'], ['@'], $this->config->get('bounce_username'));
        if (!empty($bouncemail)) {
            $removeEmails .= '|'.$bouncemail;
        }
        if (!empty($fromemail)) {
            $removeEmails .= '|'.$fromemail;
        }
        if (!empty($replyemail)) {
            $removeEmails .= '|'.$replyemail;
        }
        $removeEmails .= ')#i';

        while (($msgNB > 0) && ($this->_message = $this->getMessage($msgNB))) {
            if ($this->report) {
                echo '<script type="text/javascript" language="javascript">setCounter('.($maxMessages - $msgNB + 1).')</script>';
                if (function_exists('ob_flush')) {
                    @ob_flush();
                }
                if (!$this->mod_security2) {
                    @flush();
                }
            }
            $this->_message->messageNB = $msgNB;
            $msgNB--;

            if (!$this->decodeMessage()) {
                $this->display(acym_translation('ACYM_ERROR_RETRIEVING_MESSAGE'), false, $maxMessages - $this->_message->messageNB + 1);
                continue;
            }

            if (empty($this->_message->subject)) {
                $this->_message->subject = 'empty subject';
            }

            $this->_message->analyseText = $this->_message->html.' '.$this->_message->text;
            if (!empty($this->_message->header->from_email)) {
                $this->_message->analyseText .= ' '.$this->_message->header->from_email;
            }
            $this->display('<b>'.acym_translation('ACYM_EMAIL_SUBJECT').' : '.strip_tags($this->_message->subject).'</b>', false, $maxMessages - $this->_message->messageNB + 1);

            preg_match('#AC([0-9]+)Y([0-9]+)BA#i', $this->_message->analyseText, $resultsVars);
            if (!empty($resultsVars[1])) {
                $this->_message->userid = $resultsVars[1];
            }
            if (!empty($resultsVars[2])) {
                $this->_message->mailid = $resultsVars[2];
            }

            if (empty($this->_message->userid)) {
                preg_match_all($this->detectEmail, $this->_message->analyseText, $results);

                if (empty($results[0])) {
                    preg_match_all($this->detectEmail2, $this->_message->analyseText, $results2);
                    for ($i = 0 ; $i < count($results2[0]) ; $i++) {
                        $results[0][] = $results2[3][$i].'@'.$results2[1][$i];
                    }
                }

                if (!empty($results[0])) {
                    $alreadyChecked = [];
                    foreach ($results[0] as $oneEmail) {
                        if (!preg_match($removeEmails, $oneEmail)) {
                            $this->_message->subemail = strtolower($oneEmail);
                            if (!empty($alreadyChecked[$this->_message->subemail])) {
                                continue;
                            }

                            $user = $this->userClass->getOneByEmail($this->_message->subemail);
                            if (!empty($user)) $this->_message->userid = $user->id;

                            if (empty($this->_message->userid) && preg_match('#(&|\?)id=([0-9]+)&#ims', $this->_message->subemail, $subid)) {
                                $user = $this->userClass->getOneById($subid[2]);
                                $this->_message->userid = $user->id;
                                $this->_message->subemail = $user->email;
                            }

                            $alreadyChecked[$this->_message->subemail] = true;
                            if (!empty($this->_message->userid)) {
                                break;
                            }
                        }
                    }
                }
            }

            if (empty($this->_message->mailid) && !empty($this->_message->userid)) {
                $this->_message->mailid = acym_loadResult(
                    'SELECT `mail_id` FROM #__acym_user_stat WHERE `user_id` = '.intval($this->_message->userid).' ORDER BY `send_date` DESC'
                );
            }


            foreach ($rules as $oneRule) {
                if ($this->handleRule($oneRule)) {
                    break;
                }
            }

            if ($msgNB % 50 == 0) {
                $this->userActions();
            }

            if (!empty($this->stoptime) && time() > $this->stoptime) {
                break;
            }
        }


        $this->userActions();

        $this->close();
    }

    private function userActions()
    {

        if (!empty($this->deletedUsers)) {
            acym_arrayToInteger($this->deletedUsers);
            $this->userClass->delete($this->deletedUsers);
            $this->deletedUsers = [];
        }
        if (!empty($this->blockedUsers)) {
            acym_arrayToInteger($this->blockedUsers);
            $allUsersId = implode(',', $this->blockedUsers);
            acym_query('UPDATE `#__acym_user` SET `active` = 0 WHERE `id` IN ('.$allUsersId.')');
            acym_query('DELETE FROM `#__acym_queue` WHERE `user_id` IN ('.$allUsersId.')');
            $this->blockedUsers = [];
        }

        if (!empty($this->bounceMessages)) {
            foreach ($this->bounceMessages as $mailid => $bouncedata) {

                $updateBounceDetails = '';
                if (!empty($bouncedata['bouncedetails'])) {
                    $bouncedetails = acym_loadResult('SELECT `bounce_details` FROM #__acym_mail_stat WHERE mail_id = '.intval($mailid));
                    if (!empty($bouncedetails)) {
                        $bouncedetails = unserialize($bouncedetails);
                    } else {
                        $bouncedetails = [];
                    }

                    foreach ($bouncedata['bouncedetails'] as $ruleName => $nbTimes) {
                        if (empty($bouncedetails[$ruleName])) {
                            $bouncedetails[$ruleName] = 0;
                        }
                        $bouncedetails[$ruleName] += $nbTimes;
                    }

                    $updateBounceDetails = ' , `bounce_details` = '.acym_escapeDB(serialize($bouncedetails));
                }

                if (!empty($bouncedata['userids'])) {
                    $valueInsert = [];
                    foreach ($bouncedata['ruletriggered'] as $userid => $rulename) {
                        $valueInsert[] = '('.intval($mailid).','.intval($userid).',1,'.acym_escapeDB($rulename).')';
                    }
                    acym_query(
                        'INSERT INTO #__acym_user_stat (mail_id,user_id,bounce,bounce_rule) VALUES '.implode(
                            ',',
                            $valueInsert
                        ).' ON DUPLICATE KEY UPDATE `bounce` = `bounce` + 1, bounce_rule=VALUES(bounce_rule)'
                    );
                    acym_arrayToInteger($bouncedata['userids']);
                    $realUniqueBounces = acym_loadResult(
                        'SELECT COUNT(*) 
                        FROM #__acym_user_stat 
                        WHERE `bounce` = 1 
                            AND `user_id` IN ('.implode(',', $bouncedata['userids']).') 
                            AND `mail_id` = '.intval($mailid)
                    );
                    $bouncedata['nbbounces'] = $bouncedata['nbbounces'] - count($bouncedata['userids']) + $realUniqueBounces;
                }

                acym_query(
                    'UPDATE #__acym_mail_stat SET `bounce_unique` = `bounce_unique` + '.intval($bouncedata['nbbounces']).$updateBounceDetails.' 
                    WHERE `mail_id` = '.intval($mailid).' 
                    LIMIT 1'
                );
            }
            $this->bounceMessages = [];
        }
    }

    private function handleRule(&$oneRule)
    {
        $regex = $oneRule->regex;
        if (empty($regex)) {
            return false;
        }


        $analyseText = '';
        if (in_array('senderInfo', $oneRule->executed_on)) {
            $analyseText .= ' ';
            if (isset($this->_message->header->sender_name)) {
                $analyseText .= $this->_message->header->sender_name;
            }
            if (isset($this->_message->header->sender_email)) {
                $analyseText .= $this->_message->header->sender_email;
            }
        }
        if (in_array('subject', $oneRule->executed_on)) {
            $analyseText .= ' '.$this->_message->subject;
        }
        if (in_array('body', $oneRule->executed_on)) {
            if (!empty($this->_message->html)) {
                $analyseText .= ' '.$this->_message->html;
            }
            if (!empty($this->_message->text)) {
                $analyseText .= ' '.$this->_message->text;
            }
        }

        $analyseText = str_replace(["\n", "\r", "\t"], ' ', $analyseText);

        if (!preg_match('#'.$regex.'#ims', $analyseText)) {
            return false;
        }

        $message = acym_translation('ACYM_BOUNCE_RULE').' ['.acym_translation('ACYM_ID').' '.$oneRule->id.'] '.acym_translation($oneRule->name).' : ';
        $message .= $this->actionUser($oneRule);
        $message .= $this->actionMessage($oneRule);

        $this->display($message, true);

        return true;
    }

    private function actionUser(&$oneRule)
    {
        $message = '';

        if (empty($this->_message->userid)) {
            $message .= 'user not identified';
            if (!empty($this->_message->subemail)) {
                $message .= ' ( '.$this->_message->subemail.' ) ';
            }

            return $message;
        }

        if (in_array('delete_user_subscription', $oneRule->action_user) || in_array('unsubscribe_user', $oneRule->action_user) || in_array(
                'subscribe_user',
                $oneRule->action_user
            )) {
            $status = $this->userClass->getSubscriptionStatus($this->_message->userid);
            if (empty($this->_message->subemail)) {
                $currentUser = $this->userClass->getOneById($this->_message->userid);
                if (!empty($currentUser->email)) {
                    $this->_message->subemail = $currentUser->email;
                }
            }
        }

        if (empty($this->_message->subemail)) {
            $this->_message->subemail = $this->_message->userid;
        }


        $mail = $this->mailClass->getOneById($this->_message->mailid);
        if ($oneRule->increment_stats && !empty($this->_message->mailid) && !empty($mail)) {

            if (empty($this->bounceMessages[$this->_message->mailid])) {
                $this->bounceMessages[$this->_message->mailid] = [];
                $this->bounceMessages[$this->_message->mailid]['nbbounces'] = 0;
                $this->bounceMessages[$this->_message->mailid]['userids'] = [];
                $this->bounceMessages[$this->_message->mailid]['bouncedetails'] = [];
                $this->bounceMessages[$this->_message->mailid]['ruletriggered'] = [];
            }

            $this->bounceMessages[$this->_message->mailid]['nbbounces']++;

            $ruleName = $oneRule->name.' [ID '.$oneRule->id.'] ';
            if (empty($this->bounceMessages[$this->_message->mailid]['bouncedetails'][$ruleName])) {
                $this->bounceMessages[$this->_message->mailid]['bouncedetails'][$ruleName] = 1;
            } else {
                $this->bounceMessages[$this->_message->mailid]['bouncedetails'][$ruleName]++;
            }

            $user = $this->userClass->getOneById($this->_message->userid);
            if (!empty($this->_message->userid) && !in_array('delete_user', $oneRule->action_user) && !empty($user)) {
                $this->bounceMessages[$this->_message->mailid]['userids'][] = intval($this->_message->userid);
                $this->bounceMessages[$this->_message->mailid]['ruletriggered'][intval($this->_message->userid)] = $oneRule->name.' ['.acym_translation(
                        'ACYM_ID'
                    ).' '.$oneRule->id.']';
            }
        }

        if (!empty($oneRule->execute_action_after) && $oneRule->execute_action_after > 1) {
            if (empty($this->_message->mailid)) {
                $this->_message->mailid = 0;
            }
            $nb = intval(
                    acym_loadResult(
                        'SELECT COUNT(mail_id) FROM #__acym_user_stat WHERE bounce > 0 AND user_id = '.intval($this->_message->userid).' AND mail_id != '.intval(
                            $this->_message->mailid
                        )
                    )
                ) + 1;

            if ($nb < $oneRule->execute_action_after) {
                $message .= ' | '.acym_translation_sprintf('ACYM_BOUNCE_RECEIVED', $nb, $this->_message->subemail).' | '.acym_translation_sprintf(
                        'ACYM_BOUNCE_MIN_EXEC',
                        $oneRule->execute_action_after
                    );

                return $message;
            }
        }

        if (in_array('delete_user', $oneRule->action_user)) {
            $message .= ' | '.acym_translation_sprintf('ACYM_USER_X_DELETED', $this->_message->subemail);
            $this->deletedUsers[] = intval($this->_message->userid);

            return $message;
        }

        $listId = 0;
        if (in_array('subscribe_user', $oneRule->action_user) && !empty($oneRule->action_user['subscribe_user_list'])) {
            $listId = $oneRule->action_user['subscribe_user_list'];

            $listName = empty($this->allLists[$listId]->name) ? $listId : $this->allLists[$listId]->name;
            $message .= ' | ';
            if (isset($status[$listId])) {
                $message .= acym_translation_sprintf('ACYM_USER_X_NOT_SUBSCRIBED_TO', $this->_message->subemail, $listName);
                if ($status[$listId]->status == 1) {
                    $message .= acym_translation('ACYM_USER_ALREADY_SUBSCRIBED');
                } elseif ($status[$listId]->status == 0) {
                    $message .= acym_translation('ACYM_USER_ALREADY_UNSUBSCRIBED');
                }
            } else {
                $this->userClass->subscribe($this->_message->userid, $listId);
                $message .= acym_translation_sprintf('ACYM_USER_X_SUBSCRIBED_TO', $this->_message->subemail, $listName);
            }
        }

        if (in_array('delete_user_subscription', $oneRule->action_user)) {
            $unsubLists = array_diff(array_keys($status), [$listId]);
            if (!empty($unsubLists)) {
                $listnames = [];
                foreach ($unsubLists as $oneListId) {
                    if (!empty($this->allLists[$oneListId]->name)) {
                        $listnames[] = $this->allLists[$oneListId]->name;
                    } else {
                        $listnames[] = $oneListId;
                    }
                }
                $message .= ' | '.acym_translation_sprintf('ACYM_USER_X_REMOVED_FROM', $this->_message->subemail, implode(', ', $listnames));
                $this->userClass->removeSubscription($this->_message->userid, $unsubLists);
            } else {
                $message .= ' | '.acym_translation_sprintf('ACYM_USER_X_NOT_SUBSCRIBED', $this->_message->subemail);
            }
        }

        if (in_array('unsubscribe_user', $oneRule->action_user)) {
            $unsubLists = array_diff(array_keys($status), [$listId]);
            if (!empty($unsubLists)) {
                $listnames = [];
                foreach ($unsubLists as $oneListId) {
                    if (!empty($this->allLists[$oneListId]->name)) {
                        $listnames[] = $this->allLists[$oneListId]->name;
                    } else {
                        $listnames[] = $oneListId;
                    }
                }
                $this->userClass->unsubscribe($this->_message->userid, $unsubLists);
                $message .= ' | '.acym_translation_sprintf('ACYM_USER_X_UNSUBSCRIBED_FROM', $this->_message->subemail, implode(', ', $listnames));
            } else {
                $message .= ' | '.acym_translation_sprintf('ACYM_USER_X_NOT_SUBSCRIBED', $this->_message->subemail);
            }
        }

        if (in_array('block_user', $oneRule->action_user)) {
            $message .= ' | '.acym_translation_sprintf('ACYM_USER_X_BLOCKED', $this->_message->subemail.' ( '.acym_translation('ACYM_ID').' '.intval($this->_message->userid).' )');
            $this->blockedUsers[] = intval($this->_message->userid);
        }

        if (in_array('empty_queue_user', $oneRule->action_user)) {
            $affected = acym_query('DELETE FROM #__acym_queue WHERE user_id = '.intval($this->_message->userid));
            $message .= ' | '.acym_translation_sprintf(
                    'ACYM_USER_X_QUEUE',
                    $this->_message->subemail.' ( '.acym_translation('ACYM_ID').' '.intval($this->_message->userid).' )',
                    acym_translation_sprintf('ACYM_SUCC_DELETE_ELEMENTS', $affected)
                );
        }

        return $message;
    }

    private function actionMessage(&$oneRule)
    {
        $message = '';

        if (!empty($oneRule->action_message['forward_to'])) {
            if (strtolower($oneRule->action_message['forward_to']) == strtolower($this->config->get('bounce_username')) || strtolower(
                    $oneRule->action_message['forward_to']
                ) == strtolower($this->config->get('bounce_email'))) {
                $oneRule->action_message['forward_to'] = '';
                unset($oneRule->action_message['delete_message']);
                $message .= ' | '.acym_translation('ACYM_BOUNCE_NOT_FORWARD');
            }
        }


        if (in_array('save_message', $oneRule->action_message) && !empty($this->_message->userid) && !in_array('delete_user', $oneRule->action_user)) {
            $data = [];
            $data[] = 'SUBJECT::'.@htmlentities($this->_message->subject, ENT_COMPAT, 'UTF-8');
            $data[] = 'ACY_RULE::'.$oneRule->id.' '.$oneRule->name;
            $data[] = 'REPLYTO_ADDRESS::'.$this->_message->header->reply_to_name.' ( '.$this->_message->header->reply_to_email.' )';
            $data[] = 'FROM_ADDRESS::'.$this->_message->header->from_name.' ( '.$this->_message->header->from_email.' )';
            if (!empty($this->_message->html)) {
                $data[] = 'HTML_VERSION::'.@htmlentities($this->_message->html, ENT_COMPAT, 'UTF-8');
            }
            if (!empty($this->_message->text)) {
                $data[] = 'TEXT_VERSION::'.nl2br(@htmlentities($this->_message->text, ENT_COMPAT, 'UTF-8'));
            }
            $data[] = print_r($this->_message->headerinfo, true);
            if (empty($this->_message->mailid)) {
                $this->_message->mailid = 0;
            }
            $this->historyClass->insert($this->_message->userid, 'bounce', $data, $this->_message->mailid);
            $message .= ' | '.acym_translation_sprintf('ACYM_BOUNCE_MESSAGE_SAVED', $this->_message->userid);
        }

        $donotdelete = false;

        if (!empty($oneRule->action_message['forward_to'])) {
            $this->mailer->clearAll();
            $this->mailer->Subject = 'BOUNCE FORWARD : '.$this->_message->subject;

            if (substr_count($oneRule->action_message['forward_to'], '@') > 1) {
                $forwardAddresses = explode(';', str_replace([';', ','], ';', $oneRule->action_message['forward_to']));
            } else {
                $forwardAddresses = [$oneRule->action_message['forward_to']];
            }

            foreach ($forwardAddresses as $oneForwardAddress) {
                $this->mailer->AddAddress($this->mailer->cleanText($oneForwardAddress));
            }

            $info = acym_translation('ACYM_BOUNCE_RULE').' ['.acym_translation('ACYM_ID').' '.$oneRule->id.'] '.acym_translation($oneRule->name);
            if (!empty($this->_message->html)) {
                $this->mailer->isHTML(true);
                $this->mailer->Body = $info.'<br />'.$this->_message->html;
                if (!empty($this->_message->text)) {
                    $this->mailer->Body .= '<br /><br />-------<br />'.nl2br($this->_message->text);
                }
            } else {
                $this->mailer->isHTML(false);
                $this->mailer->Body = $info."\n".$this->_message->text;
            }

            $this->mailer->Body .= print_r($this->_message->headerinfo, true);
            $replyAddress = trim(@$this->_message->header->reply_to_email, '<> ');
            if (!empty($replyAddress)) {
                $this->mailer->AddReplyTo(trim($this->_message->header->reply_to_email, '<> '), $this->_message->header->reply_to_name);
            }

            if ($this->mailer->send()) {
                $message .= ' | '.acym_translation_sprintf('ACYM_FORWARDED_TO_X', $oneRule->action_message['forward_to']);
            } else {
                $message .= ' | '.acym_translation_sprintf('ACYM_NOT_FORWARDED_TO_X', $oneRule->action_message['forward_to'], $this->mailer->reportMessage);
                $donotdelete = true;
            }
        }

        if (in_array('delete_message', $oneRule->action_message) && !$donotdelete) {
            $message .= ' | '.acym_translation('ACYM_MESSAGE_DELETED');
            $this->deleteMessage($this->_message->messageNB);
        }

        return $message;
    }

    private function decodeAddressImap($type)
    {
        $address = $type.'address';
        $name = $type.'_name';
        $email = $type.'_email';
        if (empty($this->_message->$type)) {
            return false;
        }

        $var = $this->_message->$type;

        if (empty($this->_message->header)) {
            $this->_message->header = new \stdClass();
        }

        if (!empty($this->_message->$address)) {
            $this->_message->header->$name = $this->_message->$address;
        } else {
            $this->_message->header->$name = $var[0]->personal;
        }

        $this->_message->header->$email = $var[0]->mailbox.'@'.@$var[0]->host;

        return true;
    }


    private function display($message, $success = true, $num = '')
    {
        $this->messages[] = $message;

        if (!$this->report) return;

        $color = $success ? 'green' : 'blue';
        if (!empty($num)) {
            echo '<br />'.$num.' : ';
        } else {
            echo '<br />&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
        }

        echo '<span style="color: '.$color.'">'.$message.'</span>';

        if (function_exists('ob_flush')) {
            @ob_flush();
        }
        if (!$this->mod_security2) {
            @flush();
        }
    }

    private function decodeHeader($input)
    {
        $input = preg_replace('/(=\?[^?]+\?(q|b)\?[^?]*\?=)(\s)+=\?/i', '\1=?', $input);
        $currentCharset = false;

        while (preg_match('/(=\?([^?]+)\?(q|b)\?([^?]*)\?=)/i', $input, $matches)) {

            $encoded = $matches[1];
            $charset = $matches[2];
            $encoding = $matches[3];
            $text = $matches[4];

            switch (strtolower($encoding)) {
                case 'b':
                    $text = base64_decode($text);
                    break;

                case 'q':
                    $text = str_replace('_', ' ', $text);
                    preg_match_all('/=([a-f0-9]{2})/i', $text, $matches);
                    foreach ($matches[1] as $value) {
                        $text = str_replace('='.$value, chr(hexdec($value)), $text);
                    }
                    break;
            }
            $currentCharset = $charset;
            $input = str_replace($encoded, $text, $input);
        }

        if (!empty($currentCharset) && in_array($currentCharset, $this->allCharsets)) {
            $input = $this->encodingHelper->change($input, $currentCharset, 'UTF-8');
        }

        return $input;
    }

    private function explodeBodyMixed($struct, $path = "1")
    {
        $allParts = [];

        if (empty($struct->parts)) {
            return $allParts;
        }

        $pathPrefix = ($struct->subtype == "MIXED" && $path == '1') ? '' : $path.'.';
        foreach ($struct->parts as $i => $part) {
            $partPath = $pathPrefix.($i + 1);
            if ($part->type == 1) {
                $allParts = array_merge($this->explodeBodyMixed($part, $partPath), $allParts);
            } else {
                $allParts[$partPath] = $part;
            }
        }

        return $allParts;
    }

    private function explodeBody($struct, $path = "0", $inline = 0)
    {
        $allParts = [];

        if (empty($struct->parts)) {
            return $allParts;
        }

        $c = 0; //counts real content
        foreach ($struct->parts as $part) {
            if ($part->type == 1) {
                if ($part->subtype == "MIXED") { //Mixed:
                    $path = $this->incPath($path, 1); //refreshing current path
                    $newpath = $path.".0"; //create a new path-id (ex.:2.0)
                    $allParts = array_merge($this->explodeBody($part, $newpath), $allParts); //fetch new parts
                } else { //Alternativ / rfc / signed
                    $newpath = $this->incPath($path, 1);
                    $path = $this->incPath($path, 1);
                    $allParts = array_merge($this->explodeBody($part, $newpath, 1), $allParts);
                }
            } else {
                $c++;
                if ($c == 1 && $inline) {
                    $path = $path.".0";
                }
                $path = $this->incPath($path, 1);
                $allParts[$path] = $part;
            }
        }

        return $allParts;
    }

    private function incPath($path, $inc)
    {
        $newPath = '';
        $path_elements = explode(".", $path);
        $limit = count($path_elements);
        for ($i = 0 ; $i < $limit ; $i++) {
            if ($i == $limit - 1) { //last element
                $newPath .= $path_elements[$i] + $inc; // new Part-Number
            } else {
                $newPath .= $path_elements[$i]."."; //rebuild "1.2.2"-Chronology
            }
        }

        return $newPath;
    }

    private function decodeContent($content, $structure)
    {
        $encoding = $structure->encoding;

        if ($encoding == 2) {
            $content = imap_binary($content);
        } elseif ($encoding == 3) {
            $content = imap_base64($content);
        } elseif ($encoding == 4) {
            $content = imap_qprint($content);
        }

        if (!empty($this->action)) {
            $charset = $this->getMailParam($structure, 'charset');
            if (!empty($charset) && strtoupper($charset) != 'UTF-8') {
                $content = $this->encodingHelper->change($content, $charset, 'UTF-8');
            }

            return $content;
        }

        return substr($content, 0, 100000);
    }

    private function getMailParam($params, $name)
    {
        $searchIn = [];

        if ($params->ifparameters) {
            $searchIn = array_merge($searchIn, $params->parameters);
        }
        if ($params->ifdparameters) {
            $searchIn = array_merge($searchIn, $params->dparameters);
        }

        if (empty($searchIn)) {
            return false;
        }

        foreach ($searchIn as $num => $values) {
            if (strtolower($values->attribute) == $name) {
                return $values->value;
            }
        }
    }

    public function getErrors()
    {
        $return = [];
        if ($this->usePear) {
        } else {
            if (!function_exists('imap_alerts')) {
                $return[] = 'The IMAP extension could not be loaded, please change your PHP configuration to enable it or use the pop3 method without imap extension';

                return $return;
            }
            $alerts = imap_alerts();
            $errors = imap_errors();
            if (!empty($alerts)) {
                $return = array_merge($return, $alerts);
            }
            if (!empty($errors)) {
                $return = array_merge($return, $errors);
            }
        }

        return $return;
    }
}
