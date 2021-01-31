<?php

namespace WC\Utilities\Mailer;

use Handlebars\Engine\Hbs;

final class Mail
{
    const KEY_FROM_ADDRESS = "fromAddress";
    const KEY_FROM_NAME = "fromName";
    const KEY_TO_EMAIL = "email";
    const KEY_TO_NAME = "name";
    private $envelop;
    private $templatingEngine = '';
    private $data = [];
    private $missingData = false;
    private $opensslPkcs7Encrypt = false;
    private $sslKey = '';
    private $sslCertificate = '';
    private $privateKeyPassword = ''; // optional
    private $sslCertChain = ''; // optional

    public function __construct(bool $isHTML, array $from, array $to, string $subject, string $message, array $data=array(), string $templatingEngine='')
    {
        $this->templatingEngine = $templatingEngine;
        $this->data = $data;
        $this->envelop = new Envelop(null, $isHTML);
        $this->prepareEnvelop($from, $to, $subject, $message);
    }

    public function setTemplatingEngine(string $s) {$this->templatingEngine = $s;}
    public function setData(array $data) {$this->data = $data;}

    public function setOpenSSLPkcs7Encrypt(bool $b) {$this->opensslPkcs7Encrypt = $b;}
    public function setSSLCertificate(string $s) {$this->sslCertificate = $s;}
    public function setSSLKey(string $s) {$this->sslKey = $s;}
    public function setPrivateKeyPassword(string $s) {$this->privateKeyPassword = $s;}
    public function setSSLCertChain(string $s) {$this->sslCertChain = $s;}

    public function setReplyTo(string $email, string $name='') {$this->envelop->replyTo($email, $name);}
    public function addCC(string $addr, string $name) {$this->envelop->addCC($addr, $name);}
    public function addBCC(string $addr, string $name) {$this->envelop->addBCC($addr, $name);}
    public function addAttachment(string $addr, string $name){$this->envelop->addAttachment($addr, $name);}


    private function prepareEnvelop(array $from, array $to, string $subject, string $emailMessage)
    {
        if (((isset($to[0]) && isset($to[0][self::KEY_TO_EMAIL])) || isset($to[self::KEY_TO_EMAIL])) && isset($from[self::KEY_TO_EMAIL]) && $subject && $emailMessage)
        {
            if ($this->templatingEngine === 'gx2cms') {
                Hbs::setExt($this->templatingEngine);
                Hbs::setProcessor(strtoupper($this->templatingEngine));
            }
            $emailMessage = Hbs::render($emailMessage, $this->data);

            $this->envelop->from($from[self::KEY_FROM_ADDRESS], isset($from[self::KEY_FROM_NAME])?$from[self::KEY_FROM_NAME]:'', true);
            if (isset($to[0]) && isset($to[0][self::KEY_TO_EMAIL])) {
                foreach ($to as $toEmail) {
                    $this->envelop->to($toEmail[self::KEY_TO_EMAIL], $toEmail[self::KEY_TO_NAME]);
                }
            }
            else if (isset($to[self::KEY_TO_EMAIL])) {
                $this->envelop->to($to[self::KEY_TO_EMAIL], $to[self::KEY_TO_NAME]);
            }
            $this->envelop->subject($subject);
            $this->envelop->message($emailMessage);

            if ($this->opensslPkcs7Encrypt && $this->sslKey && $this->sslCertificate && $this->sslCertChain)
            {
                $this->envelop->sign($this->sslCertificate, $this->sslKey, $this->privateKeyPassword, $this->sslCertChain);
            }
        }
    }

    public function deliver() {return $this->envelop instanceof Envelop ? $this->envelop->deliver() : false;}
}