<?php
/**
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2007-2008, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-class@gmail.com>
 */

require_once("phpmailer/class.phpmailer.php");

/**
 * Mail Sender
 *
 * Subclass of PHPMailer, make it easier to use.
 *
 * Usage:
 * <code>
 * $m = new MailSender();
 * $m->SetHost('ssl://smtp.gmail.com', 465, true);
 * $m->SetTo($mail_to);
 * $m->SetFrom('Who send it ?');
 * $m->SetAuth($mail_user, $mail_pass);
 * $m->SetSubject($mail_subject);
 * $m->SetBody($mail_body);
 * $m->SetAttach($mail_attach);
 * $m->Send();
 * </code>
 * @package		fwolflib
 * @subpackage	class
 * @copyright	Copyright 2007-2008, Fwolf
 * @author		Fwolf <fwolf.aide+fwolflib-class@gmail.com>
 * @since		2007-03-29
 * @version		$Id$
 */
class Mailsender extends PHPMailer
{
	/**
	 * Mail attachment
	 * Array of string
	 * @var	array
	 */
	public $mAttach = array();

	/**
	 * Mail body
	 * @var	string
	 */
	public $mBody = '';

	/**
	 * Charset of mail
	 * @var	string
	 */
	public $mCharset = 'utf-8';

	/**
	 * Encoding method of mail body
	 * @var	string
	 */
	public $mEncoding = 'base64';

	/**
	 * Error count
	 * Reset when mail send success
	 * @var	int
	 */
	public $mErrorCount = 0;

	/**
	 * Error message
	 * Reset when mail send success
	 * @var string
	 */
	public $mErrorMsg = '';

	/**
	 * Mail from address
	 * @var	string
	 */
	public $mFrom = '';

	/**
	 * Mail from name
	 * @var	string
	 */
	public $mFromName = 'Aliens';

	/**
	 * Mail host
	 * Don't include port number
	 * @see	$mPort
	 * @var	string
	 */
	public $mHost = '';

	/**
	 * Html format mail ?
	 * @var	boolean
	 */
	public $mIsHtml = false;

	/**
	 * Auth type
	 * Smtp default.
	 * @var	string
	 */
	public $mIsSmtp = true;

	/**
	 * Pass to login mail host
	 * @var	string
	 */
	public $mPass = '';

	/**
	 * Mail host port number
	 * @var	int
	 */
	public $mPort = 25;

	/**
	 * Keep smtp connection to furture useage ?
	 * Var in phpmailer is $SMTPKeepAlive default false
	 * @var	boolean
	 */
	public $mSmtpKeepAlive = false;

	/**
	 * Subject of mail
	 * @var	string
	 */
	public $mSubject = '';

	/**
	 * Address to be mailed to
	 * Parsed data, always is an array
	 * @var	array
	 */
	public $mTo = array();

	/**
	 * Username on mail host
	 * Some host is xxx, while some is xxx@mail.com
	 * @var	string
	 */
	public $mUser = '';


	/**
	 * Construct
	 */
	public function __construct()
	{
		//parent::PHPMailer();
	} // end of func construct


	/**
	 * Parse mail to address
	 * Input any type address, output standard array of address
	 * Parse string including email name and address to address=>name array.
	 *
	 * @var	mixed	$to
	 * @return	array
	 */
	public function ParseTo($to)
	{
		if (is_array($to))
			return $to;
		else
		{
			//$addresses = ',,,;;; "@1>"1<1 ,\';" <1@1.com.cn> , 2@2.com, f <f@f.com.cn> ,,39@39.cc,;;; ';
			//$addresses = ', fwolfcn@gmail.com';

			//First, find all mail address out
			$j = preg_match_all('/[\s<]?([\w\d\-_\.\+]+@([\w\d\-_]+\.){1,4}\w+)[\s>]?/', $to, $addr_addr);

			//if got addresses, find names according there position in string
			$addr = array();
			if (0 < $j)
			{
				$addr_addr = $addr_addr[1];
				for ($i=0; $i<$j; $i++)
				{
					//this can always find
					$k = strpos($to, $addr_addr[$i]);
					$name = substr($to, 0, $k);
					//prepare for next loop
					$to= substr($to, $k + strlen($addr_addr[$i]));
					//trim string we parsed out
					$name = trim($name, ' \t<>;,"');
					//gerenate addr array like address=>name style
					$addr[$addr_addr[$i]] = $name;
				}
				//foreach ($addr as $key=>$val)
				//	echo $key . '=>' . $val . "\n";
			}
			return($addr);
		}
	} // end of func ParseTo


