<?php
/**
 * @group exceptions
 */
namespace Eve;

// Include phpmailer class which will then be extended
#include_once 'Eve/Mail/phpmailer.php';

#class Mail extends \PHPMailer
class Mail
{
    /**
     * Getter/setter for subject
     * @param string $value
     * @return string|$this
     */
    public function subject($value = null)
    {
        if (null !== $value) {
            $this->Subject = $value;

            return $this;
        }

        return $this->Subject;
    }

     /**
      * Getter/setter for from address/name
      * @param string $address
      * @param string $name
      * @return array|$this
      */
    public function from($address = null, $name = null, $auto = 1)
    {
        if (null !== $address) {
            $this->SetFrom($address, $name, $auto);

            return $this;
        }

        return array('email' => $this->From, 'name' => $this->FromName);
    }

     /**
      * Getter/setter for reply to address/name
      * @param string $address
      * @param string $name
      * @return array|$this
      */
    public function replyTo($address = null, $name = null)
    {
        if (null !== $address) {
            $this->AddReplyTo($address, $name);

            return $this;
        }

        return $this->ReplyTo;
    }

     /**
      * Getter/setter for reply to address/name
      * @param string $address
      * @param string $name
      * @return array|$this
      */
    public function addTo($address = null, $name = null)
    {
        if (null !== $address) {
            $this->AddAnAddress('to', $address, $name);

            return $this;
        }

        return $this->all_recipients;
    }

     /**
      * Getter/setter for text body
      * @param string $value
      * @return string|$this
      */
    public function bodyAlt($value = null)
    {
        if (null !== $value) {
            $this->AltBody = $value;

            return $this;
        }

        return $this->AltBody;
    }

     /**
      * Getter/setter for html body
      * @param string $value
      * @return string|$this
      */
    public function bodyHtml($value = null)
    {
        if (null !== $value) {
            $this->MsgHTML($value);

            return $this;
        }

        return $this->MsgHTML;
    }

    /**
     * Getter/setter for attachments
     * @param string $value
     */
    public function attachment($value = null)
    {
        if (null !== $value) {
            $this->AddAttachment($value);

            return $this;
        }

        return $this->attachment;
    }

     /**
      * Getter/setter for host
      * @param string $value
      * @return string|$this
      */
    public function host($value = null)
    {
        if (null !== $value) {
            $this->Host = $value;

            return $this;
        }

        return $this->Host;
    }

     /**
      * Getter/setter for smtp auth
      * @param string $value
      * @return string|$this
      */
    public function auth($value = null)
    {
        if (null !== $value) {
            $this->SMTPAuth = (bool) $value;

            return $this;
        }

        return $this->SMTPAuth;
    }

     /**
      * Getter/setter for smtp secure
      * @param string $value
      * @return string|$this
      */
    public function secure($value = null)
    {
        if (null !== $value) {
            $this->SMTPSecure = $value;

            return $this;
        }

        return $this->SMTPSecure;
    }

     /**
      * Getter/setter for smtp debug
      * @param string $value
      * @return string|$this
      */
    public function debug($value = null)
    {
        if (null !== $value) {
            $this->SMTPDebug = $value;

            return $this;
        }

        return $this->SMTPDebug;
    }

     /**
      * Getter/setter for port
      * @param string $value
      * @return string|$this
      */
    public function port($value = null)
    {
        if (null !== $value) {
            $this->Port = (int) $value;

            return $this;
        }

        return $this->Port;
    }

     /**
      * Getter/setter for username
      * @param string $value
      * @return string|$this
      */
    public function username($value = null)
    {
        if (null !== $value) {
            $this->Username = $value;

            return $this;
        }

        return $this->Username;
    }

     /**
      * Getter/setter for password
      * @param string $value
      * @return string|$this
      */
    public function password($value = null)
    {
        if (null !== $value) {
            $this->Password = $value;

            return $this;
        }

        return $this->Password;
    }

     /**
      * Set mode
      * @param string $value
      * @return $this
      */
    public function mode($value)
    {
        switch (strtolower($value)) {
            case 'sendmail':
                $this->IsSendmail();
                break;
            case 'smtp':
                $this->IsSMTP();
                break;
            case 'qmail':
                $this->IsQmail();
                break;
            case 'gmail':
                $this->IsSMTP();
                $this->host('smtp.gmail.com');
                $this->port(587);
                $this->secure('tls');
                break;
            default:
                $this->IsSendmail();
                break;
        }

        return $this;
    }
}
