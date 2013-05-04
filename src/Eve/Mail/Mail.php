<?php
/**
 * @group exceptions
 */
namespace Eve\Mail;

#class Mail extends \PHPMailer
class Mail
{
	/** @var PHPMailer */
	protected $phpmailer;

	/**
	 * @param \PHPMailer $phpmailer
	 */
	public function __construct($phpmailer = null)
	{
		if (null !== $phpmailer && $phpmailer instanceof \PHPMailer) {
			$this->phpmailer = $phpmailer;
		} else {
			$this->phpmailer = new \PHPMailer();
		}
	}

	/**
	 * Getter/setter for subject
	 * @param string $value
	 * @return string|$this
	 */
	public function subject($value = null)
	{
		if (null !== $value) {
			$this->phpmailer->Subject = $value;
			return $this;
		}

		return $this->phpmailer->Subject;
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
			$this->phpmailer->SetFrom($address, $name, $auto);
			return $this;
		}

		return array('email' => $this->phpmailer->From, 'name' => $this->phpmailer->FromName);
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
			$this->phpmailer->AddReplyTo($address, $name);
			return $this;
		}

		return $this->phpmailer->ReplyTo;
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
			$this->phpmailer->AddAddress($address, $name);
			return $this;
		}

		return $this->phpmailer->all_recipients;
	}

	 /**
		* Getter/setter for text body
		* @param string $value
		* @return string|$this
		*/
	public function bodyAlt($value = null)
	{
		if (null !== $value) {
			$this->phpmailer->AltBody = $value;
			return $this;
		}

		return $this->phpmailer->AltBody;
	}

	 /**
		* Getter/setter for html body
		* @param string $value
		* @return string|$this
		*/
	public function bodyHtml($value = null)
	{
		if (null !== $value) {
			$this->phpmailer->MsgHTML($value);
			return $this;
		}

		return $this->phpmailer->MsgHTML;
	}

	/**
	 * Getter/setter for attachments
	 * @param string $value
	 */
	public function attachment($value = null)
	{
		if (null !== $value) {
			$this->phpmailer->AddAttachment($value);
			return $this;
		}

		return $this->phpmailer->attachment;
	}

	 /**
		* Getter/setter for host
		* @param string $value
		* @return string|$this
		*/
	public function host($value = null)
	{
		if (null !== $value) {
			$this->phpmailer->Host = $value;
			return $this;
		}

		return $this->phpmailer->Host;
	}

	 /**
		* Getter/setter for smtp auth
		* @param string $value
		* @return string|$this
		*/
	public function auth($value = null)
	{
		if (null !== $value) {
			$this->phpmailer->SMTPAuth = (bool) $value;
			return $this;
		}

		return $this->phpmailer->SMTPAuth;
	}

	 /**
		* Getter/setter for smtp secure
		* @param string $value
		* @return string|$this
		*/
	public function secure($value = null)
	{
		if (null !== $value) {
			$this->phpmailer->SMTPSecure = $value;
			return $this;
		}

		return $this->phpmailer->SMTPSecure;
	}

	 /**
		* Getter/setter for smtp debug
		* @param string $value
		* @return string|$this
		*/
	public function debug($value = null)
	{
		if (null !== $value) {
			$this->phpmailer->SMTPDebug = $value;
			return $this;
		}

		return $this->phpmailer->SMTPDebug;
	}

	 /**
		* Getter/setter for port
		* @param string $value
		* @return string|$this
		*/
	public function port($value = null)
	{
		if (null !== $value) {
			$this->phpmailer->Port = (int) $value;
			return $this;
		}

		return $this->phpmailer->Port;
	}

	 /**
		* Getter/setter for username
		* @param string $value
		* @return string|$this
		*/
	public function username($value = null)
	{
		if (null !== $value) {
			$this->phpmailer->Username = $value;
			return $this;
		}

		return $this->phpmailer->Username;
	}

	 /**
		* Getter/setter for password
		* @param string $value
		* @return string|$this
		*/
	public function password($value = null)
	{
		if (null !== $value) {
			$this->phpmailer->Password = $value;
			return $this;
		}

		return $this->phpmailer->Password;
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
				$this->phpmailer->IsSendmail();
				break;
			case 'smtp':
				$this->phpmailer->IsSMTP();
				break;
			case 'qmail':
				$this->phpmailer->IsQmail();
				break;
			case 'gmail':
				$this->phpmailer->IsSMTP();
				$this->phpmailer->host('smtp.gmail.com');
				$this->phpmailer->port(587);
				$this->phpmailer->secure('tls');
				break;
			default:
				$this->phpmailer->IsSendmail();
				break;
		}

		return $this;
	}

	/**
	 * Passthru to phpmailer
	 */
	public function __call($method, $args)
	{
		return call_user_func_array(array($this->phpmailer, $method), $args);
	}
}