	/**
	 * Prepare - Common setup
	 */
	public function Prepare()
	{
		// NOTICE --> Char'S'et
		$this->CharSet = $this->mCharset;
		$this->Encoding = $this->mEncoding;
		if (true == $this->mIsSmtp)
			$this->IsSMTP();
		if (true == $this->mIsHtml)
			$this->IsHTML(true);
		else
			$this->IsHTML(false);
	} // end of func Prepare


	/**
	 * Send mail
	 * @param	string	$from	Only xxx@mail.com format, no fromname
	 * @param	mixed	$to
	 * @param	string	$subject
	 * @param	string	$body
	 * @param	mixed	$attach
	 * @return	boolean
	 */
	public function Send($from = '', $to = '', $subject = '', $body = '', $attach = '')
	{
		$this->Prepare();
		if (!empty($from))
			$this->SetFrom($from);
		if (!empty($to))
			$this->SetTo($to);
		if (!empty($subject))
			$this->SetSubject($subject);
		if (!empty($body))
			$this->SetBody($body);
		if (!empty($attach))
			$this->SetAttach($attach);

		$sok = parent::Send();
		if (false == $sok)
		{
			$this->mErrorCount ++;
			$this->mErrorMsg = $this->ErrorInfo;
		}
		else
		{
			$this->mErrorCount = 0;
			$this->mErrorMsg = '';
		}

		if (false == $this->mSmtpKeepAlive)
			$this->SmtpClose();
		return $sok;
	} // end of function Send


	/**
	 * Set mail attachment
	 * @param	mixed	$attach
	 */
	public function SetAttach($attach)
	{
		if (is_array($attach))
		{
			$this->mAttach = &$attach;
			$this->ClearAttachments();
			foreach ($attach as $att)
				$this->AddAttachment($att);
		}
		elseif (!empty($attach))
		{
			$this->mAttach = array($attach);
			$this->AddAttachment($attach);
		}
	} // end of func SetAttach


	/**
	 * Set host auth information
	 * @param	string	$userid
	 * @param	string	$passwd
	 */
	public function SetAuth($userid, $passwd)
	{
		$this->mUser = $userid;
		$this->mPass = $passwd;
		$this->Username = $this->mUser;
		$this->Password = $this->mPass;
	} // end of func SetAuth


	/**
	 * Set mail body content
	 * @param	string	$body
	 */
	public function SetBody($body)
	{
		$this->mBody = $body;
		$this->Body = $this->mBody;
	} // end of func SetBody


	/**
	 * Set from & from name
	 * @param	string	$from
	 * @param	string	$fromname
	 */
	public function SetFrom($from, $fromname = 'Aliens')
	{
		$this->mFrom = $from;
		$this->mFromName = $fromname;
		$this->From = $this->mFrom;
		$this->FromName = $this->mFromName;
	} // end of func SetFrom


	/**
	 * Set host information
	 * @param	string	$addr
	 * @param	int		$port
	 * @param	boolean	$issmtp
	 */
	public function SetHost($addr, $port = 25, $issmtp = true)
	{
		$this->mHost = $addr;
		$this->mPort = $port;
		$this->mIsSmtp = $issmtp;
		$this->Host = $this->mHost;
		$this->Port = $this->mPort;
		$this->SMTPAuth = $this->mIsSmtp;
	} // end of func SetHost


	/**
	 * Set mail subject
	 * @param	string	$sub
	 */
	public function SetSubject($sub)
	{
		$this->mSubject = $sub;
		$this->Subject = $this->mSubject;
	} // end of func SetSubject


	/**
	 * Set address to mail to
	 * @param	mixed	$to
	 */
	public function SetTo($to)
	{
		$to_ar = $this->ParseTo($to);
		$this->mTo = &$to_ar;
		$this->ClearAddresses();
		foreach ($to_ar as $key => $val)
			$this->AddAddress($key, $val);
	} // end of func SetTo

}
?>