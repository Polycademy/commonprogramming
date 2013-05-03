<?php

namespace PolyAuth;

//for database
use PDO;
use PDOException;

//for logger
use Psr\Log\LoggerInterface;

//for options
use PolyAuth\Options;

//for languages
use PolyAuth\Language;

//this class handles the sending of emails
class Emailer{

	protected $mailer;
	protected $options;
	protected $db;
	protected $logger;

	public function __construct(PDO $db, Options $options, LoggerInterface $logger = null){
	
		$this->options = $options;
		$this->db = $db;
		$this->logger = $logger;
		$this->mailer = new PHPMailer;
	
	}
	
	//send an email based on whether the identity or password was forgotten
	//assume $body has {{forgotten_code}} -> this code is a OTP to login and will require a manual change in passwords
	//setup that up in the session begin
	//the setup for OTP is based on the user
	//with autologin, the 
	
	//steps to take:
	//click on forgot identity/password
	//verification of user account detail (ask for username, or ask for security question) <- relies on end user to implement this
	//use AccountManager to get details of a particular user, and check if the user has that property
	//If verified
	//execute forgot_password or forgot_identity
	//forgot_password randomises the password of the user, and sends an OTP query link via send_forgotten_identity_password
	//it also executes a database query to notify that the next time they login, they need to change their password (given that forgottenCode or forgottenTime exists and is not empty, do this on autologin and normal login)
	//which then goes to forgot_password_complete which removes the check
	//forgot_identity just sends that to the user's email
	//OTP is time limited
	public function send_forgotten_identity_password($user_id, $subject = false, $body = false, $alt_body = false){
	
		//send both the OTP + identity (based on the $body's template)
		
	}
	
	//assume $body has {{activation_code}}
	//this can be sent multiple times, the activation code doesn't change (so the concept of resend activation email)
	public function send_activation_email($user_id, $subject = false, $body = false, $alt_body = false){
	
		if($this->options['reg_activation'] == 'email' AND $this->options['email']){
		
			$subject = (empty($subject)) ? $this->lang('email_activation_subject') : $subject;
			$body = (empty($body)) ? $this->options['email_activation_template'] : $body;
			
			//take the user_id, grab the person's email and activation_code
			$query = "SELECT email, activationCode FROM {$this->options['table_users']} WHERE id = :id";
			$sth = $this->db->prepare($query);
			$sth->bindParam(':id', $user_id, PDO::PARAM_INT);
			
			try{
				
				$sth->execute();
				//fetch a single row
				$row = $sth->fetch(PDO::FETCH_OBJ);
				
				//use sprintf to insert activation code and user id
				$body = sprintf(str_replace('{{user_id}}','\'%1$s\'', $body), $user_id);
				$body = sprintf(str_replace('{{activation_code}}','\'%1$s\'', $body), $row->activationCode);
				
				//send email via PHPMailer
				if(!$this->send_mail($row->email, $subject, $body, $alt_body)){
					if($this->logger){
						$this->logger->error('Failed to send activation email.');
					}
					$this->errors[] = $this->lang['activation_email_unsuccessful'];
					return false;
				}
				
				return true;
				
			}catch(PDOException $db_err){
			
				if($this->logger){
					$this->logger->error('Failed to execute query to fetch email and activation code given a user id.', ['exception' => $db_err]);
				}
				$this->errors[] = $this->lang['activation_email_unsuccessful'];
				return false;
				
			}
		
		}else{
		
			return false;
		
		}
		
	}
	
	public function send_mail($email_to, $subject, $body, $alt_body = false){
	
		if($this->options['email_smtp']){
			$this->mailer->IsSMTP();
			$this->mailer->Host = $this->options['email_host'];
			if($this->options['email_auth']){
				$this->mailer->SMTPAuth = true;
				$this->mailer->Username = $this->options['email_username'];
				$this->mailer->Password = $this->options['email_password'];
			}
			if($this->options['email_smtp_secure']) $this->mailer->SMTPSecure = $this->options['email_smtp_secure'];
		}
		
		$this->mailer->From = $this->options['email_from'];
		$this->mailer->FromName = $this->options['email_from_name'];
		$this->mailer->AddAddress($email_to);
		if($this->options['email_replyto']) $this->mailer->AddReplyTo($this->options['email_replyto'], $this->options['email_replyto_name']);
		if($this->options['email_cc']) $this->mailer->AddCC($this->options['email_cc']);
		if($this->options['email_bcc']) $this->mailer->AddBCC($this->options['email_bcc']);
		if($this->options['email_html']) $this->mailer->IsHTML(true);
		
		$this->mailer->Subject = $subject;
		$this->mailer->Body = $body;
		if($alt_body) $this->mailer->AltBody = $alt_body;
		
		if(!$mail->Send()){
			return false;
		}
		
		return true;
	
	}

}