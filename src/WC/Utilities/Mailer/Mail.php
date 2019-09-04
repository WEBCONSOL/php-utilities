<?php

namespace WC\Utilities\Mailer;

use WC\Models\ListModel;
use WC\Utilities\CustomResponse;

final class Mail
{
    const KEY_FROM_ADDRESS = "fromAddress";
    const KEY_FROM_NAME = "fromName";
    const KEY_TO_EMAIL = "email";
    const KEY_TO_NAME = "name";
    private $envelop;
    private $gx2cmsTmpl = true;
    private $missingData = false;

    public function __construct(array $to, string $tpl, array $data, string $subject, array $from=array(), bool $gx2cmsTmpl=true)
    {
        $this->gx2cmsTmpl = $gx2cmsTmpl;
        $this->prepareEnvelop($to, $tpl, $data, $subject, $from);
    }

    private function prepareEnvelop(array $from, array $to, string $subject, string $tpl, array $data=array())
    {
        if (((isset($to[0]) && isset($to[0][self::KEY_TO_EMAIL])) || isset($to[self::KEY_TO_EMAIL])) && isset($from[self::KEY_TO_EMAIL]) && $subject && $tpl)
        {
            if ($this->gx2cmsTmpl) {
                $ezpzTmpl = new GX2CMS\TemplateEngine\GX2CMS();
                $emailMessage = $ezpzTmpl->compile(new GX2CMS\TemplateEngine\Model\Context($data), new GX2CMS\TemplateEngine\Model\Tmpl($tpl));
            }
            else {
                $emailMessage = $tpl;
            }

            $this->envelop = new Envelop(null);
            $this->envelop->from($from[self::KEY_FROM_ADDRESS], isset($from[self::KEY_FROM_NAME])?$from[self::KEY_FROM_NAME]:'');
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
        }
    }

    public function deliver() {return $this->envelop instanceof Envelop ? $this->envelop->deliver() : false;}
}